<?php

namespace App\Providers;

use App\Models\HazardReport;
use App\Models\SafetyTalk;
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
        Gate::policy(HazardReport::class, HazardReportPolicy::class);
        Gate::policy(SafetyTalk::class, SafetyTalkPolicy::class);
    }
}
