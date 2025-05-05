<?php

namespace App\Http\Repository\TaskDetail;

use App\Models\TaskDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;

class TaskDetailRepository extends BaseRepository
{
    public function __construct(TaskDetail $taskDetail)
    {
        parent::__construct($taskDetail);
    }

    public function getAllByTaskId($id)
    {
        $task_details = $this->model
            ->where('task_id', $id)
            ->paginate(10);
        return $task_details;
    }
    public function createTaskDetail($request, $due_date, $task_id, $parent_id = null, $priority = TaskDetail::PRIORITY_ADMIN)
    {
        return $this->model->create([
            'task_id' => $task_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $due_date,
            'time' => $request->time,
            'priority' => $priority,
            'parent_id' => $parent_id
        ]);
    }
}
