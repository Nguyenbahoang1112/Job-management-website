<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TaskGroupController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserTaskController;

Route::prefix('/admin')->name('admin.')->group(function () {
    //authentication
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('showLogin');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });

    //manage users
    Route::prefix('/users')->name('users.')->group(function () {
        Route::post('/change-pass/{id}', [UserController::class, 'changePass'])->name('change-pass');
        Route::get('/search/email', [UserController::class, 'searchByEmail'])->name('search');

        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    //dashboard
    Route::prefix('/dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
    });

    //manage task groups
    Route::prefix('/task-groups')->name('task-groups.')->group(function () {
        Route::get('/', [TaskGroupController::class, 'index'])->name('index');
        Route::get('/create',[TaskGroupController::class,'create'])->name('create');
        Route::get('/{id}', [TaskGroupController::class, 'show'])->name('show');
        Route::post('/', [TaskGroupController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [TaskGroupController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TaskGroupController::class, 'update'])->name('update');
        Route::delete('/{id}', [TaskGroupController::class, 'destroy'])->name('destroy');
    });

    //manage tags
    Route::prefix('/tags')->name('tags.')->group(function () {
        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::get('/create', [TagController::class, 'create'])->name('create');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('/{id}', [TagController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TagController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TagController::class, 'update'])->name('update');
        Route::delete('/{id}', [TagController::class, 'destroy'])->name('destroy');
    });
    Route::prefix('/tasks')->name('tasks.')->group(function () {
        Route::get('/', [UserTaskController::class, 'index'])->name('index');
        Route::get('/create', [UserTaskController::class, 'create'])->name('create');

    });
    Route::prefix('/teams')->name('teams.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::get('/create', [TeamController::class, 'create'])->name('create');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('/{id}', [TeamController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{id}', [TeamController::class, 'destroy'])->name('destroy');
        Route::get('teams/add-users-to-team', [TeamController::class, 'showAddUsersForm'])->name('showAddUsersForm');
        Route::post('teams/add-users-to-team', [TeamController::class, 'addUsersToTeam'])->name('addUsersToTeam');
    });

});
