<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Repository\Tag\TagRepository;
use App\Http\Repository\Admin\User\Repository;
use App\Http\Repository\User\UserRepository;
use App\Http\Requests\Admin\TagRequest\TagStoreRequest;
use App\Http\Requests\Admin\TagRequest\TagUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;

class TagController extends Controller
{
    protected $tagRepository;
    protected $userRepository;

    public function __construct(TagRepository $tagRepository, UserRepository $userRepository)
    {
        $this->tagRepository = $tagRepository;
        $this->userRepository = $userRepository;
        $this->middleware('admin');
    }

    public function index()
    {
        $tags = $this->tagRepository->paginate();
        return view('admin.tag.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tag.create');
    }

    public function store(TagStoreRequest $request)
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
            $users = $this->userRepository->getAll();
            return view('admin.tag.update', ['tag' => $tag, 'users' => $users]);
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.tags.index', [],RedirectResponse::ERROR,'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(TagUpdateRequest $request, string $id)
    {
        try {
            $this->tagRepository->update([
                'name' => $request->name,
                'user_id' => $request->user_id
            ], $id);


            return RedirectResponse::redirectWithMessage('admin.tags.index',[],RedirectResponse::SUCCESS, 'Cập nhật tag thành công!');
        } catch (\Exception $e) {
            return RedirectResponse::redirectWithMessage('admin.tags.edit',[],RedirectResponse::ERROR, 'Cập nhật tag thất bại: ' . $e->getMessage());
        }
    }

    public function show(string $id){
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            return RedirectResponse::redirectWithMessage('admin.tags.index', RedirectResponse::ERROR, 'Không tìm thấy thẻ!');
        }
        return view('admin.tag.show', ['tag' => $tag])->with(RedirectResponse::SUCCESS, '');
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
