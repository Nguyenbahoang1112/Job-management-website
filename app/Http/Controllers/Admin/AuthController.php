<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function __construct()
    {
        $this->middleware('admin')->only('logout');
    }
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return RedirectResponse::redirectWithMessage('admin.dashboard.index', RedirectResponse::SUCCESS, 'Login successful!');
        }
        return RedirectResponse::redirectWithMessage('admin.auth.login', RedirectResponse::ERROR, 'Invalid credentials');
    }
    /**
     * Handle a logout request to the application.
     */
    public function logout()
    {
        Auth::logout();
        return RedirectResponse::redirectWithMessage('admin.auth.login', RedirectResponse::SUCCESS, 'Logout successful!');
    }
    public function showRegisterForm()
    {
        return view('admin.auth.register');
    }

}
