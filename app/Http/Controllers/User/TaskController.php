<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Repository\Task\TaskRepository;
use App\Http\Repository\SearchHistory\SearchHistoryRepository;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\SearchHistory;
use App\Models\Team;

class TaskController extends Controller
{
    protected $taskRepo;
    protected $searchHistoryRepo;

    public function __construct(TaskRepository $taskRepo,SearchHistoryRepository $searchHistoryRepo)
    {
        $this->middleware('auth:sanctum');
        $this->taskRepo = $taskRepo;
        $this->searchHistoryRepo = $searchHistoryRepo;
    }

    public function getTasks(Request $request)
    {
        $type = $request->input('type', 'all');
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepo->getTasksByType($type, $userId);

        if (empty($groupedTasks)) {
            return ApiResponse::error('No tasks found for the given criteria.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getCompletedTasks()
    {
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepo->getCompletedTasks($userId);
        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No completed tasks found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Completed tasks retrieved successfully.', ApiResponse::SUCCESS);
    }
    public function getDeletedTasks()
    {
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepo->getDeletedTasks($userId);
        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No deleted tasks found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Deleted tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getImportantTasks()
    {
        $userId = auth('sanctum')->user()->id;
        $tasks = $this->taskRepo->getImportantTasks($userId);
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
        $groupedTasks = $this->taskRepo->searchTasksByTitle($userId, $title);

       
        $this->searchHistoryRepo->createSearchHistory($title, $userId);
    

        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found with the given title.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }


    public function getTasksByUserInTeams()
    {
        $userId = auth('sanctum')->user()->id;
        $groupedTasks = $this->taskRepo->getTasksByUserInTeams($userId);

        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found for the user in teams.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }


    public function getTeamsAndTaskGroups()
    {
        $userId = auth('sanctum')->user()->id;

        $result = $this->taskRepo->getTeamsAndTaskGroups($userId);

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
        $tasks = $this->taskRepo->getTasksByTeamOrGroup($userId, $teamId, $taskGroupId);

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
        $tasks = $this->taskRepo->getTasksByTag($userId, $tagId);

        if (empty(array_filter($tasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No tasks found for the specified tag.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($tasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }
}
