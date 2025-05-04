<?php

namespace App\Http\Controllers\User;

use Exception;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\RepeatRule;
use App\Models\TaskDetail;
use App\Helpers\ApiResponse;
use App\Helpers\ArrayFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Repository\Task\TaskRepository;
use App\Http\Repository\TaskTag\TaskTagRepository;
use App\Http\Requests\User\Task\CreateTaskRequest;
use App\Http\Repository\RepeatRule\RepeatRuleRepository;
use App\Http\Repository\TaskDetail\TaskDetailRepository;
use App\Http\Resources\Task\TaskResource;

class TaskController extends Controller
{
    protected $taskRepository;
    protected $taskDetailRepository;
    protected $taskTagRepository;
    protected $repeatRuleRepository;

    public function __construct(TaskRepository $taskRepository, TaskDetailRepository $taskDetailRepository, TaskTagRepository $taskTagRepository, RepeatRuleRepository $repeatRuleRepository)
    {
        $this->middleware('auth:sanctum');
        $this->taskRepository = $taskRepository;
        $this->taskDetailRepository = $taskDetailRepository;
        $this->taskTagRepository = $taskTagRepository;
        $this->repeatRuleRepository = $repeatRuleRepository;
    }

    public function getTasks(Request $request)
    {
        $type = $request->input('type', 'all');
        $userId = $request->user()->id;
        $groupedTasks = $this->taskRepository->getTasksByType($type, $userId);

        if (empty($groupedTasks)) {
            return ApiResponse::error('No tasks found for the given criteria.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getCompletedTasks(Request $request)
    {
        $userId = $request->user()->id;
        $groupedTasks = $this->taskRepository->getCompletedTasks($userId);
        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No completed tasks found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Completed tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function create(CreateTaskRequest $request)
    {
        try {
            switch ($request->due_date_select) {
                case RepeatRule::TODAY:
                    $due_date = now()->startOfDay();
                    break;
                case RepeatRule::TOMORROW:
                    $due_date = now()->addDay()->startOfDay();
                    break;
                case RepeatRule::WEEKEND:
                    $due_date = now()->next(Carbon::SUNDAY)->startOfDay();
                    break;
                default:
                    $due_date = $request->due_date ;
                    break;
            }
            //format deadline của task
            $due_date = Carbon::parse($due_date);
            $userId = auth('sanctum')->user()->id;
            $groupId = $request->input('group_id');

            $due_date_copy = $due_date->copy();

            //xét kiểu lặp lại
            switch ($request->repeat_type) {
                //tạo mới task không lặp lại
                case RepeatRule::REPEAT_TYPE_NONE:
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'task_group_id' => $groupId,
                        ]);

                        // Tạo task detail
                        $taskDetailCreate = $this->taskDetailRepository->createTaskDetail($request, $due_date_copy, $taskCreate->id, null, TaskDetail::PRIORITY_LOW);

                        // Tạo task tag (n-n)
                        if ($request->tag_ids) {
                            $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                            $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                        }
                        Log::info('Created task', $taskCreate->toArray());
                    });
                    break;
                //tạo mới task lặp lại "hàng ngày"
                case RepeatRule::REPEAT_TYPE_DAILY:
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'task_group_id' => $groupId,
                        ]);

                        // Tạo task tag (n-n)
                        if ($request->tag_ids) {
                            //format collection sang array
                            $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                            $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                        }

                        //tạo repeat_rule cho task lặp lại bởi admin
                        $repeatRuleCreate = $this->repeatRuleRepository->createByAdmin($request, $taskCreate->id, RepeatRule::PRIORITY_LOW);

                        //biến lưu stt task detail
                        $parent_id = 1;

                        //tạo mảng lưu taskDetails rỗng
                        $taskDetails = [];

                        // lặp lại hàng ngày theo số lần (repeat_interval)
                        if ($request->repeat_option == RepeatRule::REPEAT_BY_INTERVAL)
                        {
                            //vòng lặp tạo task detail theo số lần lặp lại
                            for ($i = 0; $i <= $request->repeat_interval; $i++) {
                                //thêm task detail vào array
                                $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id, TaskDetail::PRIORITY_LOW);

                                //thêm task detail vào mảng task details
                                array_push($taskDetails, $taskDetail);

                                //tăng parent_id
                                $parent_id++;

                                //tăng 1 ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date_copy = $due_date_copy->addDay();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                            return ApiResponse::success($taskCreate, 'Create task successful', 200);
                        }
                        else if ($request->repeat_option == RepeatRule::REPEAT_BY_DUE_DATE)
                        {
                            if ($due_date_copy >  Carbon::parse($request->repeat_due_date)) {
                                throw new \Exception("Ngày lặp lại không hợp lệ");
                            }
                            //vòng lặp tạo task detail theo hạn lặp lại
                            while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {

                                //thêm task detail vào array
                                $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id, TaskDetail::PRIORITY_LOW);

                                //thêm task detail vào mảng task details
                                array_push($taskDetails, $taskDetail);

                                //tăng parent_id
                                $parent_id++;

                                //tăng ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date = $due_date_copy->addDay();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                            return ApiResponse::success($taskCreate, 'Create task successful', 200);
                        }
                    });
                    break;

                //tạo mới task lặp lại "ngày trong tuần"
                case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'task_group_id' => $groupId,
                        ]);

                        // Tạo task tag (n-n)
                        if ($request->tag_ids) {
                            //format collection sang array
                            $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                            $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                        }

                        //tạo repeat_rule cho task lặp lại bởi admin
                        $repeatRuleCreate = $this->repeatRuleRepository->createByAdmin($request, $taskCreate->id, RepeatRule::PRIORITY_LOW);

                        //biến lưu stt task detail
                        $parent_id = 1;
                        //tạo mảng lưu taskDetails rỗng
                        $taskDetails = [];

                        // lặp lại ngày trong tuần theo số lần (repeat_interval)
                        if ($request->repeat_option == RepeatRule::REPEAT_BY_INTERVAL)
                        {
                            //vòng lặp tạo task detail theo số lần lặp lại
                            for ($i = 0; $i <= $request->repeat_interval; $i++) {
                                //kiểm tra khoảng thứ 2 đến t6
                                if ($due_date_copy->dayOfWeek >= 1 && $due_date_copy->dayOfWeek <= 5) {
                                    //thêm task detail vào array
                                    $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id, TaskDetail::PRIORITY_LOW);

                                    //thêm task detail vào mảng task details
                                    array_push($taskDetails, $taskDetail);

                                    //tăng parent_id
                                    $parent_id++;
                                }
                                //tăng ngày theo quy tắc lặp lại (ngày trong tuần)
                                if ($due_date_copy->dayOfWeek == 5) {
                                    //nếu đang là thứ 6 thì tăng lên 3 ngày để tới thứ 2
                                    $due_date_copy->addDay(3);
                                } else if ($due_date_copy->dayOfWeek == 6) {
                                    //nếu đang là thứ 7 thì tăng lên 2 ngày để tới thứ 2
                                    $due_date_copy->addDay(2);
                                } else {
                                    //tăng 1 ngày
                                    $due_date_copy->addDay();
                                }
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        } else if ($request->repeat_option == RepeatRule::REPEAT_BY_DUE_DATE)
                        {
                            if ($due_date_copy >  Carbon::parse($request->repeat_due_date)) {
                                throw new \Exception("Ngày lặp lại không hợp lệ");
                            }
                            //vòng lặp tạo task detail theo hạn lặp lại
                            while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {
                                //kiểm tra khoảng thứ 2 đến t6
                                if ( $due_date_copy->dayOfWeek >= 1 && $due_date_copy->dayOfWeek <= 5) {
                                    //thêm task detail vào array
                                    $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id, TaskDetail::PRIORITY_LOW);

                                    //thêm task detail vào mảng task details
                                    array_push($taskDetails, $taskDetail);

                                    //lấy taskDetail->id vừa tạo
                                    $parent_id++;
                                }
                                //tăng ngày theo quy tắc lặp lại (ngày trong tuần)
                                if ($due_date_copy->dayOfWeek == 5) {
                                    //nếu đang là thứ 6 thì tăng lên 3 ngày để tới thứ 2
                                    $due_date_copy->addDay(3);
                                } else if ($due_date_copy->dayOfWeek == 6) {
                                    //nếu đang là thứ 7 thì tăng lên 2 ngày để tới thứ 2
                                    $due_date_copy->addDay(2);
                                } else {
                                    //tăng 1 ngày
                                    $due_date_copy->addDay();
                                }
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        }
                    });
                    break;
                //tạo mới task lặp lại "hàng tháng"
                case RepeatRule::REPEAT_TYPE_MONTHLY:
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId) {
                        //tạo task
                        $taskCreate = $this->taskRepository->create([
                            'user_id' => $userId,
                            'task_group_id' => $groupId
                        ]);

                        // Tạo task tag (n-n)
                        if ($request->tag_ids) {
                            //format collection sang array
                            $taskTag = ArrayFormat::taskTag($request->tag_ids, $taskCreate->id);
                            $taskTagCreate = $this->taskTagRepository->insertMany($taskTag);
                        }

                        //tạo repeat_rule cho task lặp lại bởi admin
                        $repeatRuleCreate = $this->repeatRuleRepository->createByAdmin($request, $taskCreate->id, RepeatRule::PRIORITY_LOW);

                        //biến lưu stt task detail
                        $parent_id = 1;

                        //tạo mảng lưu taskDetails rỗng
                        $taskDetails = [];

                        // lặp lại hàng tháng theo số lần (repeat_interval)
                        if ($request->repeat_option == RepeatRule::REPEAT_BY_INTERVAL)
                        {
                            //vòng lặp tạo task detail theo số lần lặp lại
                            for ($i = 0; $i <= $request->repeat_interval; $i++) {
                                //thêm task detail vào array
                                $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id, TaskDetail::PRIORITY_LOW);

                                //thêm task detail vào mảng task details
                                array_push($taskDetails, $taskDetail);

                                //tăng parent_id
                                $parent_id++;

                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        } else if ($request->repeat_option == RepeatRule::REPEAT_BY_DUE_DATE)
                        {
                            if ($due_date_copy >  Carbon::parse($request->repeat_due_date)) {
                                throw new \Exception("Ngày lặp lại không hợp lệ");
                            }
                            //vòng lặp tạo task detail theo hạn lặp lại
                            while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {
                                //thêm task detail vào array
                                $taskDetail = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskCreate->id, $parent_id, TaskDetail::PRIORITY_LOW);

                                //thêm task detail vào mảng task details
                                array_push($taskDetails, $taskDetail);

                                //lấy taskDetail->id vừa tạo
                                $parent_id++;

                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                            }
                            //insert mảng task detail vào db
                            $taskDetailCreate = $this->taskDetailRepository->insertMany($taskDetails);
                        }
                    });
                    break;
                default:
                    break;
            }
            return ApiResponse::success(1, 'Create task successful', 200);
        }
        catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), ApiResponse::ERROR);
        }
    }

    public function update(Request $request, $id)
    {

    }

    public function destroy($id)
    {

    }
}
