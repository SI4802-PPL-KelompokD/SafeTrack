<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInspectionInertiaRequests extends Middleware
{
    protected $rootView = 'inspection';

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'isAdmin' => in_array($user->role, ['super_admin', 'admin'], true),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'navigation' => [
                'dashboard' => route('dashboard'),
                'dataMaster' => $user && in_array($user->role, ['super_admin', 'admin'], true)
                    ? route('shifts.index')
                    : null,
                'workAreas' => $user && in_array($user->role, ['super_admin', 'admin'], true)
                    ? route('admin.work-areas.index')
                    : null,
                'shifts' => $user && in_array($user->role, ['super_admin', 'admin'], true)
                    ? route('shifts.index')
                    : null,
                'logout' => route('logout'),
            ],
        ];
    }
}
