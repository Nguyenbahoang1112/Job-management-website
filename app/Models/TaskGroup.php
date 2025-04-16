<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name','is_admin_created','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
