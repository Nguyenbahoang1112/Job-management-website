<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        // Handle login logic here
    }

    /**
     * Handle a logout request to the application.
     */
    public function logout()
    {
        // Handle logout logic here
    }
    public function showRegisterForm()
    {
        return view('admin.auth.register');
    }

}
