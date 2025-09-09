<?php

namespace App\Providers;

use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Jobs\ImportCsv;
use App\Models\Setting;
use App\Observers\SettingObserver;
use App\Services\Payment\Gateways\KashierGateway;
use App\Services\Payment\Gateways\PaymobGateway;
use App\Services\Payment\PaymentGatewayFactory;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseImportCsv::class, ImportCsv::class);
        $this->app->bind(\Filament\Actions\Exports\Jobs\ExportCsv::class, \App\Jobs\ExporterCsv::class);

        // Register the Payment Gateway Factory
        $this->app->bind(PaymentGatewayFactoryInterface::class, PaymentGatewayFactory::class);

        // Register individual payment gateways
        $this->app->bind(KashierGateway::class);
        $this->app->bind(PaymobGateway::class);
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
