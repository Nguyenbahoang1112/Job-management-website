<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class Login extends Controller
{
    public function checkEmailExist($email) {
        $user = User::where('email', $email)->first();
        if ($user) {
            return ApiResponse::success( $email ,'Email exist', 200);
        } else {
            return ApiResponse::error('Email not exist', 404);
        }
    }
    public function login(LoginRequest $loginRequest)
    {
        // dd($loginRequest->email);
        self::checkEmailExist($loginRequest->email);
        if( !Auth::attempt($loginRequest->only('email', 'password'))) {
            return response()->json([
                'message' => 'Login failed',
            ], 401);
        }
        else {
            $user = auth('sanctum')->user();
            dd($user);
            $user->tokens()->delete();
            $token = $user->createToken($loginRequest->email);
            return response()->json([
                'message' => 'Login successful',
                'token' => $token->plainTextToken
            ]);
            return response()->json([
                'message' => 'Login failed',
            ], 401);
        }
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
        $user = auth('sanctum')->user();
        // $user = User::where('email', $user->email)->first();

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }
    public function register(Request $request)
    {
        $userCreate = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

    }
}
