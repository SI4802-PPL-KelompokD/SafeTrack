<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin extends EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user()?->can('manage-administration'), 403, 'Akses ditolak. Fitur ini hanya dapat diakses oleh Admin/HSE.');

        return $next($request);
    }
}
