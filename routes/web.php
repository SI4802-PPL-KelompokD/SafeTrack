<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyInspectionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HazardPhotoController;
use App\Http\Controllers\HazardReportController;
use App\Http\Controllers\InspectionQuestionController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SapController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WorkAreaController;
use App\Http\Middleware\RequireAdminActionIdentity;
use App\Models\SapMonthlySummary;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// PUBLIK: autentikasi dan pengalihan awal.
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

// Shortcut Dev: Akses langsung sebagai Admin (Kelola Departemen) saat login dalam pengembangan
Route::get('/dev-login', function () {
    $admin = User::where('username', 'admin')->first();
    if ($admin) {
        Auth::login($admin);

        return redirect()->route('admin.departments.index');
    }

    return 'User admin belum ada. Jalankan: php artisan db:seed --class=DemoSeeder';
})->name('dev.login');

// Guest Authentication Routes (UC-01 Registrasi, UC-02 Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
});

// Authenticated Routes (UC-03 Logout & Dashboards)
Route::post('/demo/login/{account}', [AuthController::class, 'demoLogin'])->middleware('throttle:6,1')->name('demo.login');

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $currentPeriod = now()->format('Y-m-01');
        $sapSummary = null;
        try {
            $sapSummary = SapMonthlySummary::with('eligibleReward')
                ->where('user_id', $user->id)
                ->where('period', $currentPeriod)
                ->first();
        } catch (Throwable) {
            // Fallback aman jika tabel atau kolom reward belum ter-migrasi penuh
        }

        return view('dashboard', [
            'sapSummary' => $sapSummary,
        ]);
    })->name('dashboard');

    // Daily Inspections
    Route::get('/daily-inspections', [DailyInspectionController::class, 'index'])->name('daily-inspections.index');
    Route::middleware('inspection-user')->group(function () {
        Route::get('/daily-inspections/create', [DailyInspectionController::class, 'create'])->name('daily-inspections.create');
        Route::post('/daily-inspections', [DailyInspectionController::class, 'store'])->name('daily-inspections.store');
    });
    Route::get('/work-areas/{workArea}/active-questions', [InspectionQuestionController::class, 'index'])
        ->name('work-areas.active-questions');

    // ---- PBI-13: Laporan Saya, Daftar Pelaporan, Detail Laporan (semua peran login) ----
    Route::get('/laporan-saya', [HazardReportController::class, 'mine'])->name('hazards.mine');
    // PBI-11 - Pembuatan Laporan Bahaya
Route::middleware('inspection.inertia')->group(function () {
    Route::get('/laporan-bahaya/create',
        [HazardReportController::class, 'create']
    )->name('hazards.create');

    Route::post('/laporan-bahaya',
        [HazardReportController::class, 'store']
    )->name('hazards.store');
});
    Route::get('/laporan-bahaya', [HazardReportController::class, 'index'])->name('hazards.index');
    Route::get('/laporan-bahaya/{hazardReport}', [HazardReportController::class, 'show'])
        ->withTrashed()->name('hazards.show');
    Route::get('/laporan-bahaya/{hazardReport}/foto/{photo}', [HazardPhotoController::class, 'hazard'])
        ->withTrashed()->name('hazards.photos.show');
    Route::get('/laporan-bahaya/{hazardReport}/bukti/{photo}', [HazardPhotoController::class, 'resolution'])
        ->withTrashed()->name('hazards.resolution-photos.show');

    // Admin & Super Admin Only Routes
    Route::middleware('admin')->group(function () {
        Route::get('/data-master', [ShiftController::class, 'index'])->name('admin.data-master');
        Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');

        Route::middleware(RequireAdminActionIdentity::class)->group(function () {
            Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store');
            Route::put('shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
            Route::patch('shifts/{shift}/toggle-status', [ShiftController::class, 'toggleStatus'])
                ->name('shifts.toggle-status');
        });

        // SAP (Pencapaian SAP UC-28)
        Route::get('/sap', [SapController::class, 'index'])->name('sap.index');
        Route::post('/sap/recalculate', [SapController::class, 'recalculate'])->name('sap.recalculate');

        Route::prefix('admin')->name('admin.')->group(function () {
            // Pengguna Management
            Route::get('pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
            Route::put('pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
            Route::delete('pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
            Route::post('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset-password');

            // Departments
            Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
        });

        Route::prefix('admin')->name('admin.')->middleware(RequireAdminActionIdentity::class)->group(function () {
            Route::resource('work-areas', WorkAreaController::class)->only(['index', 'store', 'show', 'update']);
            Route::patch('work-areas/{workArea}/toggle-status', [WorkAreaController::class, 'toggleStatus'])
                ->name('work-areas.toggle-status');
            Route::post('work-areas/{workArea}/questions', [InspectionQuestionController::class, 'store'])
                ->name('work-areas.questions.store');
            Route::put('work-areas/{workArea}/questions/{question}', [InspectionQuestionController::class, 'update'])
                ->name('work-areas.questions.update');
            Route::patch('work-areas/{workArea}/questions/{question}/toggle-status', [InspectionQuestionController::class, 'toggleStatus'])
                ->name('work-areas.questions.toggle-status');
        });
    });
});
