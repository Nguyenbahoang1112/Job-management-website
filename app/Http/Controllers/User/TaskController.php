<?php

namespace App\Http\Controllers\User;

use Exception;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\RepeatRule;
use App\Models\TaskDetail;
use App\Helpers\ApiResponse;
use App\Helpers\ArrayFormat;
use App\Helpers\DateFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Repository\Task\TaskRepository;
use App\Http\Repository\TaskTag\TaskTagRepository;
use App\Http\Requests\User\Task\CreateTaskRequest;
use App\Http\Repository\RepeatRule\RepeatRuleRepository;
use App\Http\Repository\TaskDetail\TaskDetailRepository;
use App\Http\Requests\User\Task\UpdateAllTaskRequest;
use App\Http\Requests\User\Task\UpdateTaskRequest;
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
                    $due_date = $request->due_date;
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
                    $taskCreate = null;
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId, &$taskCreate) {
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
                        // Log::info('Created task', $taskCreate->toArray());
                    });
                    return ApiResponse::success($taskCreate, 'Create task successful', 200);
                    break;
                //tạo mới task lặp lại "hàng ngày"
                case RepeatRule::REPEAT_TYPE_DAILY:
                    $taskCreate = null;
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId, &$taskCreate) {
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
                        }
                    });
                    return ApiResponse::success($taskCreate, 'Create task successful', 200);
                    break;

                //tạo mới task lặp lại "ngày trong tuần"
                case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
                    $taskCreate = null;
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId, &$taskCreate) {
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
                    return ApiResponse::success($taskCreate, 'Create task successful', 200);
                    break;
                //tạo mới task lặp lại "hàng tháng"
                case RepeatRule::REPEAT_TYPE_MONTHLY:
                    $taskCreate = null;
                    DB::transaction(function () use ($request, $due_date_copy, $groupId, $userId, &$taskCreate) {
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
                    return ApiResponse::success($taskCreate, 'Create task successful', 200);
                    break;
                default:
                    return ApiResponse::success(1, 'Create task successful', 200);
                    break;
            }

        }
        catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), ApiResponse::ERROR);
        }
    }

    public function duplicate($id)
    {
        try {
            $taskDetail = $this->taskDetailRepository->find($id);
            if ($taskDetail->task->is_admin_created == Task::TASK_CREATED_BY_USER) {
                // dd($taskDetail->task->user_id, $taskDetail->task->task_group_id);
                $taskCreate = $this->taskRepository->create([
                    'user_id' => $taskDetail->task->user_id,
                    'task_group_id' => $taskDetail->task->task_group_id,
                ]);

                $taskDetailCreate = $this->taskDetailRepository->create([
                    'task_id' => $taskCreate->id,
                    'title' => $taskDetail->title,
                    'description' => $taskDetail->description,
                    'due_date' => $taskDetail->due_date,
                    'time' => $taskDetail->time,
                    'priority' => $taskDetail->priority
                ]);

                return ApiResponse::success($taskDetailCreate, 'Duplicate task successful', 200);
            } else {
                return ApiResponse::error('Task of admin created', 400);
            }
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), ApiResponse::ERROR);
        }

    }
    public function update(UpdateTaskRequest $request, $taskDetailId)
    {
        try {
            //lấy ra task detail cần cập nhật
            $taskDetail = $this->taskDetailRepository->find($taskDetailId);
            $tagIds = $this->taskTagRepository->getTagId($taskDetail->task_id)->toArray();
            //kiểm tra task có được tạo bởi admin hay không
            if ( !$this->taskRepository->checkAdminCreated($taskDetail->task_id) == Task::TASK_CREATED_BY_ADMIN) {
                //cập nhật thay đổi ở task detail
                DB::transaction(function () use ($request, $taskDetail, $tagIds) {
                    $taskDetailUpdate = $this->taskDetailRepository->update([
                        'title' => $request->title,
                        'description' => $request->description,
                        'due_date' => $request->due_date,
                        'time' => $request->time,
                        'priority' => $request->priority,
                    ], $taskDetail->id);

                    //lấy ra mảng tag mới
                    $tagIdRequest = $request->tag_ids;
                    //lấy phần tử chung của 2 mảng
                    $common = array_intersect($tagIds, $tagIdRequest);
                    //Lấy phần tử chỉ tồn tại ở mảng 1
                    $onlyInArray1 = array_diff($tagIds, $common);
                    //Lấy phần tử chỉ tồn tại ở mảng 2
                    $onlyInArray2 = array_diff($tagIdRequest, $common);

                    //xóa các tag không có trong request mới
                    if (count($onlyInArray1) > 0) {
                        foreach ($onlyInArray1 as $tagId) {
                            $taskTagDelete = $this->taskTagRepository->deleteTaskTag($taskDetail->task_id, $tagId);
                        }
                    }
                    //thêm có tag có trong request mới
                    if (count($onlyInArray2) > 0) {
                        foreach ($onlyInArray2 as $tagId) {
                            $taskTagCreate = $this->taskTagRepository->create([
                                'task_id' => $taskDetail->task_id,
                                'tag_id' => $tagId
                            ]);
                        }
                    }
                });
            }
            return ApiResponse::success($request->tag_ids, 'Update task successful', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), ApiResponse::ERROR);
        }
    }

    public function updateAll(UpdateAllTaskRequest $request, $taskId)
    {
        try {
            //kiểm tra task của admin hay không?
            $isAdminCreated = $this->taskRepository->checkAdminCreated($taskId);
            if ($isAdminCreated == Task::TASK_CREATED_BY_USER) {
                //lấy ra danh sách task detail của task (task_id)
                $taskDetails = $this->taskDetailRepository->getAllTaskDetail($taskId);
                // dd($taskDetails);
                //due_date
                $due_date = Carbon::parse($request->due_date);
                if ($request->repeat_option == RepeatRule::REPEAT_BY_INTERVAL)
                {
                    $due_date_copy = $due_date->copy();

                    $indexRepeat = 0;
                    //đánh dấu parent_id
                    $parent_id = 1;
                    //mảng thêm mới
                    $taskDetailCreates = [];
                    // dd($due_date_copy, $indexRepeat, $parent_id, $taskDetailCreates);
                    //cập nhật trong mảng task detail ban đầu
                    foreach ($taskDetails as $taskDetail)
                    {
                        if ($taskDetail->status == TaskDetail::STATUS_IN_PROGRESS)
                        {
                            if ($indexRepeat > $request->repeat_interval) {
                                $this->taskDetailRepository->delete($taskDetail->id);
                            } else {
                                //cập nhật task detail
                                $taskDetailUpdate = $this->taskDetailRepository->update([
                                    'title' => $request->title,
                                    'description' => $request->description,
                                    'due_date' => $due_date_copy->copy(),
                                    'time' => $request->time,
                                    'priority' => $request->priority,
                                ], $taskDetail->id);
                            }
                        }
                        //tăng các giá trị due_date, số đếm lặp lại và parent_id để bỏ qua task hoàn thành hoặc ở thùng rác
                        //tăng ngày theo quy tắc lặp lại
                        switch ($request->repeat_type) {
                            case RepeatRule::REPEAT_TYPE_DAILY:
                                //tăng 1 ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date_copy = $due_date_copy->addDay();
                                break;
                            case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
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
                                break;
                            case RepeatRule::REPEAT_TYPE_MONTHLY:
                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                                break;
                            default:
                                break;
                        }
                        //tăng indexRepeat
                        $indexRepeat++;
                        //tăng parent_id
                        $parent_id++;
                    }
                    //thêm mới các task detail nếu còn thiếu
                    while ($indexRepeat <= $request->repeat_interval) {
                        //thêm task detail vào array
                        $taskDetailCreate = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskId, $parent_id, $request->priority);
                        //thêm task detail vào mảng task details
                        array_push($taskDetailCreates, $taskDetailCreate);

                        //tăng indexRepeat
                        $indexRepeat++;
                        //tăng parent_id
                        $parent_id++;
                    }
                    //nếu có thêm mới thì insert vào dbs
                    if ($taskDetails->count() > 0) {
                        $taskDetailUpdates = $this->taskDetailRepository->insertMany($taskDetailCreates);
                        return ApiResponse::success(true, 'Update task successful', 200);
                    }
                } else if ($request->repeat_option == RepeatRule::REPEAT_BY_DUE_DATE) {
                    $due_date_copy = $due_date->copy();
                    //đánh dấu parent_id
                    $parent_id = 1;
                    //mảng thêm mới
                    $taskDetailCreates = [];
                    // dd($due_date_copy, $indexRepeat, $parent_id, $taskDetailCreates);
                    //cập nhật trong mảng task detail ban đầu
                    foreach ($taskDetails as $taskDetail)
                    {
                        if ($taskDetail->status == TaskDetail::STATUS_IN_PROGRESS)
                        {
                            if ($due_date_copy > Carbon::parse($request->repeat_due_date)) {
                                $this->taskDetailRepository->delete($taskDetail->id);
                            } else {
                                //cập nhật task detail
                                $taskDetailUpdate = $this->taskDetailRepository->update([
                                    'title' => $request->title,
                                    'description' => $request->description,
                                    'due_date' => $due_date_copy->copy(),
                                    'time' => $request->time,
                                    'priority' => $request->priority,
                                ], $taskDetail->id);
                            }
                        }
                        //tăng các giá trị due_date, số đếm lặp lại và parent_id để bỏ qua task hoàn thành hoặc ở thùng rác
                        //tăng ngày theo quy tắc lặp lại
                        switch ($request->repeat_type) {
                            case RepeatRule::REPEAT_TYPE_DAILY:
                                //tăng 1 ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date_copy = $due_date_copy->addDay();
                                break;
                            case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
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
                                break;
                            case RepeatRule::REPEAT_TYPE_MONTHLY:
                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                                break;
                            default:
                                break;
                        }
                        //tăng parent_id
                        $parent_id++;
                    }
                    //thêm mới các task detail nếu còn thiếu
                    while ($due_date_copy <= Carbon::parse($request->repeat_due_date)) {
                        //thêm task detail vào array
                        $taskDetailCreate = ArrayFormat::taskDetailByAdmin($request, $due_date_copy->copy(), $taskId, $parent_id, $request->priority);
                        //thêm task detail vào mảng task details
                        array_push($taskDetailCreates, $taskDetailCreate);

                        //tăng các giá trị due_date và parent_id
                        //tăng ngày theo quy tắc lặp lại
                        switch ($request->repeat_type) {
                            case RepeatRule::REPEAT_TYPE_DAILY:
                                //tăng 1 ngày theo quy tắc lặp lại (hàng ngày)
                                $due_date_copy = $due_date_copy->addDay();
                                break;
                            case RepeatRule::REPEAT_TYPE_DAY_OF_WEEK:
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
                                break;
                            case RepeatRule::REPEAT_TYPE_MONTHLY:
                                //tăng 1 tháng
                                $due_date_copy->addMonth();
                                break;
                            default:
                                break;
                            }
                            //tăng parent_id
                            $parent_id++;
                    }
                    //nếu có thêm mới thì insert vào dbs
                    if ($taskDetails->count() > 0) {
                        $taskDetailUpdates = $this->taskDetailRepository->insertMany($taskDetailCreates);
                        return ApiResponse::success(true, 'Update task successful', 200);
                    }
                }
                return ApiResponse::success(true, 'Update task successful', 200);
            }
            return ApiResponse::error('Task of admin created', 400);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), ApiResponse::ERROR);
        }

    }
    public function destroy($id)
    {
        $taskDetail = $this->taskDetailRepository->find($id);
        //)
        //kiểm tra task có được tạo bởi admin hay không
        if ( !$this->taskRepository->checkAdminCreated($taskDetail->task_id) == Task::TASK_CREATED_BY_ADMIN)
        {
            $taskDetailDelete = $this->taskDetailRepository->removeToTrash($id);
            if ($taskDetailDelete)
            {
                return ApiResponse::success($taskDetailDelete, 'Delete task successful', 200);
            }
        }
        return ApiResponse::error('Delete task failed', 400);
    }
    public function destroyAll($taskId)
    {
        //kiểm tra task có được tạo bởi admin hay không
        if ( !$this->taskRepository->checkAdminCreated($taskId) == Task::TASK_CREATED_BY_ADMIN)
        {
            //lấy ra danh sách task detail đang processing của task
            $taskDetails = $this->taskDetailRepository->getTaskDetailProcess($taskId);

            if ($taskDetails->count() > 0) {
                $taskDelete = $this->taskDetailRepository->removeAllToTrash($taskId, $taskDetails);

                if ($taskDelete) {
                    return ApiResponse::success('Delete task successful', 200);
                }
            }
        }
        return ApiResponse::error('Delete task failed', 400);
    }
}
