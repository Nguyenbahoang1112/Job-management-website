<?php

namespace App\Http\Repository\Admin\Tag;

use App\Http\Repository\BaseRepository;
use App\Models\Tag;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $tag)
    {
        parent::__construct($tag);
    }

    public function getAll($columns = ['*'])
    {
        return $this->model::all($columns);
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
}
