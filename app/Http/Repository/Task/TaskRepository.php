<?php

namespace App\Http\Repository\Task;

use App\Models\Task;
use App\Models\RepeatRule;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;
use App\Models\TaskDetail;
use Carbon\Carbon;

use App\Models\Team;
class TaskRepository extends BaseRepository
{
    public function __construct(Task $task)
    {
        parent::__construct($task);
    }
    //Lấy tất cả task của admin tạo
    public function getAllUserTaskByAdmin($columns = ['*'], $page = 10)
    {
        $tasks = $this->model
            ->with(['taskDetails' => function ($query) {
                $query->where('status', TaskDetail::STATUS_IN_PROGRESS);
            }])
            ->whereHas('taskDetails', function ($query) {
                $query->where('status', TaskDetail::STATUS_IN_PROGRESS);
            })
            ->where('is_admin_created', Task::TASK_CREATED_BY_ADMIN)
            ->where('team_id', null)
            ->paginate($page);

        return $tasks;
    }

    public function createTaskToUser($user_id)
    {
        return $this->model->create([
            'user_id' => $user_id,
            'is_admin_created' => Task::TASK_CREATED_BY_ADMIN,
        ]);
    }

    public function getAllTeamTaskByAdmin($columns = ['*'], $teamId,$page = 10)
        {
            $tasks = $this->model
                ->where('team_id', $teamId)
                ->paginate($page);
            return $tasks;
        }

    public function getTasksByType(string $type, int $userId)
    {
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        $query = Task::with([
            'taskDetails:id,task_id,title,description,due_date,time,priority,status,parent_id',
            'taskGroup:id,name',
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId)
                  ->orWhereIn('team_id', $teamIds);
        });

        // Xây dựng điều kiện lọc thời gian
        $dateFilter = function ($q) use ($type) {
            $q->where('status', 0); // Chỉ lấy taskDetails chưa hoàn thành
            if ($type === 'today') {
                // Kết hợp due_date và time để tạo thời gian đầy đủ
                $q->where(function ($subQuery) {
                    $subQuery->whereDate('due_date', Carbon::today()->toDateString())
                             ->whereRaw("CONCAT(due_date, ' ', COALESCE(time, '00:00:00')) >= ?", [Carbon::today()->startOfDay()])
                             ->whereRaw("CONCAT(due_date, ' ', COALESCE(time, '00:00:00')) <= ?", [Carbon::today()->endOfDay()]);
                });
            } elseif ($type === 'three_days') {
                $startOfDay = Carbon::today()->startOfDay();
                $endOfDay = Carbon::today()->addDays(3)->endOfDay();
                $q->whereRaw("CONCAT(due_date, ' ', COALESCE(time, '00:00:00')) BETWEEN ? AND ?", [$startOfDay, $endOfDay]);
            } elseif ($type === 'seven_days') {
                $startOfDay = Carbon::today()->startOfDay();
                $endOfDay = Carbon::today()->addDays(7)->endOfDay();
                $q->whereRaw("CONCAT(due_date, ' ', COALESCE(time, '00:00:00')) BETWEEN ? AND ?", [$startOfDay, $endOfDay]);
            }
            // Với type = 'all', không cần thêm điều kiện thời gian
        };

        // Áp dụng điều kiện lọc: lấy task theo type hoặc task trễ hạn
        if ($type !== 'all') {
            $query->where(function ($query) use ($dateFilter) {
                $query->whereHas('taskDetails', $dateFilter) // Task thỏa mãn điều kiện thời gian
                      ->orWhereHas('taskDetails', function ($q) {
                          // Task trễ hạn: thời gian đầy đủ nhỏ hơn thời điểm hiện tại
                          $q->whereRaw("CONCAT(due_date, ' ', COALESCE(time, '00:00:00')) < ?", [Carbon::now()])
                            ->where('status', 0); // Chưa hoàn thành
                      });
            });
        } else {
            // Với type = 'all', lấy tất cả task chưa hoàn thành (bao gồm cả trễ hạn)
            $query->whereHas('taskDetails', function ($q) {
                $q->where('status', 0);
            });
        }

        $tasks = $query->get();

