<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\Login;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\TagController;
use App\Http\Controllers\User\NoteController;
use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\TaskGroupController;
use App\Http\Controllers\User\SearchHistoryController;
use App\Http\Controllers\User\TeamController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::prefix('/auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/register', [LoginController::class, 'register'])->name('register');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/user', [LoginController::class, 'getProfile'])->name('user');
    Route::post('/check-email', [LoginController::class, 'checkEmailExist'])->name('check-email');
    //forgot password
    Route::post('/send-otp', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPasswordWithOtp']);
});

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->name('verification.verify');
//     Route::post('/email/resend', [VerifyEmailController::class, 'resend'])->name('verification.send');
// });

//Chức năng quản lý notes của user
Route::prefix('/notes')->name('notes.')->group(function () {
    Route::get('/', [NoteController::class, 'index'])->name('index');
    Route::post('/create', [NoteController::class, 'store'])->name('store');
    Route::put('/update/{id}', [NoteController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [NoteController::class, 'destroy'])->name('destroy');
});

//Chức năng quản lý tag của user
Route::prefix('/tags')->name('tags.')->group(function () {
    Route::get('/', [TagController::class, 'getAll'])->name('index');
    Route::post('/create', [TagController::class, 'store'])->name('store');
    Route::put('/update/{id}', [TagController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [TagController::class, 'destroy'])->name('destroy');
});

//Chức năng quản lý group của user
Route::prefix('/task-groups')->name('task-groups.')->group(function () {
    Route::get('/', [TaskGroupController::class, 'index'])->name('index');
    Route::post('/create', [TaskGroupController::class, 'store'])->name('store');
    Route::put('/update/{id}', [TaskGroupController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [TaskGroupController::class, 'destroy'])->name('destroy');
});

// Nhóm route quên mật khẩu OTP
Route::prefix('auth')->group(function () {
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.forgot'); // Gửi OTP
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verifyOtp'); // Xác minh OTP
    Route::post('/reset', [ForgotPasswordController::class, 'resetPasswordWithOtp'])->name('password.reset'); // Đổi mật khẩu
});


// Route::post('/email/resend', [VerifyEmailController::class, 'resend'])
//     ->middleware(['auth:sanctum'])
//     ->name('verification.send');


Route::prefix('/task')->name('task.')->group(function () {
    Route::get('/', [TaskController::class, 'getTasks'])->name('list');
    Route::get('/completed', [TaskController::class, 'getCompletedTasks']);
    Route::get('/deleted', [TaskController::class, 'getDeletedTasks']);
    Route::get('/important', [TaskController::class, 'getImportantTasks'])->name('important');
    Route::get('/search', [TaskController::class, 'searchTasksByTitle'])->name('search');
    Route::get('/user-teams', [TaskController::class, 'getTasksByUserInTeams'])->name('task.user-teams');
    Route::get('/teams-and-groups', [TaskController::class, 'getTeamsAndTaskGroups'])->name('task.teams-and-groups');
    Route::get('/tasks-by-team-or-group', [TaskController::class, 'getTasksByTeamOrGroup'])->name('task.tasks-by-team-or-group');
    Route::get('/tasks-by-tag', [TaskController::class, 'getTasksByTag'])->name('task.tasks-by-tag');
});


Route::prefix('/search-history')->group(function () {
    Route::post('/', [SearchHistoryController::class, 'store'])->name('search-history.store');
    Route::get('/', [SearchHistoryController::class, 'index'])->name('search-history.index');
    Route::delete('/{id}', [SearchHistoryController::class, 'destroy'])->name('search-history.destroy');
});

Route::prefix('/teams')->group(function () {
    Route::get('/user', [TeamController::class, 'getUserTeams'])->name('teams.user');
});
