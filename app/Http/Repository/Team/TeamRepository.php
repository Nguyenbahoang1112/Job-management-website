<?php

namespace App\Http\Repository\Team;

use App\Models\Team;
use App\Http\Repository\BaseRepository;

class TeamRepository extends BaseRepository{
    public function __construct(Team $team){
        parent::__construct($team);
    }

    public function getAll($columns = ['*']){
        return $this->model::orderBy('updated_at','desc')->paginate(12);
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
    public function getUserTeams(int $userId)
    {
        return $this->model
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with('users:id,email') // Lấy thông tin user liên quan (tùy chọn)
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'description' => $team->description,
                    'users' => $team->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->email,
                        ];
                    }),
                    'created_at' => $team->created_at->toISOString(),
                    'updated_at' => $team->updated_at->toISOString(),
                ];
            })
            ->toArray();
    }

}
