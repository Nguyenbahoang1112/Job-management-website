<?php

namespace App\Http\Repository\Task;

use App\Http\Repository\BaseRepository;

use App\Models\Task;

class TaskRepository extends BaseRepository
{
    public function __construct(Task $task)
    {
        parent::__construct($task);
    }

    public function getAllByAdmin($columns = ['*'], $page = [10])
    {
        return $this->model
            ->select($columns)
            ->with(['user:id,email'])
            ->where('is_admin_created', 1)
            ->where('status', '!=', 2) // task do admin giao thì admin sẽ xóa hẳn và k có trạng thái deleting
            ->paginate($page);
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model::find($id, $columns);
    }

    public function create($attributes = [])
    {
        return $this->model::create($attributes);
    }

    public function update($attributes = [], $id)
    {
        return $this->model::where('id', $id)->update($attributes);
    }

    public function delete($id)
    {
        $record = $this->model::findOrFail($id);
        return $record->delete();
    }
}
