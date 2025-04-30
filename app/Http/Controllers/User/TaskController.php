<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Repository\Task\TaskRepository;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
class TaskController extends Controller
{
    protected $taskRepo;

    public function __construct(TaskRepository $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function getTasks(Request $request)
    {
        $type = $request->input('type', 'all');
        $userId = $request->user()->id;
        $groupedTasks = $this->taskRepo->getTasksByType($type, $userId);

        if (empty($groupedTasks)) {
            return ApiResponse::error('No tasks found for the given criteria.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Tasks retrieved successfully.', ApiResponse::SUCCESS);
    }

    public function getCompletedTasks(Request $request)
    {
        $userId = $request->user()->id;
        $groupedTasks = $this->taskRepo->getCompletedTasks($userId);
        if (empty(array_filter($groupedTasks, fn($group) => !empty($group)))) {
            return ApiResponse::error('No completed tasks found.', ApiResponse::NOT_FOUND);
        }

        return ApiResponse::success($groupedTasks, 'Completed tasks retrieved successfully.', ApiResponse::SUCCESS);
    }
}
