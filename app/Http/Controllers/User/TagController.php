<?php

namespace App\Http\Controllers\User;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Repository\Tag\TagRepository;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Resources\Tag\TagResource;

class TagController extends Controller
{
    protected $tagRepository;
    public function __construct(TagRepository $tagRepository)
    {
        $this->middleware('auth:sanctum');
        $this->tagRepository = $tagRepository;
    }

    public function getAll()
    {
        $userId = auth('sanctum')->user()->id;
        $tags = $this->tagRepository->getAllByUser($userId);
        if ($tags) {
            return ApiResponse::success(TagResource::collection($tags), 'Get tags successful', 200);
        }
        return ApiResponse::error('Get tags failed', 400);
    }
    //thêm tag
    public function store(CreateTagRequest $request)
    {
        $userId = auth('sanctum')->user()->id;
        $name = $request->input('name');
        $tag = $this->tagRepository->create([
            'name' => $name,
            'user_id' => $userId
        ]);
        if ($tag) {
            return ApiResponse::success(new TagResource($tag), 'Create tag successful', 200);
        }
        return ApiResponse::error('Create tag failed', 400);
    }

    //sửa tag trừ tag tạo bởi admin
    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $tag = $this->tagRepository->updateByUser([
            'name' => $name
        ], $id);
        if ($tag) {
            return ApiResponse::success(new TagResource($tag), 'Update tag successful', 200);
        } else {
            return ApiResponse::error('Update tag failed', 400);
        }
    }

    //xóa tag trừ tag của admin
    public function destroy($id)
    {
        $tag = $this->tagRepository->deleteByUser($id);
        if ($tag) {
            return ApiResponse::success(new TagResource($tag), 'Delete tag successful', 200);
        }
        return ApiResponse::error('Delete tag failed', 400);
    }
}
