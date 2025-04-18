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

    public function getAll($columns = ['*'])
    {
        return $this->model::select($columns)->where('is_admin_created')->paginate(12);
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
