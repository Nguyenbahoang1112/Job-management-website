<?php

namespace App\Http\Repository\Tag;

use App\Http\Repository\BaseRepository;
use App\Models\Tag;

class TagRepository extends BaseRepository
{
    public function __construct(Tag $tag)
    {
        parent::__construct($tag);
    }

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

    public function update($attributes = [], $id)
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
        return $this->model::orderBy('updated_at','desc')->paginate($perPage,$columns);
    }
}
