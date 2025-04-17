<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaskGroupController;
use App\Http\Controllers\Admin\UserController;


Route::prefix('/admin')->name('task_groups.')->group(function () {
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('showLogin');
        Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login');
        Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/register', [\App\Http\Controllers\Admin\AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [\App\Http\Controllers\Admin\AuthController::class, 'register'])->name('register.post');
    });
    Route::prefix('/user')->name('user.')->group(function () {
        Route::resource('/', \App\Http\Controllers\Admin\UserController::class);
        Route::post('/change-pass/{id}', [\App\Http\Controllers\Admin\UserController::class, 'changePass'])->name('users.change-pass');
    });
    Route::prefix('/dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
    });
    Route::prefix('/task-groups')->name('task_groups.')->group(function () {
        Route::get('/task-groups/create',[TaskGroupController::class,'create']);
        Route::get('/task-groups', [TaskGroupController::class, 'index'])->name('index');
        Route::post('/task-groups', [TaskGroupController::class, 'store'])->name('store');
        Route::get('/task-groups/{id}/edit', [TaskGroupController::class, 'edit'])->name('edit');
        Route::put('/task-groups/{id}', [TaskGroupController::class, 'update'])->name('update');
        Route::delete('/task-groups/{id}', [TaskGroupController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('/tags')->name('tags.')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::get('/create', [TagController::class, 'create'])->name('create');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [TagController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TagController::class, 'update'])->name('update');
        Route::delete('/{id}', [TagController::class, 'destroy'])->name('destroy');
    });

});
