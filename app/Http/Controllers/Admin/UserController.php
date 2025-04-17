<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Repository\Admin\User\UserRepository;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\User\Auth\PasswordUserRequest;
use Illuminate\Support\Facades\Redirect;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->middleware('admin');

        $this->userRepository = $userRepository;
    }
    public function index()
    {
        // Get all users
        $users = $this->userRepository->getAll();
        return view('admin.user.index', compact('users'))->with(RedirectResponse::SUCCESS, 'Danh sách người dùng');
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return RedirectResponse::viewWithMessage('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegisterRequest $registerRequest)
    {
        $this->userRepository->create($registerRequest->all());
        return RedirectResponse::redirectWithMessage('admin.users.index', [], RedirectResponse::SUCCESS, 'Thêm người dùng thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return RedirectResponse::redirectWithMessage('admin.users.index', RedirectResponse::ERROR, 'Không tìm thấy người dùng!');
        }
        return view('admin.user.show', compact('user'))->with(RedirectResponse::SUCCESS, 'Chi tiết người dùng');
    }
    public function changePass(PasswordUserRequest $passwordUserRequest, string $id)
    {
        $result = $this->userRepository->changePass($passwordUserRequest->only('password'), $id);

        if ($result) {
            return RedirectResponse::redirectWithMessage('admin.users.index', RedirectResponse::SUCCESS, 'Đổi mật khẩu thành công!');
        }

        return RedirectResponse::redirectWithMessage('admin.users.create', null, RedirectResponse::ERROR, 'Đổi mật khẩu không thành công!');
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return RedirectResponse::redirectWithMessage('admin.users.index', RedirectResponse::ERROR, 'Không tìm thấy người dùng!');
        }
        return view('admin.user.update', compact('user'))->with(RedirectResponse::SUCCESS, 'Cập nhật người dùng thành công!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        // $request->only(['email', 'status']);
        $user = $this->userRepository->find($id);
        if (!$user) {
            return RedirectResponse::redirectWithMessage('admin.users.index', RedirectResponse::ERROR, 'Không tìm thấy người dùng!');
        }
        $this->userRepository->update($request->only(['email', 'status']), $id);
        return RedirectResponse::redirectWithMessage('admin.users.show', $id, RedirectResponse::SUCCESS, 'Cập nhật người dùng thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return RedirectResponse::redirectWithMessage('admin.users.index', RedirectResponse::ERROR, 'Không tìm thấy người dùng!');
        }
        $this->userRepository->delete($id);
        return RedirectResponse::redirectWithMessage('admin.users.index', $id,RedirectResponse::SUCCESS, 'Xóa người dùng thành công!');
    }
}
