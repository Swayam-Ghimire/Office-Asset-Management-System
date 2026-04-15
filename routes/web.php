<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'admin'])->middleware(['auth', 'verified', 'role:admin'])->name('dashboard');

Route::get('/employee/dashboard', [DashboardController::class, 'employee'])->middleware(['auth', 'verified', 'role:employee'])->name('employee-dashboard');

Route::get('/home', [AssetController::class, 'index'])->middleware('auth')->name('home');

// Category routes
Route::controller(CategoryController::class)->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/categories', 'index')->name('categories.index');
    Route::post('/admin/category/create', 'store')->name('categories.store');
    Route::put('/admin/categories/update/{category}', 'update')->name('categories.update');
    Route::delete('/admin/categories/destroy/{category}', 'destroy')->name('categories.destroy');
});

// Department routes

Route::controller(DepartmentController::class)->prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/departments', 'index')->name('departments.index');
    Route::post('/departments', 'store')->name('departments.store');
    Route::patch('/departments/{department}', 'update')->name('departments.update');
    Route::delete('/departments/{department}', 'destroy')->name('departments.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/update-picture', [ProfileController::class,
        'updatePicture'])->name('profile.update-picture');
});

require __DIR__.'/auth.php';
require __DIR__.'/asset.php';
require __DIR__.'/user.php';
