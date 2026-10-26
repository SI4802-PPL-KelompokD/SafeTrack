<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInspectionUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->can('submit-inspections')) {
            abort(403, 'Pengisian inspeksi hanya tersedia untuk akun pribadi HSE dan Non-HSE.');
        }

        return $next($request);
    }
}
