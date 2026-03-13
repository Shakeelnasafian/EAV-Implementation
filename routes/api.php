<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public Auth (strict rate limit: 5 req/min) ──────────────────────
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    // ── Protected Routes (60 req/min) ────────────────────────────────────
    Route::middleware(['auth:api', 'throttle:api'])->group(function () {

        // Auth
        Route::get('/user',    [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Attributes — admin only for write operations (enforced via Policy)
        Route::apiResource('attributes', AttributeController::class);

        // Projects — managers/admins write, all authenticated users read
        Route::get('projects/filter', [ProjectController::class, 'filter']);
        Route::post('projects/{project}/restore', [ProjectController::class, 'restore'])
            ->withTrashed();
        Route::apiResource('projects', ProjectController::class);

        // Timesheets — users see their own, admins/managers see all
        Route::post('timesheets/{timesheet}/restore', [TimesheetController::class, 'restore'])
            ->withTrashed();
        Route::apiResource('timesheets', TimesheetController::class);

        // Reports — admin/manager full access; users see their own data
        Route::prefix('reports')->group(function () {
            Route::get('/timesheets', [ReportController::class, 'timesheets']);
        });
    });
});