        // Nhóm task theo taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                continue;
            }

            $groupName = $task->taskGroup ? $task->taskGroup->name : 'Khác';
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                // Với task lặp lại, chỉ lấy taskDetail gần nhất với ngày hôm nay
                $taskDetail = $task->taskDetails
                    ->where('status', 0) // Chưa hoàn thành
                    ->sortBy(function ($detail) {
                        // Kết hợp due_date và time để tính khoảng cách thời gian
                        $taskDateTime = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'));
                        return abs($taskDateTime->diffInSeconds(Carbon::now()));
                    })
                    ->first();

                if (!$taskDetail) {
                    continue; // Nếu không có taskDetail chưa hoàn thành, bỏ qua task này
                }

                $dueDate = Carbon::parse($taskDetail->due_date . ' ' . ($taskDetail->time ?? '00:00:00'))
                    ->setTimezone('Asia/Ho_Chi_Minh')
                    ->format('Y-m-d\TH:i:s.000000P');

                $formattedTasks[$groupName][] = [
                    'id' => $taskDetail->id,
                    'title' => $taskDetail->title,
                    'description' => $taskDetail->description,
                    'dueDate' => $dueDate,
                    'isRepeating' => true,
                    'isImportant' => $taskDetail->priority > 0,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
            } else {
                // Với task không lặp lại hoặc type = 'all', lấy tất cả taskDetails
                foreach ($task->taskDetails as $detail) {
                    // Chỉ lấy taskDetails chưa hoàn thành
                    if ($detail->status != 0) {
                        continue;
                    }

                    $dueDate = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'))
                        ->setTimezone('Asia/Ho_Chi_Minh')
                        ->format('Y-m-d\TH:i:s.000000P');

                    $formattedTasks[$groupName][] = [
                        'id' => $detail->id,
                        'title' => $detail->title,
                        'description' => $detail->description,
                        'dueDate' => $dueDate,
                        'isRepeating' => $isRepeating,
                        'isImportant' => $detail->priority > 0,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                }
            }
        }

        return $formattedTasks;
    }


// Hàm mới để lấy tất cả task đã hoàn thành
public function getCompletedTasks(int $userId)
{
    // Lấy danh sách team mà user tham gia
    $teamIds = Team::whereHas('users', function ($query) use ($userId) {
        $query->where('user_id', $userId);
    })->pluck('id')->toArray();

    // Lấy task của user hoặc task thuộc team mà user tham gia
    $query = Task::with([
        'taskDetails' => function ($q) {
            $q->select('id', 'task_id', 'title', 'description', 'due_date', 'time', 'priority', 'status', 'parent_id', 'created_at')
              ->where('status', 1) // Chỉ lấy task đã hoàn thành
              ->orderBy('created_at', 'asc'); // Sắp xếp theo created_at để lấy task đầu tiên nếu có lặp lại
        },
        'taskGroup:id,name',
        'repeatRule:id,task_id,repeat_type',
        'tags:id,name'
    ])
    ->where(function ($query) use ($userId, $teamIds) {
        $query->where('user_id', $userId)
              ->orWhereIn('team_id', $teamIds);
    })
    ->whereHas('taskDetails', function ($q) {
        $q->where('status', 1); // Đảm bảo chỉ lấy task đã hoàn thành
    });

    $tasks = $query->get();

    // Nhóm task theo taskGroup
    $formattedTasks = [];
    foreach ($tasks as $task) {
        $groupName = $task->taskGroup ? $task->taskGroup->name : 'Khác';

        // Nếu không có task_details, vẫn tạo nhóm nhưng để mảng rỗng
        if ($task->taskDetails->isEmpty()) {

            $formattedTasks[$groupName] = $formattedTasks[$groupName] ?? [];
            continue;
        }

        // Với quan hệ 1-1 của repeatRule, chỉ cần lấy task_details đầu tiên
        $detail = $task->taskDetails->first();

        $formattedTasks[$groupName][] = [
            'id' => $detail->id,
            'title' => $detail->title,
            'description' => $detail->description,
            'dueDate' => Carbon::parse($detail->due_date)->toISOString(),
            'isRepeating' => !is_null($task->repeatRule),
            'isImportant' => $detail->priority > 0,
            'tags' => $task->tags->pluck('name')->toArray()
        ];
    }


    return $formattedTasks;
}

}
