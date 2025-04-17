<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaskGroupController;
use App\Http\Controllers\Admin\UserController;

Route::prefix('admin/auth')->name('admin.auth.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('showLogin');
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login');
    Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
    Route::get('/register', [\App\Http\Controllers\Admin\AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Admin\AuthController::class, 'register'])->name('register.post');
});
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
Route::prefix('admin')->name('admin.')->group(function () {

    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/change-pass/{id}', [\App\Http\Controllers\Admin\UserController::class, 'changePass'])->name('users.change-pass');

    Route::resource('/tags',TagController::class);
    Route::resource('/task-groups',TaskGroupController::class);
});


  

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
});