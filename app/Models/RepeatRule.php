<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepeatRule extends Model
{
    const STATUS_IN_PROGRESS = 0;
    const STATUS_DONE = 1;
    const STATUS_DELETING = 2;

    const PRIORITY_LOW = 0;
    const PRIORITY_ADMIN = 1;

    protected $fillable = [
        'task_id',
        'repeat_type',
        'repeat_interval',
        'repeat_due_date',
        'status_repeat_task',
        'priority_repeat_task'
    ];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
