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
        return parent::handle($request, $next, ...User::ADMIN_ROLES);
    }
}
