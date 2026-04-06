<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// User management routes

Route::prefix('admin')->middleware(['auth', 'verified', 'role:admin'])->controller(UserController::class)->group(function () {
    Route::get('/users', 'index')->name('users.index');
    Route::get('/users/create', 'create')->name('users.create');
    Route::post('/users', 'store')->name('users.store');
    Route::get('/users/show/{user}', 'show')->name('users.show');
    Route::get('/users/{user}/edit', 'edit')->name('users.edit');
    Route::patch('/users/{user}', 'update')->name('users.update');
    Route::delete('/users/{user}', 'destroy')->name('users.destroy');
    Route::post('/users/{user}/toggle', 'toggleStatus')->name('users.update-status');
});

// Notifications
Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead'])->name('notifications.read');
Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
Route::get('/notifications',              [NotificationController::class, 'index'])->name('notifications.index');

// New pages (admin)
Route::get('/admin/categories',  [CategoryController::class, 'index'])->name('categories.index');
Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('departments.index');
Route::get('/admin/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');