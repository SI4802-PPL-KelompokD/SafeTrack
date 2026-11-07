<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyInspectionController;
use App\Http\Controllers\HazardPhotoController;
use App\Http\Controllers\HazardReportController;
use App\Http\Controllers\InspectionQuestionController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SapController;
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
    if ($user->can('manage-administration')) {
        return redirect()->route('shifts.index');
    }

    return redirect()->route('dashboard');
});

// Guest Authentication Routes (UC-01 Registrasi, UC-02 Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

// Authenticated Routes UC-03 Logout & Dashboards
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::middleware('can:access-application')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        Route::middleware('inspection.inertia')->group(function () {
            Route::get('/daily-inspections', [DailyInspectionController::class, 'index'])->name('daily-inspections.index');
            Route::middleware('inspection-user')->group(function () {
                Route::get('/daily-inspections/create', [DailyInspectionController::class, 'create'])->name('daily-inspections.create');
                Route::post('/daily-inspections', [DailyInspectionController::class, 'store'])->name('daily-inspections.store');
            });
        });
        Route::get('/work-areas/{workArea}/active-questions', [InspectionQuestionController::class, 'index'])
            ->name('work-areas.active-questions');

        // Hazard Reports (Laporan Bahaya)
        Route::get('/hazards', [HazardReportController::class, 'index'])->name('hazards.index');
        Route::get('/hazards/mine', [HazardReportController::class, 'mine'])->name('hazards.mine');
        Route::get('/hazards/{hazardReport}', [HazardReportController::class, 'show'])->name('hazards.show');
        Route::get('/hazards/{hazardReport}/photos/{photo}', [HazardPhotoController::class, 'hazard'])->name('hazards.photos.show');
        Route::get('/hazards/{hazardReport}/resolution-photos/{photo}', [HazardPhotoController::class, 'resolution'])->name('hazards.resolution-photos.show');

        // Admin & Super Admin Only Routes (Kelola Shift Kerja)
        Route::middleware('admin')->group(function () {
            Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);

            // SAP (Pencapaian SAP)
            Route::get('/sap', [SapController::class, 'index'])->name('sap.index');
            Route::post('/sap/recalculate', [SapController::class, 'recalculate'])->name('sap.recalculate');

            Route::prefix('admin')->name('admin.')->group(function () {
                // Pengguna Management
                Route::get('pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
                Route::put('pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
                Route::delete('pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
            });

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
});
