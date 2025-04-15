<?php
namespace App\Http\Repository;

interface RepositoryInterface
{
    public function getAll($columns = ['*']);
    public function find($id, $columns = ['*']);
    public function create($attributes = []);
    public function update($attributes = [], $id);
    public function delete($id);
}
