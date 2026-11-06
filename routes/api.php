<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SapApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile REST API Routes (SafeTrack iOS / Swift)
|--------------------------------------------------------------------------
*/

// Public Authentication Route
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Authenticated Routes (Requires Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // SAP & Reward / Target Summary
    Route::get('/sap/summary', [SapApiController::class, 'summary'])->name('api.sap.summary');
});
