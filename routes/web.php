<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DashboardController;
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
