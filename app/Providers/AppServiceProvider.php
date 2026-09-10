<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        // Grant all permissions implicitly to users with the 'admin' or 'superadmin' role
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole')) {
                if ($user->hasRole('superadmin', 'backend') || $user->hasRole('admin', 'backend') || $user->hasRole('superadmin') || $user->hasRole('admin')) {
                    return true;
                }
            }

            return null;
        });
    }
}
