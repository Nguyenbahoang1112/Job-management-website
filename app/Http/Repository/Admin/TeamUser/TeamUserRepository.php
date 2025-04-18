<?php

namespace App\Http\Repository\Admin\TeamUser;
use App\Models\TeamUser;
use App\Http\Repository\BaseRepository;
use Faker\Provider\Base;
use SebastianBergmann\Type\TrueType;

class TeamUserRepository extends BaseRepository{
    public function __construct(TeamUser $team_user){
        parent::__construct($team_user);
    }

    public function getAll($columns = ['*']){
        return $this->model::all();
    }
    
    public function create($attributes = []){
        try{
            return $this->model::create([
                $attributes
            ]);
        }  
        catch(\Exception $e){
            throw new \Exception('Không thể thêm người dùng vào nhóm'.$e->getMessage());
        }
    }

    public function addUsers($teamId, array $userIds)
    {
        $team = $this->model::findOrFail($teamId);
        $team->users()->syncWithoutDetaching($userIds);
    }

    public function removeUser($teamId, $userId)
    {
        $team = $this->model::findOrFail($teamId);
        $team->users()->detach($userId);
    }

}