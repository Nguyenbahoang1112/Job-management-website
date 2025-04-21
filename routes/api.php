<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\Login;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\NoteController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\TaskGroupController;

Route::prefix('/auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/user', [LoginController::class, 'getProfile'])->name('user');
    Route::post('/check-email', [LoginController::class, 'checkEmailExist'])->name('check-email');
});

Route::prefix('/note')->name('note.')->group(function () {
    Route::get('/', [NoteController::class, 'index'])->name('index');
    Route::post('/create', [NoteController::class, 'store'])->name('store');
    Route::put('/update/{id}', [NoteController::class, 'update'])->name('update');
});
