<?php

namespace App\Providers;

use App\Models\DailyInspection;
use App\Models\HazardReport;
use App\Models\HazardResolution;
use App\Models\SafetyTalk;
use App\Models\User;
use App\Observers\DailyInspectionObserver;
use App\Observers\HazardReportObserver;
use App\Observers\HazardResolutionObserver;
use App\Observers\SafetyTalkObserver;
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
        // Global Gates Otorisasi Aplikasi (ST-8)
        Gate::define('access-application', fn (User $user): bool => $user->is_active && in_array($user->role, ['employee', 'admin', 'super_admin'], true));
        Gate::define('manage-administration', fn (User $user): bool => $user->can('access-application') && in_array($user->role, ['admin', 'super_admin'], true));
        Gate::define('submit-inspections', fn (User $user): bool => $user->can('access-application') && $user->role === 'employee');

        // Policies Modul (SPK-7)
        Gate::policy(HazardReport::class, HazardReportPolicy::class);
        Gate::policy(SafetyTalk::class, SafetyTalkPolicy::class);

        // UC-25: Observer untuk trigger perhitungan SAP otomatis
        HazardReport::observe(HazardReportObserver::class);
        DailyInspection::observe(DailyInspectionObserver::class);
        SafetyTalk::observe(SafetyTalkObserver::class);
        HazardResolution::observe(HazardResolutionObserver::class);
    }
}
