<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InspectionQuestionController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WorkAreaController;
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
        return view('dashboard');
    })->name('dashboard');

    // Admin & Super Admin Only Routes (Kelola Shift Kerja)
    Route::middleware('admin')->group(function () {
        Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::prefix('admin')->name('admin.')->middleware('inspection.inertia')->group(function () {
            Route::resource('work-areas', WorkAreaController::class)->only(['index', 'store', 'show', 'update']);
            Route::patch('work-areas/{workArea}/toggle-status', [WorkAreaController::class, 'toggleStatus'])
                ->name('work-areas.toggle-status');
            Route::post('work-areas/{workArea}/questions', [InspectionQuestionController::class, 'store'])
                ->name('work-areas.questions.store');
            Route::put('work-areas/{workArea}/questions/{question}', [InspectionQuestionController::class, 'update'])
                ->name('work-areas.questions.update');
            Route::patch('work-areas/{workArea}/questions/{question}/toggle-status', [InspectionQuestionController::class, 'toggleStatus'])
                ->name('work-areas.questions.toggle-status');
            Route::delete('work-areas/{workArea}/questions/{question}', [InspectionQuestionController::class, 'destroy'])
                ->name('work-areas.questions.destroy');
        });
    });
});
