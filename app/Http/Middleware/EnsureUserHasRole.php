<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        abort_unless(
            $user && $user->can('access-application')
                && in_array($user->role, array_intersect($roles, User::ROLES), true),
            403,
            'Akses ditolak.',
        );

        return $next($request);
    }
}
