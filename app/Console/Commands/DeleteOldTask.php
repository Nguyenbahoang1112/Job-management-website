<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Repository\Task\TaskRepository;
use App\Http\Repository\TaskDetail\TaskDetailRepository;

class DeleteOldTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động xóa task trong thùng rác sau 30 ngày';

    /**
     * Execute the console command.
     */
    protected $taskDetailRepository;
    protected $taskRepository;

    public function __construct(TaskDetailRepository $taskDetailRepo, TaskRepository $taskRepo)
    {
        parent::__construct();
        $this->taskDetailRepository = $taskDetailRepo;
        $this->taskRepository = $taskRepo;
    }

    public function handle()
    {
        $expiredTaskDetails = $this->taskDetailRepository->getExpiredFromBin(Carbon::now()->subDays(30));

        $deletedTaskCount = 0;
        $deletedTaskDetailCount = 0;

        foreach ($expiredTaskDetails as $taskDetail) {
            $relatedDetails = $this->taskDetailRepository->getAllTaskDetail($taskDetail->task_id);

            if ($relatedDetails->count() == 1) {
                $this->taskRepository->delete($taskDetail->task_id);
                $deletedTaskCount++;
            } else {
                $this->taskDetailRepository->delete($taskDetail->id);
                $deletedTaskDetailCount++;
            }
        }

        $message = "Đã xóa {$deletedTaskCount} task và {$deletedTaskDetailCount} task detail quá hạn.";

        // Hiện ra console
        $this->info($message);

        // Ghi log
        Log::info('[DeleteOldTask] ' . $message);
    }
}
