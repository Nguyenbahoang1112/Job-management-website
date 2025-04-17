<?php

namespace App\Http\Repository\Admin\User;

use App\Http\Repository\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
    public function getAll($columns = ['*'])
    {
        return $this->model::where('role', User::ROLE_USER)->get($columns);
    }
    public function find($id, $columns = ['*'])
    {
        return $this->model::where('role', User::ROLE_USER)->find($id, $columns);
    }
    public function create($attributes = [])
    {
        // Create a new user
        $attributes['password'] = bcrypt($attributes['password']);
        $attributes['role'] = User::ROLE_USER;

        return $this->model::create($attributes);
    }
    public function update($attributes = [], $id)
    {
        // Update user
        return $this->model::where('id', $id)->update($attributes);
    }
    public function changePass($attributes, $id)
    {
        $user = $this->model::find($id);
        if ($user) {
            if (isset($attributes['password'])) {
                $attributes['password'] = bcrypt($attributes['password']);
                return $user->update(['password' => $attributes['password']]);
            }
        }
        return false;
    }
    public function delete($id)
    {
        return $this->model::delete($id);
    }
}
