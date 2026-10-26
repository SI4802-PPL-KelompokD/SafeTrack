<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-application', fn (User $user): bool => $user->is_active && in_array($user->role, ['employee', 'admin', 'super_admin'], true));
        Gate::define('manage-administration', fn (User $user): bool => $user->can('access-application') && in_array($user->role, ['admin', 'super_admin'], true));
        Gate::define('submit-inspections', fn (User $user): bool => $user->can('access-application') && $user->role === 'employee');
    }
}
