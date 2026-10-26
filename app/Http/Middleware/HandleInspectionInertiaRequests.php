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
                    'isAdmin' => $user->can('manage-administration'),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'navigation' => [
                'dashboard' => route('dashboard'),
                'inspections' => route('daily-inspections.index'),
                'workAreas' => $user && $user->can('manage-administration')
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
