<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Pesan yang sama untuk email terdaftar maupun tidak, agar form ini
     * tidak bisa dipakai untuk menebak email mana yang terdaftar.
     */
    private const LINK_SENT_MESSAGE = 'Jika email tersebut terdaftar dan akunnya aktif, tautan reset password telah dikirim. Silakan periksa inbox atau folder spam Anda.';

    private const INVALID_LINK_MESSAGE = 'Tautan reset password tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.';

    /**
     * Tampilkan form lupa password (UC-04 langkah 1).
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset berisi token berbatas waktu ke email terdaftar (UC-04 langkah 2).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        $developmentResetUrl = null;

        Password::sendResetLink(
            ['email' => $validated['email'], 'is_active' => true],
            function (User $user, string $token) use (&$developmentResetUrl): void {
                $notification = new ResetPasswordNotification($token);
                $user->notify($notification);

                if ($this->shouldShowResetLinkOnScreen()) {
                    $developmentResetUrl = $notification->resetUrl($user);
                }
            },
        );

        return back()
            ->with('success', self::LINK_SENT_MESSAGE)
            ->with('dev_reset_url', $developmentResetUrl);
    }

    /**
     * Tampilkan form password baru dari tautan email (UC-04 langkah 3).
     */
    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Simpan password baru jika token valid dan belum kedaluwarsa (UC-04 langkah 4).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
        ]);

        $status = Password::reset(
            [...$validated, 'is_active' => true],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => self::INVALID_LINK_MESSAGE]);
        }

        return redirect()->route('login')
            ->with('success', 'Password berhasil diperbarui. Silakan masuk dengan password baru Anda.');
    }

    /**
     * Bantuan khusus pengembangan lokal: jika email hanya ditulis ke log,
     * tampilkan tautan reset di layar agar anggota tim bisa mencoba tanpa SMTP.
     */
    private function shouldShowResetLinkOnScreen(): bool
    {
        return app()->environment('local') && config('mail.default') === 'log';
    }
}
