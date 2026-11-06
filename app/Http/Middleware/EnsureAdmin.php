<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     * Restricts access to admin and super_admin roles.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['super_admin', 'admin'], true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Fitur ini hanya dapat diakses oleh Admin/HSE.'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Akses ditolak. Fitur ini hanya dapat diakses oleh Admin/HSE.');
        }

        return $next($request);
    }
}
