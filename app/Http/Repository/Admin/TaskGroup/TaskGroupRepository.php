<?php
namespace App\Http\Repository\Admin\TaskGroup;

use App\Http\Repository\Admin\TaskGroupsTaskGroupRepositoryInterface;
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
        try {
            return $this->model::create($attributes);
        } catch (\Exception $e) {
            throw new \Exception('Không thể tạo nhóm công việc: ' . $e->getMessage());
        }
    }

    public function update($attributes = [], $id){
        try {
            $taskGroup = $this->model::findOrFail($id);
            $taskGroup->update($attributes);
            return $taskGroup;
        } catch (\Exception $e) {
            throw new \Exception('Không thể cập nhật nhóm công việc: ' . $e->getMessage());
        }
    }

    public function delete($id){
        try {
            $taskGroup = $this->model::findOrFail($id);
            return $taskGroup->delete();
        } catch (\Exception $e) {
            throw new \Exception('Không thể xóa nhóm công việc: ' . $e->getMessage());
        }
    }

    public function find($id, $columns = ['*']){
        return $this->model::select($columns)->find($id);
    }
    public function paginate($perPage = 10, $columns = ['*']){
        return $this->model::orderBy('updated_at','desc')->paginate($perPage,$columns);
    }
}
