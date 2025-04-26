<?php

namespace App\Http\Repository\Task;

use App\Models\Task;
use App\Models\RepeatRule;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;
use Illuminate\Http\Request;

class TaskRepository extends BaseRepository
{
    public function __construct(Task $task)
    {
        parent::__construct($task);
    }
    //Lấy tất cả task của admin tạo
    public function getAllByAdmin($columns = ['*'], $page = 10)
    {
        $tasks = $this->model
        ->with(['taskDetails' => function ($query) {
            $query->where('status', 0);
        }])
        ->whereHas('taskDetails', function ($query) {
            $query->where('status', 0);
        })
        ->where('is_admin_created', 1)
        ->paginate($page);

        return $tasks;
    }

    // Tạo task không lặp lại
    public function createTaskToUser($user_id)
    {
        return $this->model->create([
            'user_id' => $user_id,
            'is_admin_created' => 1,
        ]);
    }
}
