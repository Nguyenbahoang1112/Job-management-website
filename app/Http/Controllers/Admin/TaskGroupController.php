<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Repository\Admin\TaskGroup\TaskGroupRepository;
use App\Http\Requests\Admin\TaskGroupRequest\TaskGroupRequest;
use Illuminate\Support\Facades\Auth;

class TaskGroupController extends Controller
{
    protected $taskGroupRepository;

    public function __construct(TaskGroupRepository $taskGroupRepository)
    {
        $this->taskGroupRepository = $taskGroupRepository;
        $this->middleware('admin');
    }

    public function index()
    {
        $taskGroups = $this->taskGroupRepository->getAll();
        return view('task_groups.list', compact('taskGroups'));
    }

    public function create()
    {
        return view('task_groups.create');
    }

    public function store(TaskGroupRequest $request)
    {
        try {
            $this->taskGroupRepository->create([
                'name' => $request->name,
                'user_id' => Auth::id(), 
            ]);

            return RedirectResponse::success('task-groups.index', 'Tạo nhóm công việc thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::error('task-groups.create', 'Tạo nhóm công việc thất bại: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $taskGroup = $this->taskGroupRepository->find($id);
            if (!$taskGroup) {
                return RedirectResponse::warning('task-groups.index', 'Nhóm công việc không tồn tại.');
            }

            return view('task_groups.edit', compact('taskGroup'));
        } catch (\Exception $e) {
            return RedirectResponse::error('task-groups.index', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(TaskGroupRequest $request, string $id)
    {
        try {
            $this->taskGroupRepository->update([
                'name' => $request->name,
            ], $id);

            return RedirectResponse::success('task-groups.index', 'Cập nhật nhóm công việc thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::error('task-groups.edit', 'Cập nhật thất bại: ' . $e->getMessage(), ['id' => $id]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->taskGroupRepository->delete($id);
            return RedirectResponse::success('task-groups.index', 'Xóa nhóm công việc thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::error('task-groups.index', 'Xóa thất bại: ' . $e->getMessage());
        }
    }
}
