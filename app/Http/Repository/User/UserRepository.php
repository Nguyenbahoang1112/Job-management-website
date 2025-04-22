<?php

namespace App\Http\Repository\User;

use App\Http\Repository\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    //get all user (not admin)
    public function getAllUser($columns = ['*'])
    {
        return $this->model::select($columns)->where('role', User::ROLE_USER)->paginate(10);
    }

    //find user by id
    public function find($id, $columns = ['*'])
    {
        return $this->model::where('role', User::ROLE_USER)->find($id, $columns);
    }

    //create new user
    public function create($attributes = [])
    {
        $attributes['password'] = bcrypt($attributes['password']);
        $attributes['role'] = User::ROLE_USER;

        return $this->model::create($attributes);
    }

    //update user
    public function update($attributes = [], $id)
    {
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
        $record = $this->model::findOrFail($id);
        return $record->delete();
    }

    public function searchByEmail($query)
    {
        return $this->model::where('email', 'like', '%' . $query . '%')
                        ->where('role', User::ROLE_USER)
                        ->paginate(10);
    }
}
