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
        Gate::define('viewAuditLogs', fn (User $user): bool => $user->isSuperAdmin());
        Gate::define('manageHomepage', fn (User $user): bool => $user->isSuperAdmin());
    }
}
