<?php

namespace App\Helpers;

use App\Models\TaskDetail;

class ArrayFormat
{
    static function taskTag(array $tagIds, int $taskId): array
    {
        return array_map(fn($tag_id) => [
            'tag_id' => $tag_id,
            'task_id' => $taskId,
        ], $tagIds);
    }
    static function taskDetailByAdmin($request, $due_date, $task_id, $parent_id)
    {
        return [
            'task_id' => $task_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $due_date,
            'time' => $request->time,
            'priority' => TaskDetail::PRIORITY_ADMIN,
            'parent_id' => $parent_id,
            'created_at' => now()
        ];
    }
}
