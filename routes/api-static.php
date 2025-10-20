<?php

use App\Http\Controllers\Api\V1\StaticAuthController;
use App\Http\Controllers\Api\V1\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Static Token API Routes
|--------------------------------------------------------------------------
|
| Routes yang menggunakan static token authentication.
| Header: Authorization: Bearer your-static-token
|
*/

// Public info endpoint (no auth required)
Route::get('/info', [StaticAuthController::class, 'info']);

Route::middleware('static.token')->group(function () {
    // Validate token
    Route::get('/validate', [StaticAuthController::class, 'validate']);
    
    // Test endpoints untuk static token
    Route::get('/test', [TestController::class, 'index']);
    Route::get('/test/ping', [TestController::class, 'ping']);
    Route::post('/test/echo', [TestController::class, 'echo']);
    Route::get('/test/{id}', [TestController::class, 'show']);
    Route::post('/test', [TestController::class, 'store']);
});

