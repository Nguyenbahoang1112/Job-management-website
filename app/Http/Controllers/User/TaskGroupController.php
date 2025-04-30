<?php

namespace App\Http\Controllers\User;

use App\Models\TaskGroup;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Repository\TaskGroup\TaskGroupRepository;
use App\Http\Requests\TaskGroup\CreateTaskGroupRequest;
use App\Http\Resources\TaskGroup\TaskGroupResource;

class TaskGroupController extends Controller
{
    protected $taskGroupRepository;

    public function __construct(TaskGroupRepository $taskGroupRepository)
    {
        $this->middleware('auth:sanctum');
        $this->taskGroupRepository = $taskGroupRepository;
    }

    public function index()
    {
        $userId = auth('sanctum')->user()->id;
        $taskGroups = $this->taskGroupRepository->getAllByUser($userId);
        if ($taskGroups) {
            return ApiResponse::success(TaskGroupResource::collection($taskGroups), 'Get tags successful', 200);
        }
        return ApiResponse::error('Get tags failed', 400);
    }

    //thêm tag
    public function store(CreateTaskGroupRequest $request)
    {
        $userId = auth('sanctum')->user()->id;
        $name = $request->input('name');
        $tag = $this->taskGroupRepository->create([
            'name' => $name,
            'user_id' => $userId
        ]);
        if ($tag) {
            return ApiResponse::success(new TaskGroupResource($tag), 'Create tag successful', 200);
        }
        return ApiResponse::error('Create tag failed', 400);
    }

    //sửa tag trừ tag tạo bởi admin
    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $tag = $this->taskGroupRepository->updateByUser([
            'name' => $name
        ], $id);
        if ($tag) {
            return ApiResponse::success(new TaskGroupResource($tag), 'Update tag successful', 200);
        } else {
            return ApiResponse::error('Update tag failed', 400);
        }
    }

    //xóa tag trừ tag của admin
    public function destroy($id)
    {
        $tag = $this->taskGroupRepository->deleteByUser($id);
        if ($tag) {
            return ApiResponse::success(new TaskGroupResource($tag), 'Delete tag successful', 200);
        }
        return ApiResponse::error('Delete tag failed', 400);
    }
}
