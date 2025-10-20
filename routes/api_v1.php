<?php

use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the CCTV Dashboard API
| Base URL: /api/v1/
| Status: Current (Latest)
| Released: October 2025
|
*/

// User management routes - using session-based auth with proper API response
Route::middleware('api.session|auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->names([
        'index' => 'v1.users.index',
        'store' => 'v1.users.store',
        'show' => 'v1.users.show',
        'update' => 'v1.users.update',
        'destroy' => 'v1.users.destroy',
    ]);

    Route::get('/users/pagination/options', [UserController::class, 'paginationOptions'])->name('v1.users.pagination.options');
});

