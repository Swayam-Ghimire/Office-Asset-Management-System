<?php

use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetRequestController;
use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;

// Asset Routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->controller(AssetController::class)->group(function () {
    Route::get('/assets/create', 'create')->name('assets.create');
    Route::post('/assets', 'store')->name('assets.store');
    Route::get('/assets/{asset}/edit', 'edit')->name('assets.edit');
    Route::patch('/assets/{asset}', 'update')->name('assets.update');
    Route::delete('/assets/{asset}', 'destroy')->name('assets.destroy');
});

Route::controller(AssetController::class)->middleware('auth')->group(function () {
    Route::get('/assets', 'index')->name('assets.index');
    Route::get('/assets/{asset}', 'show')->name('assets.show');
});

// Asset Request
Route::controller(AssetRequestController::class)->middleware('auth')->group(function () {
    /*
    POST /requests → Request an asset

GET /requests/my → View my requests

GET /requests/{id} → View request details
    */
    Route::get('/requests', 'index')->name('asset-requests.index');
    Route::get('/requests/create', 'create')->name('asset-requests.create');
    Route::get('/requests/{id}', 'show')->name('asset-requests.show');
    Route::post('/requests', 'store')->name('asset-requests.store');
});

Route::controller(AssetRequestController::class)->middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/requests/{assetRequest}/approve', 'approve')->name('asset-requests.approve');
    Route::put('/requests/{assetRequest}/reject', 'reject')->name('asset-requests.reject');
});

// Asset Assignments
Route::controller(AssetAssignmentController::class)->middleware('auth')->group(function () {
    Route::get('/asset-assignments', 'index')->name('asset-assignments.index');
    Route::post('/asset-assignments/{assetAssignment}/return', 'return')->name('asset-assignments.return');
});

// Maintenance Management
Route::controller(MaintenanceController::class)->prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/maintenance', 'index')->name('maintenance.index');
    Route::get('/maintenance/{id}', 'show')->name('maintenance.show');
    Route::patch('/maintenance/{id}/resolve', 'resolve')->name('maintenance.resolve');
});
Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
