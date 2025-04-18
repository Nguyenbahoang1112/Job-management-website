<?php

namespace App\Http\Repository\Admin\Team;

use App\Http\Repository\RepositoryInterface;
use App\Models\Team;
use App\Http\Repository\BaseRepository;

class TeamRepository extends BaseRepository implements TeamRepositoryInterface{
    public function __construct(Team $team){
        parent::__construct($team);
    }

    public function getAll($columns = ['*']){
        return $this->model::all($columns);
    }

    public function create($attributes = []){
        try{
            return $this->model::create($attributes);
        }
        catch(\Exception $e){
            throw new \Exception('Không thể tạo nhóm:',$e->getMessage());
        }
    }

    public function update($attributes = [],$id){
        try{
            $team = $this->model::findOrFail($id);
            $team->update($attributes);
            return $team;
        }
        catch(\Exception $e){
            throw new \Exception('Không thể sửa nhóm',$e->getMessage());
        }
    }

    public function find($id,$columns = ['*']){
        return $this->model::select($columns)->find($id);
    }
    public function paginate($perPage = 10, $columns = ['*']){
        return $this->model::orderBy('updated_at','desc')->paginate($perPage,$columns);
    }
}