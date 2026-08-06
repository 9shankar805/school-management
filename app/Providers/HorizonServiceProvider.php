<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * HorizonServiceProvider
 *
 * Wraps Laravel Horizon dashboard access control.
 * The laravel/horizon package must be installed for the dashboard to work:
 *   php /var/www/composer.phar require laravel/horizon
 *
 * Once installed, uncomment App\Providers\HorizonServiceProvider in config/app.php
 * and replace this stub with the full Horizon provider.
 */
class HorizonServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Only register the Horizon gate if the package is actually installed
        if (!class_exists(\Laravel\Horizon\Horizon::class)) {
            return;
        }

        Gate::define('viewHorizon', function ($user) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
            if ($user->hasRole('Admin')) {
                return true;
            }
            $allowed = array_filter(explode(',', env('HORIZON_EMAILS', '')));
            return in_array($user->email, $allowed);
        });
    }

    public function register(): void
    {
        //
    }
}
