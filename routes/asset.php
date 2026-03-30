<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssetController;


// Asset Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->controller(AssetController::class)->group(function () {
    Route::get('/assets/create', 'create')->name('assets.create');
    Route::post('/assets', 'store')->name('assets.store');
    Route::get('/assets/{asset}/edit', 'edit')->name('assets.edit');
    Route::patch('/assets/{asset}', 'update')->name('assets.update');
    Route::delete('/assets/{asset}', 'destroy')->name('assets.destroy');
    });

Route::controller(AssetController::class)->middleware('auth')->group(function() {
    Route::get('/assets', 'index')->name('assets.index');
    Route::get('/assets/{asset}', 'show')->name('assets.show');
});

// Asset Request

// Asset Assignments


// Maintenance Management





