<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Repository\Auth\AuthRepository;

class LoginController extends Controller
{
    protected $authRepository;
    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
        $this->middleware('auth:sanctum')->only(['logout', 'getProfile']);
        // $this->middleware('auth:sanctum')->except(['login', 'register', 'checkEmailExist']);
    }
    public function checkEmailExist(Request $request) {
        $email = $request->email;
        if ($this->isEmailExist($email)) {
            return ApiResponse::success( $email ,'Email exist', 200);
        } else {
            return ApiResponse::error('Email not exist', 404);
        }
    }
    public function isEmailExist($email) {
        $user = User::where('email', $email)->first();
        if ($user) {
            return true;
        } else {
            return false;
        }
    }
    public function login(Request $loginRequest)
    {
        // Kiểm tra email có tồn tại không
        if (!$this->isEmailExist($loginRequest->email)) {
            return ApiResponse::error('Email không tồn tại', 404);
        }
    
        // Thử xác thực người dùng
        if (!Auth::attempt($loginRequest->only('email', 'password'))) {
            return ApiResponse::error('Mật khẩu sai', 401);
        }
    
        // Lấy người dùng đã xác thực
        $user = auth('sanctum')->user();
    
        // Xóa các token cũ (tùy chọn)
        $user->tokens()->delete();
    
        // Tạo token mới
        $token = $user->createToken($loginRequest->email);
    
        return ApiResponse::success($token->plainTextToken, 'Đăng nhập thành công', 200);
    }
    public function logout() {
        $user = auth('sanctum')->user();
        $user->tokens()->delete();
        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
    public function getProfile()
    {
        try {
            $user = auth('sanctum')->user();
            return ApiResponse::success(new UserResource($user), 'Get profile successful', 200);
        } catch (\Exception $e) {
            return ApiResponse::error('Get profile failed', 400);
        }
    }
    public function register(Request $registerRequest)
    {
    
        $userCreate = User::create([
            'name' => '123',
            'email' => $registerRequest->email,
            'password' => Hash::make($registerRequest->password),
        ]);
        if ($userCreate) {
            return ApiResponse::success('Register successful', 201);
        } else {
            return ApiResponse::error('Register failed', 400);
        }
    }
}
