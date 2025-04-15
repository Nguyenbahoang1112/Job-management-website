<?php

use App\Http\Controllers\Auth\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('/auth')->group(function () {
    Route::post('/login', [Login::class, 'login'])->name('login');
    Route::post('/register', [Login::class, 'register'])->name('register');
    Route::post('/logout', [Login::class, 'logout'])->name('logout')->middleware('auth:sanctum');
});

Route::get('/user', [Login::class, 'getProfile'])->middleware('auth:sanctum');
Route::post('/check-email', [Login::class, 'checkEmailExist'])->name('check-email');
