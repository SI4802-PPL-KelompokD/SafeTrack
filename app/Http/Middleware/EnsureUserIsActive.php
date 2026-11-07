<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Memastikan akun pengguna yang mengakses endpoint autentikasi berstatus aktif dan tidak di-soft-delete.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! $user->is_active || $user->trashed()) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akun Anda berstatus nonaktif atau telah dihapus. Silakan hubungi Administrator atau tim HSE.',
                    ], 403);
                }

                return redirect()->route('login')
                    ->withErrors([
                        'login' => 'Akun Anda berstatus nonaktif atau telah dihapus. Silakan hubungi Administrator atau tim HSE.',
                    ]);
            }
        }

        return $next($request);
    }
}
