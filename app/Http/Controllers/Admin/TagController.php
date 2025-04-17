<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Repository\Admin\Tag\TagRepository;
use App\Http\Requests\Admin\TagRequest\TagRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;

class TagController extends Controller
{
    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function index()
    {
        $tags = $this->tagRepository->getAll();
        return view('admin.tag.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tag.create');
    }

    public function store(TagRequest $request)
    {
        try {
            $this->tagRepository->create([
                'name' => $request->name,
                'user_id' => Auth::id(),
                'is_admin_created' => 1, 
            ]);

            return RedirectResponse::redirectWithMessage('admin.tags.index',[],RedirectResponse::SUCCESS, 'Tạo tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.tags.create',[],RedirectResponse::ERROR, 'Tạo tag thất bại: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $tag = $this->tagRepository->find($id);
            if (!$tag) {
                return RedirectResponse::redirectWithMessage('admin.tags.index',[],RedirectResponse::WARNING ,'Tag không tồn tại.');
            }
            return view('admin.tag.update', compact('tag'));
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.tags.index', [],RedirectResponse::ERROR,'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(TagRequest $request, string $id)
    {
        try {
            $this->tagRepository->update([
                'name' => $request->name,
            ], $id);

            return RedirectResponse::redirectWithMessage('admin.tags.index',[],RedirectResponse::SUCCESS, 'Cập nhật tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.tags.edit',[],RedirectResponse::ERROR, 'Cập nhật tag thất bại: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->tagRepository->delete($id);
            return RedirectResponse::redirectWithMessage('admin.tags.index',[],RedirectResponse::SUCCESS, 'Xóa tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.tags.index',[],RedirectResponse::ERROR, 'Xóa tag thất bại: ' . $e->getMessage());
        }
    }
}
