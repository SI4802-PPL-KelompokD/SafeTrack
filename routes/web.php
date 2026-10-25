<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SapController;
use App\Http\Controllers\ShiftController;
use App\Models\SapMonthlySummary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    if (in_array($user->role, ['super_admin', 'admin'], true)) {
        return redirect()->route('shifts.index');
    }

    return redirect()->route('dashboard');
});

// Guest Authentication Routes (UC-02 Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated Routes (UC-03 Logout & Dashboards)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $currentPeriod = now()->format('Y-m-01');
        $sapSummary = SapMonthlySummary::where('user_id', $user->id)
            ->where('period', $currentPeriod)
            ->first();

        return view('dashboard', [
            'sapSummary' => $sapSummary,
        ]);
    })->name('dashboard');

    // Admin & Super Admin Only Routes (Kelola Shift & Pencapaian SAP UC-28)
    Route::middleware('admin')->group(function () {
        Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/sap', [SapController::class, 'index'])->name('sap.index');
        Route::post('/sap/recalculate', [SapController::class, 'recalculate'])->name('sap.recalculate');
    });
});

