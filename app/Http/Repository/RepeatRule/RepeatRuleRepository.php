<?php

namespace App\Http\Repository\RepeatRule;

use App\Http\Repository\BaseRepository;
use App\Models\RepeatRule;

class RepeatRuleRepository extends BaseRepository
{
    public function __construct(RepeatRule $repeatRule)
    {
        parent::__construct($repeatRule);
    }

    public function createByAdmin($request, $taskId)
    {
        $data = [
            'repeat_type' => $request->repeat_type,
            'task_id' => $taskId,
            'repeat_interval' => null,
            'repeat_due_date' => null,
            'priority_repeat_task' => RepeatRule::PRIORITY_ADMIN,
        ];

        if ($request->repeat_option === 'interval') {
            $data['repeat_interval'] = $request->repeat_interval;
        } else if ($request->repeat_option === 'endDate') {
            $data['repeat_due_date'] = $request->repeat_due_date;
        }
        return self::create($data);
    }
}
