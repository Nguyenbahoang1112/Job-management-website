<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Repository\Admin\TaskGroup\TaskGroupRepository;
use App\Http\Repository\Admin\User\UserRepository;
use App\Http\Requests\Admin\TaskGroupRequest\TaskGroupStoreRequest;
use App\Http\Requests\Admin\TaskGroupRequest\TaskGroupUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class TaskGroupController extends Controller
{
    protected $taskGroupRepository;
    protected $userRepository;

    public function __construct(TaskGroupRepository $taskGroupRepository,UserRepository $userRepository)
    {
        $this->taskGroupRepository = $taskGroupRepository;
        $this->userRepository = $userRepository;
        $this->middleware('admin');
    }

    public function index()
    {
        $taskGroups = $this->taskGroupRepository->paginate();
        return view('admin.task-group.index', compact('taskGroups'));
    }

    public function create()
    {

        return view('admin.task-group.create');
    }

    public function store(TaskGroupStoreRequest $request)
    {
        try {
            $this->taskGroupRepository->create([
                'name' => $request->name,
                'user_id' => Auth::id(), 
                'is_admin_created' => 1, 
            ]);

            return RedirectResponse::redirectWithMessage('admin.task-groups.index',[],RedirectResponse::SUCCESS, 'Tạo nhóm công việc thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.task-groups.create',[],RedirectResponse::ERROR, 'Tạo nhóm công việc thất bại: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $taskGroup = $this->taskGroupRepository->find($id);
            if (!$taskGroup) {
                return RedirectResponse::redirectWithMessage('admin.task-groups.index', 'Nhóm công việc không tồn tại.');
            }
            $users = $this->userRepository->getAll();
            return view('admin.task-group.update', ['taskGroup' => $taskGroup,'users' => $users]);
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.task-groups.index',[],RedirectResponse::ERROR, 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(TaskGroupUpdateRequest $request, string $id)
    {
        try {
            $this->taskGroupRepository->update([
                'name' => $request->name,
                'user_id' => $request->user_id
            ], $id);

            return RedirectResponse::redirectWithMessage('admin.task-groups.index',[],RedirectResponse::SUCCESS, 'Cập nhật nhóm công việc thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.task-groups.edit',[],RedirectResponse::ERROR, 'Cập nhật thất bại: ' . $e->getMessage());
        }
    }

    public function show(string $id){
        $taskGroup = $this->taskGroupRepository->find($id);
      
        if (!$taskGroup) {
            return RedirectResponse::redirectWithMessage('admin.task-group.index', RedirectResponse::ERROR, 'Không tìm thấy nhóm công việc!');
        }
        return view('admin.task-group.show', ['taskGroup' => $taskGroup])->with(RedirectResponse::SUCCESS, '');
    }
    public function destroy(string $id)
    {
        try {
            $this->taskGroupRepository->delete($id);
            return RedirectResponse::redirectWithMessage('admin.task-groups.index',[],RedirectResponse::SUCCESS, 'Xóa nhóm công việc thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.task-groups.index',[],RedirectResponse::ERROR, 'Xóa thất bại: ' . $e->getMessage());
        }
    }
}
