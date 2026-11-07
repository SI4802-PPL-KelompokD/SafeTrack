<?php

namespace App\Providers;

use App\Models\HazardReport;
use App\Models\SafetyTalk;
use App\Models\User;
use App\Policies\HazardReportPolicy;
use App\Policies\SafetyTalkPolicy;
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
        Gate::define('access-application', fn (User $user): bool => $user->is_active && ! $user->trashed() && in_array($user->role, User::ROLES, true));
        Gate::define('access-admin', fn (User $user): bool => $user->can('access-application') && $user->isAdmin());
        Gate::define('manage-administration', fn (User $user): bool => $user->can('access-application') && $user->isAdmin());
        Gate::define('submit-inspections', fn (User $user): bool => $user->can('access-application') && $user->role === 'employee');
        Gate::policy(HazardReport::class, HazardReportPolicy::class);
        Gate::policy(SafetyTalk::class, SafetyTalkPolicy::class);
    }
}
