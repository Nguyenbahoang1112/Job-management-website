<?php

namespace App\Http\Repository\TaskTag;

use App\Models\TaskTag;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;

class TaskTagRepository extends BaseRepository
{
    public function __construct(TaskTag $taskTag)
    {
        parent::__construct($taskTag);
    }

    public function getTagId($taskId)
    {
        // dd($this->model::where('task_id', $taskId)->pluck('tag_id'));
        return $this->model::where('task_id', $taskId)->pluck('tag_id');
    }
    public function deleteTaskTag($taskDetailId, $tagId)
    {
        return $this->model::where('task_id', $taskDetailId)->where('tag_id', $tagId)->delete();
    }
}
