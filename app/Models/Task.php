<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    const TASK_CREATED_BY_ADMIN = 1;
    const TASK_CREATED_BY_USER = 0;

    use HasFactory;

    protected $fillable = [
        'is_admin_created',
        'user_id',
        'task_group_id',
        'team_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

    public function repeatRule(){
        return $this->hasOne(RepeatRule::class);
    }

    public function taskDetails() {
        return $this->hasMany(TaskDetail::class);
    }

    public function taskGroup() {
        return $this->belongsTo(TaskGroup::class);
    }

    public function team() {
        return $this->belongsTo(Team::class);
    }

}
