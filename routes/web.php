<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\InspectionQuestionController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SapController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\WorkAreaController;
use App\Models\SapMonthlySummary;
use Illuminate\Http\Request;
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
    $admin = \App\Models\User::where('username', 'admin')->first();
    if ($admin) {
        \Illuminate\Support\Facades\Auth::login($admin);
        return redirect()->route('admin.departments.index');
    }
    return 'User admin belum ada. Jalankan: php artisan db:seed --class=DemoSeeder';
})->name('dev.login');

// Guest Authentication Routes (UC-01 Registrasi, UC-02 Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

// PUBLIK: demo tetap terdaftar agar flag diperiksa sebelum pengalihan pengguna login.
Route::post('/demo/login/{account}', [AuthController::class, 'demoLogin'])->middleware('throttle:6,1')->name('demo.login');

// SEMUA ROLE: identitas dan status akun selalu diperiksa di server.
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Admin & Super Admin Only Routes (Kelola Shift Kerja)
    Route::middleware('admin')->group(function () {
        Route::resource('shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
        });

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
