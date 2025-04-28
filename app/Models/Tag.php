<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    const TAG_CREATED_BY_ADMIN = 1;
    const TAG_CREATED_BY_USER = 0;
    protected $fillable = [
        'name',
        'is_admin_created',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(Task::class, 'task_tags', 'tag_id', 'task_id');
    }
}
