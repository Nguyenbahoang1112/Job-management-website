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

    public function createByAdmin($request, $taskId, $priority = RepeatRule::PRIORITY_ADMIN)
    {
        $data = [
            'repeat_type' => $request->repeat_type,
            'task_id' => $taskId,
            'repeat_interval' => null,
            'repeat_due_date' => null,
            'priority_repeat_task' => $priority,
        ];

        if ($request->repeat_option == RepeatRule::REPEAT_BY_INTERVAL) {
            $data['repeat_interval'] = $request->repeat_interval;
        } else if ($request->repeat_option == RepeatRule::REPEAT_BY_DUE_DATE) {
            $data['repeat_due_date'] = $request->repeat_due_date;
        }
        return self::create($data);
    }
    public function getRepeatRule($taskId)
    {
        $repeatRule = $this->model->find($taskId);
        return $repeatRule;
    }
}
