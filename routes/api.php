<?php

use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\TaskGroupController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::prefix('/auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/user', [LoginController::class, 'getProfile'])->name('user');
    Route::post('/check-email', [LoginController::class, 'checkEmailExist'])->name('check-email');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [VerifyEmailController::class, 'resend'])->name('verification.send');
});

Route::get('/task-groups', [TaskGroupController::class, 'index']);
Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPasswordWithOtp']);
});



// Nhóm route quên mật khẩu OTP
Route::prefix('auth')->group(function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.forgot'); // Gửi OTP
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verifyOtp'); // Xác minh OTP
    Route::post('/reset', [ForgotPasswordController::class, 'resetPasswordWithOtp'])->name('password.reset'); // Đổi mật khẩu
});


Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
    ->middleware(['auth:sanctum'])
    ->name('verification.send');
