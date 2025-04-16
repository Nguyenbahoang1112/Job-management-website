<?php
namespace App\Http\Repository\Admin;

use App\Http\Repository\Admin\TaskGroupRepositoryInterface;
use App\Http\Repository\BaseRepository;
use App\Models\TaskGroup;

class TaskGroupRepository extends BaseRepository implements TaskGroupRepositoryInterface{

    public function __construct(TaskGroup $taskGroup){
        parent::__construct($taskGroup);
    }

    public function getAll($columns = ['*']){
        return $this->model::all($columns);
    }

    public function create($attributes = []){

    }

    public function update($attributes = [], $id){

    }

    public function delete($id){

    }

    public function find($id, $columns = ['*']){

    }
}
