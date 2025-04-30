<?php

namespace App\Http\Repository\Tag;

use App\Models\Tag;
use App\Models\User;
use App\Http\Repository\BaseRepository;

class TagRepository extends BaseRepository
{
    public function __construct(Tag $tag)
    {
        parent::__construct($tag);
    }

    public function getAllByUser($userId)
    {
        $tags = $this->model
            ->where('user_id', $userId)
            ->orWhere('is_admin_created', '1')
            ->orderBy('name')
            ->get();
        return $tags;
    }

    //lấy ra tag của admin tạo
    public function getAllByAdmin($columns = ['*'])
    {
        return $this->model::where('is_admin_created', '1');
    }

    public function create($attributes = [])
    {
        try {
            return $this->model::create($attributes);
        } catch (\Exception $e) {
            throw new \Exception('Không thể tạo thẻ tag: ' . $e->getMessage());
        }
    }

    //sửa tag trừ tag tạo bởi admin
    public function updateByUser($attributes = [], $id)
    {
        {
            $tag = $this->model::findOrFail($id);
            if ($tag->is_admin_created == 0)
            {
                $tag->update($attributes);
                return $tag;
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

    public function updateByAdmin($attributes = [], $id)
    {
        try {
            $tag = $this->model::findOrFail($id);
            $tag->update($attributes);
            return $tag;
        } catch (\Exception $e) {
            throw new \Exception('Không thể cập nhật thẻ tag: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $tag = $this->model::findOrFail($id);
            return $tag->delete();
        } catch (\Exception $e) {
            throw new \Exception('Không thể xóa thẻ tag: ' . $e->getMessage());
        }
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model::select($columns)->find($id);
    }

    public function paginate($perPage = 10, $columns = ['*']){
        return $this->model::orderBy('updated_at','desc')->orderBy('is_admin_created','desc')->paginate($perPage,$columns);
    }

    public function searchByName($query)
    {
        return $this->model::where('name', 'like', '%' . $query . '%')
                        ->orderBy('is_admin_created','desc')
                        ->paginate(10);
    }
}
