<?php
namespace App\Http\Repository\TaskGroup;

use App\Http\Repository\BaseRepository;
use App\Models\TaskGroup;
use Illuminate\Http\Request;

class TaskGroupRepository extends BaseRepository{

    public function __construct(TaskGroup $taskGroup){
        parent::__construct($taskGroup);
    }

    public function getAllByUser($userId)
    {
        $taskGroups = $this->model
            ->where('user_id', $userId)
            ->orWhere('is_admin_created', '1')
            ->orderBy('name')
            ->get();
        return $taskGroups;
    }

    public function getAll($columns = ['*']){
        return $this->model::all($columns);
    }

    public function updateByUser($attributes = [], $id)
    {
        {
            $taskGroup = $this->model::findOrFail($id);
            if ($taskGroup->is_admin_created == 0)
            {
                $taskGroup->update($attributes);
                return $taskGroup;
            }
        }
        return false;
    }

    //xóa tag trừ tag của admin
    public function deleteByUser($id)
    {
        $tag = $this->model::findOrFail($id);
        if ($tag->is_admin_created == 0)
        {
            $tag->delete();
            return $tag;
        }
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
