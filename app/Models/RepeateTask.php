<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepeateTask extends Model
{
    use HasFactory;

    protected $fillable = ['task_id','repeat_type','repeat_interval','repeat_date'];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
