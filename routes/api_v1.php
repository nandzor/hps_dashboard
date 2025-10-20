<?php

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DetectionController;
use App\Http\Controllers\Api\V1\ApiCredentialController;
use App\Http\Controllers\CctvLiveStreamController;
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

    // API Credentials management routes
    Route::apiResource('api-credentials', ApiCredentialController::class)->names([
        'index' => 'v1.api-credentials.index',
        'store' => 'v1.api-credentials.store',
        'show' => 'v1.api-credentials.show',
        'update' => 'v1.api-credentials.update',
        'destroy' => 'v1.api-credentials.destroy',
    ]);
});

// API Key protected routes
Route::middleware('api.key')->group(function () {
    // Detection routes
    Route::post('/detection/log', [DetectionController::class, 'store'])->name('v1.detection.store');
    Route::get('/detection/status/{jobId}', [DetectionController::class, 'status'])->name('v1.detection.status');
    Route::get('/detections', [DetectionController::class, 'index'])->name('v1.detections.index');
    Route::get('/detection/summary', [DetectionController::class, 'summary'])->name('v1.detection.summary');

    // Person tracking routes
    Route::get('/person/{reId}', [DetectionController::class, 'showPerson'])->name('v1.person.show');
    Route::get('/person/{reId}/detections', [DetectionController::class, 'personDetections'])->name('v1.person.detections');

    // Branch detection routes
    Route::get('/branch/{branchId}/detections', [DetectionController::class, 'branchDetections'])->name('v1.branch.detections');

    // API Credential test endpoint
    Route::get('/api-credentials/{id}/test', [ApiCredentialController::class, 'test'])->name('v1.api-credentials.test');
});

// CCTV API routes - using session-based auth with proper API response
// Route::middleware('auth:sanctum')->group(function () {
//     // CCTV Stream routes
//     Route::get('/cctv/streams/{deviceId}', [CctvLiveStreamController::class, 'getStreamUrl'])->name('v1.cctv.stream-url');
//     Route::get('/cctv/branches/{branchId}/devices', [CctvLiveStreamController::class, 'getBranchDevices'])->name('v1.cctv.branch-devices');
//     Route::put('/cctv/layouts/{layoutId}/positions/{positionNumber}', [CctvLiveStreamController::class, 'updatePosition'])->name('v1.cctv.update-position');
//     Route::post('/cctv/screenshots/{deviceId}', [CctvLiveStreamController::class, 'captureScreenshot'])->name('v1.cctv.screenshot');
//     Route::post('/cctv/recordings/{deviceId}', [CctvLiveStreamController::class, 'toggleRecording'])->name('v1.cctv.recording');
// });
