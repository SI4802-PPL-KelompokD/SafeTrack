<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->is('api/*') ? Auth::guard('sanctum')->user() : $request->user();
        $sessionUserId = $request->hasSession()
            ? $request->session()->get(Auth::guard('web')->getName())
            : null;

        if ($user?->exists || $sessionUserId) {
            $storedUser = User::withTrashed()->find($user?->id ?? $sessionUserId);
            if (! $storedUser || ! $storedUser->is_active || $storedUser->trashed()) {
                return $this->endSession($request, $storedUser, $user?->id ?? $sessionUserId);
            }

            if ($user) {
                $user->setRawAttributes($storedUser->getAttributes(), true);
                $user->unsetRelation('department');
            }
        } elseif ($user && (! $user->is_active || $user->trashed())) {
            return $this->endSession($request, $user, $user->id);
        }

        if ($user?->exists) {
            $user->loadMissing('department');
        }

        return $next($request);
    }

    private function endSession(Request $request, ?User $user, int|string|null $userId): Response
    {
        $user?->tokens()->delete();
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'session_ended');
        }

        return response()->json(['message' => 'Sesi Anda telah berakhir.'], 401);
    }
}
