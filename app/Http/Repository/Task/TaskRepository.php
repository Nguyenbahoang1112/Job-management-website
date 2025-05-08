<?php

namespace App\Http\Repository\Task;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Team;
use App\Models\TaskGroup;
use App\Models\RepeatRule;
use App\Models\TaskDetail;

use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;

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
            'team:id,name', // Thêm thông tin team
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

        $totalTasks = 0;

        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                $groupName = $this->getGroupName($task);
                $formattedTasks[$groupName] = $formattedTasks[$groupName] ?? [];
                continue;
            }

            $groupName = $this->getGroupName($task);
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                // Với task lặp lại, chỉ lấy taskDetail gần nhất với ngày hôm nay
                $taskDetail = $task->taskDetails
                    ->where('status', 0) // Chưa hoàn thành
                    ->sortBy(function ($detail) {
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
                    'task_id' => $taskDetail->task_id,
                    'title' => $taskDetail->title,
                    'description' => $taskDetail->description,
                    'dueDate' => $dueDate,
                    'isRepeating' => true,
                    'isImportant' => $taskDetail->priority > 0,
                    'isAdminCreated' => $task->is_admin_created,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
                $totalTasks++;
            } else {
                // Với task không lặp lại hoặc type = 'all', lấy tất cả taskDetails
                foreach ($task->taskDetails as $detail) {
                    if ($detail->status != 0) {
                        continue;
                    }

                    $dueDate = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'))
                        ->setTimezone('Asia/Ho_Chi_Minh')
                        ->format('Y-m-d\TH:i:s.000000P');

                    $formattedTasks[$groupName][] = [
                        'id' => $detail->id,
                        'task_id' => $taskDetail->task_id,
                        'title' => $detail->title,
                        'description' => $detail->description,
                        'dueDate' => $dueDate,
                        'isRepeating' => $isRepeating,
                        'isImportant' => $detail->priority > 0,
                        'isAdminCreated' => $task->is_admin_created,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                    $totalTasks++;
                }
            }
        }
        $formattedTasks['total_tasks'] = $totalTasks;
        return $formattedTasks;
    }

    // Phương thức phụ để xác định tên nhóm
    private function getGroupName($task)
    {
        if ($task->team) {
            return $task->team->name; // Nhóm theo tên team nếu có
        } elseif ($task->taskGroup) {
            return $task->taskGroup->name; // Nhóm theo tên taskGroup nếu có
        } else {
            return 'Khác'; // Không thuộc team hoặc taskGroup
        }
    }

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
            'team:id,name', // Thêm thông tin team
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
        $totalTasks= 0;
        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            // Nếu không có task_details, vẫn tạo nhóm nhưng để mảng rỗng
            if ($task->taskDetails->isEmpty()) {
                $groupName = $this->getGroupName($task);
                $formattedTasks[$groupName] = $formattedTasks[$groupName] ?? [];
                continue;
            }

            // Lấy task_details đầu tiên (vì đã sắp xếp theo created_at)
            $detail = $task->taskDetails->first();
            $dueDate = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'))
                ->setTimezone('Asia/Ho_Chi_Minh')
                ->format('Y-m-d\TH:i:s.000000P');

            $groupName = $this->getGroupName($task);

            $formattedTasks[$groupName][] = [
                'id' => $detail->id,
                'title' => $detail->title,
                'description' => $detail->description,
                'dueDate' => $dueDate,
                'isRepeating' => !is_null($task->repeatRule),
                'isImportant' => $detail->priority > 0,
                'isAdminCreated' => $task->is_admin_created,
                'tags' => $task->tags->pluck('name')->toArray()
            ];
            $totalTasks++;
        }
        $formattedTasks['total_tasks'] = $totalTasks;
        return $formattedTasks;
    }


    public function getDeletedTasks(int $userId)
    {
        // Lấy danh sách team mà user tham gia
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Lấy task của user hoặc task thuộc team mà user tham gia
        $query = Task::with([
            'taskDetails' => function ($q) {
                $q->select('id', 'task_id', 'title', 'description', 'due_date', 'time', 'priority', 'status', 'parent_id', 'created_at')
                  ->where('status', 2)
                  ->orderBy('created_at', 'asc'); // Sắp xếp theo created_at để lấy task đầu tiên nếu có lặp lại
            },
            'taskGroup:id,name',
            'team:id,name', // Thêm thông tin team
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId)
                  ->orWhereIn('team_id', $teamIds);
        })
        ->whereHas('taskDetails', function ($q) {
            $q->where('status', 2); // Đảm bảo chỉ lấy task với status = 2
        });

        $tasks = $query->get();
        $totalTasks  =0;
        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            // Nếu không có task_details, vẫn tạo nhóm nhưng để mảng rỗng
            if ($task->taskDetails->isEmpty()) {
                $groupName = $this->getGroupName($task);
                $formattedTasks[$groupName] = $formattedTasks[$groupName] ?? [];
                continue;
            }

            // Lấy task_details đầu tiên (vì đã sắp xếp theo created_at)
            $detail = $task->taskDetails->first();

            $dueDate = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'))
                ->setTimezone('Asia/Ho_Chi_Minh')
                ->format('Y-m-d\TH:i:s.000000P');

            $groupName = $this->getGroupName($task);

            $formattedTasks[$groupName][] = [
                'id' => $detail->id,
                'title' => $detail->title,
                'description' => $detail->description,
                'dueDate' => $dueDate,
                'isRepeating' => !is_null($task->repeatRule),
                'isImportant' => $detail->priority > 0,
                'isAdminCreated' => $task->is_admin_created,
                'tags' => $task->tags->pluck('name')->toArray()
            ];
            $totalTasks ++;
        }
        $formattedTasks['total_tasks'] = $totalTasks;
        return $formattedTasks;
    }

    public function getImportantTasks(int $userId)
    {
        // Lấy danh sách team mà user tham gia
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Lấy task của user hoặc task thuộc team mà user tham gia
        $query = Task::with([
            'taskDetails' => function ($q) {
                $q->select('id', 'task_id', 'title', 'description', 'due_date', 'time', 'priority', 'status', 'parent_id')
                  ->where('status', 0) // Chỉ lấy task chưa hoàn thành
                  ->where('priority', '>', 0); // Chỉ lấy task có priority > 0
            },
            'taskGroup:id,name',
            'team:id,name', // Thêm thông tin team
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId)
                  ->orWhereIn('team_id', $teamIds);
        })
        ->whereHas('taskDetails', function ($q) {
            $q->where('status', 0) // Đảm bảo chỉ lấy task chưa hoàn thành
              ->where('priority', '>', 0); // Đảm bảo chỉ lấy task có priority > 0
        });

        $tasks = $query->get();
        $totalTasks = 0;
        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                continue;
            }

            $groupName = $this->getGroupName($task);
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                // Với task lặp lại, chỉ lấy taskDetail gần nhất với ngày hôm nay
                $taskDetail = $task->taskDetails
                    ->where('status', 0)
                    ->where('priority', '>', 0)
                    ->sortBy(function ($detail) {
                        $taskDateTime = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'));
                        return abs($taskDateTime->diffInSeconds(Carbon::now()));
                    })
                    ->first();

                if (!$taskDetail) {
                    continue;
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
                    'isImportant' => true,
                    'isAdminCreated' => $task->is_admin_created,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
                $totalTasks++;
            } else {
                foreach ($task->taskDetails as $detail) {
                    if ($detail->status != 0 || $detail->priority <= 0) {
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
                        'isImportant' => true,
                        'isAdminCreated' => $task->is_admin_created,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                    $totalTasks++;
                }
            }
        }
        $formattedTasks['total_tasks'] = $totalTasks;
        return $formattedTasks;
    }


    public function searchTasksByTitle(int $userId, string $title)
    {
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        $query = Task::with([
            'taskDetails' => function ($q) use ($title) {
                $q->select('id', 'task_id', 'title', 'description', 'due_date', 'time', 'priority', 'status', 'parent_id')
                    ->where('status','!=', 2) // Chỉ lấy task chưa hoàn thành
                    ->where(function ($subQuery) use ($title) {
                        $subQuery->where('title', 'LIKE', "%{$title}%") // Tìm theo title
                                 ->orWhere('description', 'LIKE', "%{$title}%"); // Tìm theo description
                    });
            },
            'taskGroup:id,name',
            'team:id,name', // Thêm thông tin team để nhóm
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId)
                ->orWhereIn('team_id', $teamIds);
        })
        ->whereHas('taskDetails', function ($q) use ($title) {
            $q->where('status', '!=', 2) // Chỉ lấy task chưa hoàn thành
            ->where(function ($subQuery) use ($title) {
                  $subQuery->where('title', 'LIKE', "%{$title}%") // Tìm theo title
                           ->orWhere('description', 'LIKE', "%{$title}%"); // Tìm theo description
            });
        });

        $tasks = $query->get();
        $totalTasks = 0;
        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                continue;
            }

            $groupName = $this->getGroupName($task);
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                // Với task lặp lại, tìm taskDetail khớp với title hoặc description và gần nhất với ngày hiện tại
                $taskDetail = $task->taskDetails
                    ->where('status', '!=', 2)
                    ->where(function ($detail) use ($title) {
                        return stripos($detail->title, $title) !== false
                        || ($detail->description !== null && stripos($detail->description, $title) !== false);
                    })
                    ->sortBy(function ($detail) {
                        $taskDateTime = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'));
                        return abs($taskDateTime->diffInSeconds(Carbon::now()));
                    })
                    ->first();

                if (!$taskDetail) {
                    continue; // Nếu không có taskDetail nào khớp, bỏ qua task này
                }

                $dueDate = Carbon::parse($taskDetail->due_date . ' ' . ($taskDetail->time ?? '00:00:00'))
                    ->setTimezone('Asia/Ho_Chi_Minh')
                    ->format('Y-m-d\TH:i:s.000000P');

                $formattedTasks[$groupName][] = [
                    'id' => $taskDetail->id,
                    'status' => $taskDetail->status,
                    'title' => $taskDetail->title,
                    'description' => $taskDetail->description,
                    'dueDate' => $dueDate,
                    'isRepeating' => true,
                    'isImportant' => $taskDetail->priority > 0,
                    'isAdminCreated' => $task->is_admin_created,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
                $totalTasks ++;
            } else {
                // Với task không lặp lại, lấy tất cả taskDetails khớp với title hoặc description
                foreach ($task->taskDetails as $detail) {
                    if ($detail->status != 0 ||
                        (stripos($detail->title, $title) === false &&
                        ($detail->description === null || stripos($detail->description, $title) === false))) {
                        continue;
                    }

                    $dueDate = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'))
                        ->setTimezone('Asia/Ho_Chi_Minh')
                        ->format('Y-m-d\TH:i:s.000000P');

                    $formattedTasks[$groupName][] = [
                        'id' => $detail->id,
                        'status' => $taskDetail->status,
                        'title' => $detail->title,
                        'description' => $detail->description,
                        'dueDate' => $dueDate,
                        'isRepeating' => $isRepeating,
                        'isImportant' => $detail->priority > 0,
                        'isAdminCreated' => $task->is_admin_created,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                    $totalTasks++;
                }
            }
        }
        $formattedTasks['total_tasks']  = $totalTasks;
        return $formattedTasks;
    }

    public function getTasksByUserInTeams(int $userId)
    {
        // Lấy tất cả team mà user tham gia
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Lấy task của user và task trong các team
        $query = Task::with([
            'taskDetails' => function ($q) {
                $q->select('id', 'task_id', 'title', 'description', 'due_date', 'time', 'priority', 'status', 'parent_id')
                    ->where('status', 0); // Chỉ lấy task chưa hoàn thành
            },
            'team:id,name', // Lấy thông tin team
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId) // Task trực tiếp của user
                    ->orWhereIn('team_id', $teamIds); // Task trong team
        });

        $tasks = $query->get();

        // Nhóm task theo tên team
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                continue;
            }

            $teamName = $task->team ? $task->team->name : ($task->user_id == $userId ? 'Personal' : 'Unknown');
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                $taskDetail = $task->taskDetails
                    ->where('status', 0)
                    ->sortBy(function ($detail) {
                        $taskDateTime = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'));
                        return abs($taskDateTime->diffInSeconds(Carbon::now()));
                    })
                    ->first();

                if (!$taskDetail) {
                    continue;
                }

                $dueDate = Carbon::parse($taskDetail->due_date . ' ' . ($taskDetail->time ?? '00:00:00'))
                    ->setTimezone('Asia/Ho_Chi_Minh')
                    ->format('Y-m-d\TH:i:s.000000P');

                $formattedTasks[$teamName][] = [
                    'id' => $taskDetail->id,
                    'title' => $taskDetail->task->title,
                    'description' => $taskDetail->description,
                    'dueDate' => $dueDate,
                    'isRepeating' => true,
                    'isImportant' => $taskDetail->priority > 0,
                    'isAdminCreated' => $task->is_admin_created,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
            } else {
                foreach ($task->taskDetails as $detail) {
                    if ($detail->status != 0) {
                        continue;
                    }

                    $dueDate = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'))
                        ->setTimezone('Asia/Ho_Chi_Minh')
                        ->format('Y-m-d\TH:i:s.000000P');

                    $formattedTasks[$teamName][] = [
                        'id' => $detail->id,
                        'title' => $detail->title,
                        'description' => $detail->description,
                        'dueDate' => $dueDate,
                        'isRepeating' => $isRepeating,
                        'isImportant' => $detail->priority > 0,
                        'isAdminCreated' => $task->is_admin_created,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                }
            }
        }

        return $formattedTasks;
    }

    // public function getTeamsAndTaskGroups(int $userId)
    // {
    //     // Lấy danh sách team mà user tham gia
    //     $teams = Team::whereHas('users', function ($query) use ($userId) {
    //         $query->where('user_id', $userId);
    //     })->pluck('name')->toArray();

    //     // Lấy danh sách team_id mà user tham gia để lọc task
    //     $teamIds = Team::whereHas('users', function ($query) use ($userId) {
    //         $query->where('user_id', $userId);
    //     })->pluck('id')->toArray();

    //     // Lấy danh sách task group từ các task của user (bao gồm task cá nhân và task trong team)
    //     $taskGroups = Task::where(function ($query) use ($userId, $teamIds) {
    //         $query->where('user_id', $userId)
    //             ->orWhereIn('team_id', $teamIds);
    //         })
    //         ->whereHas('taskGroup') // Chỉ lấy task có taskGroup
    //         ->with('taskGroup:id,name')
    //         ->get()
    //         ->pluck('taskGroup.name')
    //         ->filter() // Loại bỏ null
    //         ->unique() // Loại bỏ trùng lặp
    //         ->values()
    //         ->toArray();

    //     // Kết hợp danh sách team và task group, team trước, task group sau
    //     return [
    //         'teams' => $teams,
    //         'task_groups' => $taskGroups
    //     ];
    // }
    public function getTeamsAndTaskGroups(int $userId)
    {
        // Lấy danh sách team mà user tham gia (gồm id và name)
        $teams = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get(['id', 'name']) // lấy cả id và name
        ->map(function ($team) {
            return [
                'id' => $team->id,
                'name' => $team->name,
            ];
        })
        ->toArray();

        // Lấy danh sách team_id mà user tham gia để lọc task
        $teamIds = array_column($teams, 'id');

        // Lấy danh sách task group từ các task của user (bao gồm task cá nhân và task trong team)
        $taskGroups = $taskGroups = TaskGroup::where('user_id', 2)
            ->orWhere('is_admin_created', 1)
            ->select('id', 'name')
            ->get();

        return [
            'teams' => $teams,
            'task_groups' => $taskGroups
        ];
    }

    public function getTasksByTeamOrGroup(int $userId, $teamId = null, $taskGroupId = null)
    {
        // Lấy danh sách team_id mà user tham gia để kiểm tra quyền truy cập
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Xây dựng query cơ bản
        $query = Task::with([
            'taskDetails:id,task_id,title,description,due_date,time,priority,status,parent_id',
            'taskGroup:id,name',
            'team:id,name',
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->whereHas('taskDetails', function ($q) {
            $q->where('status', 0); // Chỉ lấy task chưa hoàn thành
        });

        // Lọc task theo team_id hoặc task_group_id
        if ($teamId) {
            if (!in_array($teamId, $teamIds)) {
                return []; // Trả về mảng rỗng nếu user không có quyền truy cập team
            }
            $query->where('team_id', $teamId); // Chỉ lấy task thuộc team_id cụ thể
        } elseif ($taskGroupId) {
            $query->where('task_group_id', $taskGroupId)
                  ->where(function ($q) use ($userId, $teamIds) {
                      $q->where('user_id', $userId) // Task cá nhân
                        ->orWhereIn('team_id', $teamIds); // Task trong team của user
                  });
        }

        $tasks = $query->get();

        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                continue;
            }

            $groupName = $this->getGroupName($task);
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                $taskDetail = $task->taskDetails
                    ->where('status', 0)
                    ->sortBy(function ($detail) {
                        $taskDateTime = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'));
                        return abs($taskDateTime->diffInSeconds(Carbon::now()));
                    })
                    ->first();

                if (!$taskDetail) {
                    continue;
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
                    'isAdminCreated' => $task->is_admin_created,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
            } else {
                foreach ($task->taskDetails as $detail) {
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
                        'isAdminCreated' => $task->is_admin_created,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                }
            }
        }

        return $formattedTasks;
    }

    public function checkAdminCreated($id)
    {
        $task = $this->model->find($id);
        return $task->is_admin_created;
    }

    public function getTasksByTag(int $userId, int $tagId)
    {
        // Lấy danh sách team_id mà user tham gia
        $teamIds = Team::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->pluck('id')->toArray();

        // Lấy tất cả task của user có chứa tag_id
        $query = Task::with([
            'taskDetails:id,task_id,title,description,due_date,time,priority,status,parent_id',
            'taskGroup:id,name',
            'team:id,name',
            'repeatRule:id,task_id,repeat_type',
            'tags:id,name'
        ])
        ->where(function ($query) use ($userId, $teamIds) {
            $query->where('user_id', $userId)
                  ->orWhereIn('team_id', $teamIds);
        })
        ->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId); // Lọc task có chứa tag_id
        })
        ->whereHas('taskDetails', function ($q) {
            $q->where('status', 0); // Chỉ lấy task chưa hoàn thành
        });

        $tasks = $query->get();

        // Nhóm task theo team hoặc taskGroup
        $formattedTasks = [];
        foreach ($tasks as $task) {
            if ($task->taskDetails->isEmpty()) {
                continue;
            }

            $groupName = $this->getGroupName($task);
            $isRepeating = !is_null($task->repeatRule);

            if ($isRepeating) {
                $taskDetail = $task->taskDetails
                    ->where('status', 0)
                    ->sortBy(function ($detail) {
                        $taskDateTime = Carbon::parse($detail->due_date . ' ' . ($detail->time ?? '00:00:00'));
                        return abs($taskDateTime->diffInSeconds(Carbon::now()));
                    })
                    ->first();

                if (!$taskDetail) {
                    continue;
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
                    'isAdminCreated' => $task->is_admin_created,
                    'tags' => $task->tags->pluck('name')->toArray()
                ];
            } else {
                foreach ($task->taskDetails as $detail) {
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
                        'isAdminCreated' => $task->is_admin_created,
                        'tags' => $task->tags->pluck('name')->toArray()
                    ];
                }
            }
        }

        return $formattedTasks;
    }
}
