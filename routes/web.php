<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TaskGroupController;
use App\Http\Controllers\Admin\UserController;
Route::get('/task-groups/create',[TaskGroupController::class,'create']);
Route::get('/task-groups', [TaskGroupController::class, 'index'])->name('task_groups.index');
Route::post('/task-groups', [TaskGroupController::class, 'store'])->name('task_groups.store');
Route::get('/task-groups/{id}/edit', [TaskGroupController::class, 'edit'])->name('task_groups.edit');
Route::put('/task-groups/{id}', [TaskGroupController::class, 'update'])->name('task_groups.update');
Route::delete('/task-groups/{id}', [TaskGroupController::class, 'destroy'])->name('task_groups.destroy');

Route::prefix('admin')->name('tags.')->middleware(['auth'])->group(function () {
    Route::get('/tags', [TagController::class, 'index'])->name('index');
    Route::get('/tags/create', [TagController::class, 'create'])->name('create');
    Route::post('/tags', [TagController::class, 'store'])->name('store');
    Route::get('/tags/{id}/edit', [TagController::class, 'edit'])->name('edit');
    Route::put('/tags/{id}', [TagController::class, 'update'])->name('update');
    Route::delete('/tags/{id}', [TagController::class, 'destroy'])->name('destroy');
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
});