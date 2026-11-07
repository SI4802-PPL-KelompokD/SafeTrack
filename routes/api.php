<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SapApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile REST API Routes (SafeTrack iOS / Swift)
|--------------------------------------------------------------------------
*/

// PUBLIK: autentikasi mobile.
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// SEMUA ROLE: token Sanctum, status akun, dan role valid.
Route::middleware(['auth:sanctum', 'active', 'can:access-application'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    // SAP & Reward / Target Summary
    Route::get('/sap/summary', [SapApiController::class, 'summary'])->name('api.sap.summary');
});
