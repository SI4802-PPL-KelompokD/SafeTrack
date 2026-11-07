<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman form login (UC-02).
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (in_array($user->role, ['super_admin', 'admin'], true)) {
                return redirect()->route('shifts.index');
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login (UC-02).
     * Mendukung login dengan username atau email dan password,
     * serta menolak akun yang berstatus nonaktif.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');

        // Deteksi apakah input berupa format email atau username
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari data pengguna berdasarkan username atau email
        $user = User::where($field, $loginInput)->first();

        // 1. Verifikasi kredensial akun
        if (! $user || ! Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Username/email atau password salah.',
                ]);
        }

        // 2. Verifikasi status akun aktif (Alternatif 4')
        if (! $user->is_active) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'Akun Anda berstatus nonaktif. Silakan hubungi Administrator atau tim HSE.',
                ]);
        }

        // 3. Mulai sesi pengguna
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 4. Arahkan pengguna sesuai dengan role masing-masing
        if ($user->role === 'employee') {
            return redirect()->route('dashboard')
                ->with('success', 'Selamat datang di SafeTrack, ' . $user->name . '.');
        }

        return redirect()->route('shifts.index')
            ->with('success', 'Selamat datang di SafeTrack, ' . $user->name . '.');
    }

    /**
     * Proses mengakhiri sesi pengguna / logout (UC-03).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
