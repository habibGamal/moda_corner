<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with Inertia.js frontend
        Inertia::share([
            'settings' => function () {
                return SettingsService::getSiteConfig();
            },
        ]);
    }
}
