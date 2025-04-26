<?php

namespace App\Http\Repository\Task;

use App\Models\Task;
use App\Models\RepeatRule;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\Team;
class TaskRepository extends BaseRepository
{
    public function __construct(Task $task)
    {
        parent::__construct($task);
    }
    //Lấy tất cả task của admin tạo
    public function getAllByAdmin($columns = ['*'], $page = 10)
    {
        $tasks = $this->model
        ->with(['taskDetails' => function ($query) {
            $query->where('status', 0);
        }])
        ->whereHas('taskDetails', function ($query) {
            $query->where('status', 0);
        })
        ->where('is_admin_created', 1)
        ->paginate($page);

        return $tasks;
    }

    // Tạo task không lặp lại
    public function createTaskToUser($user_id)
    {
        return $this->model->create([
            'user_id' => $user_id,
            'is_admin_created' => 1,
        ]);
    }


    public function getTasksByType(string $type, int $userId)
    {
        // Lấy danh sách team mà user tham gia
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();



        // Lấy task của user hoặc task thuộc team mà user tham gia
        $query = Task::with([
            'taskDetails:id,task_id,title,description,due_date,time,priority,status,parent_id',
            'taskGroup:id,name',
            'repeatRule:id,task_id,repeat_type', // Quan hệ 1-1
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId)
                  ->orWhereIn('team_id', $teamIds);
        });

        // Lọc theo loại thời gian
        if ($type === 'today') {
            $query->whereHas('taskDetails', function ($q) {
                $q->whereDate('due_date', Carbon::today());
            });
        } elseif ($type === 'three_days') {
            $query->whereHas('taskDetails', function ($q) {
                $q->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(3)]);
            });
        } elseif ($type === 'seven_days') {
            $query->whereHas('taskDetails', function ($q) {
                $q->whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(7)]);
            });
        }
        // Nếu type là 'all', không cần thêm điều kiện lọc thời gian

        $tasks = $query->get();



        // Nhóm task theo taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {

                continue;
            }

            $groupName = $task->taskGroup ? $task->taskGroup->name : 'Khác';

            foreach ($task->taskDetails as $detail) {
                $formattedTasks[$groupName][] = [
                    'id' => $detail->id,
                    'title' => $detail->title,
                    'description' => $detail->description,
                    'dueDate' => Carbon::parse($detail->due_date)->toISOString(), // Định dạng ISO cho dueDate
                    'isRepeating' => !is_null($task->repeatRule), // Kiểm tra có repeatRule không
                    'isImportant' => $detail->priority > 0, // Dựa vào priority
                    'tags' => $task->tags->pluck('name')->toArray() // Lấy danh sách tag
                ];
            }
        }



        return $formattedTasks;
    }
}
