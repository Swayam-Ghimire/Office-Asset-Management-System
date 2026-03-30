<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\UserController;

// User management routes

Route::controller(UserController::class)->middleware(['auth', 'role:admin'])->group(function() {

});