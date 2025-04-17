<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Repository\Admin\Tag\TagRepository;
use App\Http\Requests\Admin\TagRequest\TagRequest;
use Illuminate\Support\Facades\Auth;

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
        return view('tags.list', compact('tags'));
    }

    public function create()
    {
        return view('tags.create');
    }

    public function store(TagRequest $request)
    {
        try {
            $this->tagRepository->create([
                'name' => $request->name,
                'user_id' => Auth::id(),
                'is_admin_created' => 1, 
            ]);

            return RedirectResponse::success('tags.index', 'Tạo tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::error('tags.create', 'Tạo tag thất bại: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        try {
            $tag = $this->tagRepository->find($id);
            if (!$tag) {
                return RedirectResponse::warning('tags.index', 'Tag không tồn tại.');
            }

            return view('tags.edit', compact('tag'));
        } catch (\Exception $e) {
            return RedirectResponse::error('tags.index', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(TagRequest $request, string $id)
    {
        try {
            $this->tagRepository->update([
                'name' => $request->name,
            ], $id);

            return RedirectResponse::success('tags.index', 'Cập nhật tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::error('tags.edit', 'Cập nhật tag thất bại: ' . $e->getMessage(), ['id' => $id]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->tagRepository->delete($id);
            return RedirectResponse::success('tags.index', 'Xóa tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::error('tags.index', 'Xóa tag thất bại: ' . $e->getMessage());
        }
    }
}
