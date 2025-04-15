<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','due_date',
                            'time','priority','status','is_admin_created',
                            'user_id','task_group_id','team_id'];


    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function tags(){
        return $this->belongsToMany(Tag::class);
    }

    public function repeatTask(){
        return $this->hasOne(RepeateTask::class);
    }
}
