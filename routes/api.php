<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TaskGroupController;

Route::prefix('/auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/user', [LoginController::class, 'getProfile'])->name('user');
    Route::post('/check-email', [LoginController::class, 'checkEmailExist'])->name('check-email');
});


Route::get('/task-groups', [TaskGroupController::class, 'index']);
