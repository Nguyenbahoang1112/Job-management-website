<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{

    const STATUS_IN_PROGRESS = 0;
    const STATUS_DONE = 1;
    const STATUS_DELETING = 2;

    const PRIORITY_LOW = 0;
    const PRIORITY_ADMIN = 1;

    const TASK_CREATED_BY_ADMIN = 1;
    const TASK_CREATED_BY_USER = 0;
    use HasFactory;

    protected $fillable = ['title','description','due_date',
                            'time','priority','status','is_admin_created',
                            'user_id','task_group_id','team_id'];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

    public function repeatTask(){
        return $this->hasOne(RepeatTask::class);
    }
}
