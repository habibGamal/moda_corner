<?php

namespace App\Providers;

use App\Jobs\ImportCsv;
use App\Interfaces\PaymentServiceInterface;
use App\Services\KashierPaymentService;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use App\Observers\SettingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseImportCsv::class, ImportCsv::class);
        $this->app->bind(\Filament\Actions\Exports\Jobs\ExportCsv::class, \App\Jobs\ExporterCsv::class);


        // Bind the PaymentServiceInterface to KashierPaymentService implementation
        $this->app->bind(
            PaymentServiceInterface::class,
            KashierPaymentService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Model::unguard();

        // Register observers
        Setting::observe(SettingObserver::class);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar']);
        });
    }
}
