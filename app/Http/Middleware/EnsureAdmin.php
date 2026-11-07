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
        abort_unless($request->user()?->can('manage-administration'), 403, 'Akses ditolak. Fitur ini hanya dapat diakses oleh Admin/HSE.');

        return $next($request);
    }
}
