<?php

namespace App\Http\Repository\Admin\User;

use App\Models\User;
use App\Http\Repository\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function getAll($columns = ['*'])
    {
        return $this->model::select($columns)->paginate(10); // phân trang
    }

    public function find($id, $columns = ['*'])
    {
        return $this->model::findOrFail($id);
    }

    public function update($attributes = [], $id)
    {
        $user = $this->model::findOrFail($id);
        $user->update($attributes);
        return $user;
    }

    public function delete($id)
    {
        $user = $this->model::findOrFail($id);
        return $user->delete();
    }
}
