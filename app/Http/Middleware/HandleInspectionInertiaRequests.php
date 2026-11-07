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
                    'roleLabel' => $user->roleLabel(),
                    'isAdmin' => $user->can('manage-administration'),
                ] : null,
            ],
            'demoLogin' => config('app.demo_login') === true,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'navigation' => [
                'dashboard' => route('dashboard'),
                'employees' => $user?->can('access-admin') ? route('admin.pengguna.index') : null,
                'hazards' => route('hazards.index'),
                'inspections' => route('daily-inspections.index'),
                'dataMaster' => $user?->can('access-admin') ? route('shifts.index') : null,
                'workAreas' => $user && $user->can('access-admin')
                    ? route('admin.work-areas.index')
                    : null,
                'shifts' => $user && $user->can('manage-administration')
                    ? route('shifts.index')
                    : null,
                'logout' => route('logout'),
            ],
        ];
    }
}
