<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Repository\Admin\User\UserRepository;
use App\Http\Requests\Admin\UserRequest\UserRequest;
use App\Helpers\RedirectResponse;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $users = $this->userRepository->getAll();
        return view('admin.users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = $this->userRepository->find($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequest $request, $id)
    {
        try {
            $this->userRepository->update($request->validated(), $id);
            return RedirectResponse::success('admin.users.index', 'Cập nhật người dùng thành công');
        } catch (\Exception $e) {
            return RedirectResponse::error('admin.users.edit', 'Lỗi: ' . $e->getMessage(), ['id' => $id]);
        }
    }

    public function destroy($id)
    {
        try {
            $this->userRepository->delete($id);
            return RedirectResponse::success('admin.users.index', 'Xóa người dùng thành công');
        } catch (\Exception $e) {
            return RedirectResponse::error('admin.users.index', 'Lỗi khi xóa: ' . $e->getMessage());
        }
    }
}
