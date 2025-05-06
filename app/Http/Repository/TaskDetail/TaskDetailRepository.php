<?php

namespace App\Http\Repository\TaskDetail;

use App\Models\TaskDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Repository\BaseRepository;

class TaskDetailRepository extends BaseRepository
{
    public function __construct(TaskDetail $taskDetail)
    {
        parent::__construct($taskDetail);
    }

    public function getAllByTaskId($id)
    {
        $task_details = $this->model
            ->where('task_id', $id)
            ->paginate(10);
        return $task_details;
    }
    public function createTaskDetail($request, $due_date, $task_id, $parent_id = null, $priority = TaskDetail::PRIORITY_ADMIN)
    {
        return $this->model->create([
            'task_id' => $task_id,
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $due_date,
            'time' => $request->time,
            'priority' => $priority,
            'parent_id' => $parent_id
        ]);
    }

    public function getAllTaskDetail($taskId)
    {
        $taskDetails = $this->model
            ->select('*')
            ->where('task_id', $taskId)
            ->orderBy('parent_id', 'asc')
            ->get();
        return $taskDetails;
    }
    public function getTaskDetailProcess($taskId)
    {
        $taskDetails = $this->model
            ->select('*')
            ->where('task_id', $taskId)
            ->where('status', TaskDetail::STATUS_IN_PROGRESS)
            ->orderBy('parent_id', 'asc')
            ->get();
        return $taskDetails;
    }
    public function getTaskDetailDone($taskId)
    {
        $taskDetails = $this->model
            ->select('id')
            ->where('task_id', $taskId)
            ->where('status', TaskDetail::STATUS_DONE)
            ->get();
        return $taskDetails;
    }

    //cập nhật toàn bộ task có status = processing sang deleting
    public function removeAllToTrash($taskId, $taskDetails)
    {
        //nếu parent_id = null thì task bình thường (không lặp)
        $isRepeat = $taskDetails->first()->parent_id;
        //nếu là task lặp lại thì sẽ phải kiểm tra lần lượt từng task
        if ($isRepeat) {
            //có tồn tại
            DB::transaction(function () use ($taskDetails) {
                foreach ($taskDetails as $taskDetail) {
                    if ($taskDetail->status == TaskDetail::STATUS_IN_PROGRESS) {
                        $taskDetailDelete = $this->model
                            ->where('id', $taskDetail->id)
                            ->update([
                                'status' => '2'
                            ]);
                    }
                }
            });
            return true;
        } else {
            //xóa task không lặp (có thể xóa nếu fe gọi removeToTrash cho task không lặp lại)
            return $this->model
                ->where('task_id', $taskId)
                ->update([
                    'status' => '2'
                ]);
        }

        return false;
    }
    //Cập nhật trạng thái của task detail sang deleting (gửi id của taskdetail)
    public function removeToTrash($id) {
        $taskDetail = $this->model->find($id);
        if ($taskDetail->status == TaskDetail::STATUS_IN_PROGRESS) {
            return $this->model
            ->where('id', $id)
            ->update([
                'status' => '2'
            ]);
        }
        else {
            return false;
        }
    }

}
