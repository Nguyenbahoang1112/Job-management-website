<?php
namespace App\Http\Repository;

use App\Http\Repository\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model; 
    }

    public function getAll($columns = ['*'])
    {
        return $this->model::all($columns);
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
        $model = $this->model::find($id);
        if ($model) {
            $model->update($attributes);
            return $model;
        }
        return false;
    }
    public function delete($id)
    {
        $model = $this->model::find($id);
        if ($model) {
            $model->delete();
            return true;
        }
        return false;
    }
}
