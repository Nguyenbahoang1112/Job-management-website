<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Repository\Task\TaskRepository;
use Illuminate\Http\Request;

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

        return response()->json([
            'data' => $groupedTasks
        ]);
    }

    // API mới để lấy tất cả task đã hoàn thành
    public function getCompletedTasks(Request $request)
    {
        $userId = $request->user()->id;

        $groupedTasks = $this->taskRepo->getCompletedTasks($userId);

        return response()->json([
            'data' => $groupedTasks
        ]);
    }
}
