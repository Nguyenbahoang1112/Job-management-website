<?php

namespace App\Http\Repository\Auth;

use App\Http\Repository\BaseRepository;
use App\Models\User;

class AuthRepository extends BaseRepository
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }
    public function getAll($columns = ['*'])
    {
        //admin can see all users
        return $this->model::all($columns);
    }
    public function find($id, $columns = ['*'])
    {
        //admin can see detail user by id
        return $this->model::find($id, $columns);
    }
}
