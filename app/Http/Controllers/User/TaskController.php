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
use App\Http\Repository\SearchHistory\SearchHistoryRepository;
use App\Http\Repository\TaskTag\TaskTagRepository;
use App\Http\Requests\User\Task\CreateTaskRequest;
use App\Http\Repository\RepeatRule\RepeatRuleRepository;
use App\Http\Repository\TaskDetail\TaskDetailRepository;
use App\Http\Resources\Task\TaskResource;

use App\Models\SearchHistory;
use App\Models\Team;

class TaskController extends Controller
{
    protected $taskRepository;
    protected $taskDetailRepository;
    protected $taskTagRepository;
    protected $repeatRuleRepository;
    protected $searchHistoryRepo;

    public function __construct(TaskRepository $taskRepository,SearchHistoryRepository $searchHistoryRepo, TaskDetailRepository $taskDetailRepository, TaskTagRepository $taskTagRepository, RepeatRuleRepository $repeatRuleRepository)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('auth:sanctum');
        $this->taskRepository = $taskRepository;
        $this->taskDetailRepository = $taskDetailRepository;
        $this->taskTagRepository = $taskTagRepository;
        $this->repeatRuleRepository = $repeatRuleRepository;
        $this->searchHistoryRepo = $searchHistoryRepo;
    }

    public function getTasks(Request $request)
    {
        $type = $request->input('type', 'all');
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepository->getTasksByType($type, $userId);

        if (empty($groupedTasks)) {
            return ApiResponse::error('No tasks found for the given criteria.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getCompletedTasks()
    {
        $userId = auth('sanctum')->user()->id;
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
    public function getDeletedTasks()
    {
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepository->getDeletedTasks($userId);
        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No deleted tasks found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Deleted tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getImportantTasks()
    {
        $userId = auth('sanctum')->user()->id;
        $tasks = $this->taskRepository->getImportantTasks($userId);
        if (empty(array_filter($tasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No important tasks found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($tasks, 'Important tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function searchTasksByTitle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $userId = $request->user()->id;
        $title = $request->input('title');
        $groupedTasks = $this->taskRepository->searchTasksByTitle($userId, $title);

       
        $this->searchHistoryRepo->createSearchHistory($title, $userId);
    

        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found with the given title.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }


    public function getTasksByUserInTeams()
    {
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepository->getTasksByUserInTeams($userId);

        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found for the user in teams.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }


    public function getTeamsAndTaskGroups()
    {
        $userId = auth('sanctum')->user()->id;

        $result = $this->taskRepository->getTeamsAndTaskGroups($userId);

        if (empty($result['teams']) && empty($result['task_groups'])) {
            return ApiResponse::error('No teams or task groups found for the user.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($result, 'Teams and task groups retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getTasksByTeamOrGroup(Request $request)
    {
        $userId = auth('sanctum')->user()->id; // Lấy userId từ user đã xác thực
        $teamId = $request->query('team_id'); // Lấy team_id từ query parameter
        $taskGroupId = $request->query('task_group_id'); // Lấy task_group_id từ query parameter

        // Chuyển đổi sang kiểu int nếu có giá trị
        $teamId = $teamId ? (int)$teamId : null;
        $taskGroupId = $taskGroupId ? (int)$taskGroupId : null;

        // Kiểm tra chỉ một tham số được truyền vào
        if (($teamId !== null) && ($taskGroupId !== null)) {
            return ApiResponse::error('Please provide only one parameter: team_id or task_group_id.', ApiResponse::ERROR);
        }

        if ($teamId === null && $taskGroupId === null) {
            return ApiResponse::error('Please provide either team_id or task_group_id.', ApiResponse::ERROR);
        }

        // Kiểm tra quyền truy cập team nếu teamId được cung cấp
        if ($teamId) {
            $teamIds = Team::whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->pluck('id')->toArray();

            if (!in_array($teamId, $teamIds)) {
                return ApiResponse::error('You do not have access to this team.', ApiResponse::FORBIDDEN);
            }
        }

        // Gọi repository để lấy task
        $tasks = $this->taskRepository->getTasksByTeamOrGroup($userId, $teamId, $taskGroupId);

        if (empty(array_filter($tasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found for the specified team or task group.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($tasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getTasksByTag(Request $request)
    {
        $userId = auth('sanctum')->user()->id; // Lấy userId từ user đã xác thực
        $tagId = $request->query('tag_id'); // Lấy tag_id từ query parameter

        // Kiểm tra nếu tag_id không được cung cấp
        if (!$tagId) {
            return ApiResponse::error('Tag ID is required.', ApiResponse::ERROR);
        }

        // Chuyển đổi sang kiểu int
        $tagId = (int)$tagId;

        // Gọi repository để lấy task
        $tasks = $this->taskRepository->getTasksByTag($userId, $tagId);

        if (empty(array_filter($tasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found for the specified tag.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($tasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }
}
