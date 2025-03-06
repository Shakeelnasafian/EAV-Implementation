<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\TimesheetController;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Require Authentication)
Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);  // Get Authenticated User
    Route::post('/logout', [AuthController::class, 'logout']); // Logout User
});

Route::middleware('auth:api')->group(function () {

    // Attributes Resource
    Route::apiResource('attributes', AttributeController::class);

    // Projects Resource
    Route::get('projects/filter', [ProjectController::class, 'filter']);
    Route::apiResource('projects', ProjectController::class);

    // Timesheets Resource
    Route::apiResource('timesheets', TimesheetController::class);
});
