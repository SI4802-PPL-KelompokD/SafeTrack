<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InspectionQuestionController;
use App\Http\Controllers\SapController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WorkAreaController;
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
        $sapSummary = SapMonthlySummary::with('eligibleReward')
            ->where('user_id', $user->id)
            ->where('period', $currentPeriod)
            ->first();

        return view('dashboard', [
            'sapSummary' => $sapSummary,
        ]);
    })->name('dashboard');

    // Admin & Super Admin Only Routes (Kelola Shift, Work Areas, & Pencapaian SAP UC-28)
    Route::middleware('admin')->group(function () {
        Route::get('/data-master', [ShiftController::class, 'index'])->name('admin.data-master');
        Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/sap', [SapController::class, 'index'])->name('sap.index');
        Route::post('/sap/recalculate', [SapController::class, 'recalculate'])->name('sap.recalculate');

        Route::prefix('admin')->name('admin.')->middleware('inspection.inertia')->group(function () {
            Route::resource('work-areas', WorkAreaController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
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
