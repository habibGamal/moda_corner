<?php

namespace App\Providers;

use App\Jobs\ImportCsv;
use App\Models\Setting;
use App\Observers\SettingObserver;
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

        // Register payment services following Dependency Inversion Principle
        $this->registerPaymentServices();
    }

    /**
     * Register payment services and their dependencies
     */
    private function registerPaymentServices(): void
    {
        // Register Kashier-specific services
        $this->app->bind(
            \App\Interfaces\PaymentValidatorInterface::class,
            \App\Services\Payment\Validators\KashierPaymentValidator::class
        );

        $this->app->bind(
            \App\Interfaces\PaymentUrlProviderInterface::class,
            \App\Services\Payment\UrlProviders\KashierUrlProvider::class
        );

        // Register Kashier gateway
        $this->app->bind(
            \App\Services\Payment\Gateways\KashierPaymentGateway::class,
            function ($app) {
                return new \App\Services\Payment\Gateways\KashierPaymentGateway(
                    $app->make(\App\Interfaces\PaymentValidatorInterface::class),
                    $app->make(\App\Interfaces\PaymentUrlProviderInterface::class)
                );
            }
        );

        // Register payment processor and strategies
        $this->app->singleton(\App\Services\Payment\PaymentProcessor::class, function ($app) {
            $processor = new \App\Services\Payment\PaymentProcessor;

            // Register Kashier strategy for online payment methods
            $kashierGateway = $app->make(\App\Services\Payment\Gateways\KashierPaymentGateway::class);
            $kashierStrategy = new \App\Services\Payment\Strategies\KashierPaymentStrategy($kashierGateway);
            $processor->addStrategy($kashierStrategy, 'kashier');

            // Register Cash on Delivery strategy
            $codStrategy = new \App\Services\Payment\Strategies\CashOnDeliveryStrategy;
            $processor->addStrategy($codStrategy, 'cod');

            return $processor;
        });

        // Register webhook handler
        $this->app->bind(
            \App\Services\Payment\Webhooks\PaymentWebhookHandler::class,
            function ($app) {
                return new \App\Services\Payment\Webhooks\PaymentWebhookHandler(
                    $app->make(\App\Services\Payment\PaymentProcessor::class),
                    $app->make(\App\Interfaces\PaymentValidatorInterface::class)
                );
            }
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
