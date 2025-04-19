<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Repository\Task\TaskRepository;
use App\Models\User;
use Illuminate\Http\Request;

class UserTaskController extends Controller
{
    protected $taskRepository;
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
        // Apply the admin middleware to all methods in this controller
        $this->middleware('admin');
    }
    public function index()
    {
        // Get all users
        $tasks = $this->taskRepository->getAllByAdmin('*', 10);
        return view('admin.task.index', compact('tasks'));
    }

}
