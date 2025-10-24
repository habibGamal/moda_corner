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
        $defaultGateway = config('payment.default_gateway', 'paymob');

        // Register Kashier-specific services
        $this->app->bind(
            'kashier.validator',
            \App\Services\Payment\Validators\KashierPaymentValidator::class
        );

        $this->app->bind(
            'kashier.urlProvider',
            \App\Services\Payment\UrlProviders\KashierUrlProvider::class
        );

        // Register Paymob-specific services
        $this->app->bind(
            'paymob.validator',
            \App\Services\Payment\Validators\PaymobPaymentValidator::class
        );

        $this->app->bind(
            'paymob.urlProvider',
            \App\Services\Payment\UrlProviders\PaymobUrlProvider::class
        );

        // Bind default validator and URL provider based on default gateway
        $this->app->bind(
            \App\Interfaces\PaymentValidatorInterface::class,
            $defaultGateway === 'paymob'
                ? \App\Services\Payment\Validators\PaymobPaymentValidator::class
                : \App\Services\Payment\Validators\KashierPaymentValidator::class
        );

        $this->app->bind(
            \App\Interfaces\PaymentUrlProviderInterface::class,
            $defaultGateway === 'paymob'
                ? \App\Services\Payment\UrlProviders\PaymobUrlProvider::class
                : \App\Services\Payment\UrlProviders\KashierUrlProvider::class
        );

        // Register Kashier gateway
        $this->app->bind(
            \App\Services\Payment\Gateways\KashierPaymentGateway::class,
            function ($app) {
                return new \App\Services\Payment\Gateways\KashierPaymentGateway(
                    $app->make('kashier.validator'),
                    $app->make('kashier.urlProvider')
                );
            }
        );

        // Register Paymob gateway
        $this->app->bind(
            \App\Services\Payment\Gateways\PaymobPaymentGateway::class,
            function ($app) {
                return new \App\Services\Payment\Gateways\PaymobPaymentGateway(
                    $app->make('paymob.validator'),
                    $app->make('paymob.urlProvider')
                );
            }
        );

        // Register payment processor and strategies
        $this->app->singleton(\App\Services\Payment\PaymentProcessor::class, function ($app) use ($defaultGateway) {
            $processor = new \App\Services\Payment\PaymentProcessor;

            // Register Paymob strategy if it's the default or as an alternative
            if ($defaultGateway === 'paymob') {
                $paymobGateway = $app->make(\App\Services\Payment\Gateways\PaymobPaymentGateway::class);
                $paymobStrategy = new \App\Services\Payment\Strategies\PaymobPaymentStrategy($paymobGateway);
                $processor->addStrategy($paymobStrategy, 'paymob');
            }

            // Register Kashier strategy
            $kashierGateway = $app->make(\App\Services\Payment\Gateways\KashierPaymentGateway::class);
            $kashierStrategy = new \App\Services\Payment\Strategies\KashierPaymentStrategy($kashierGateway);
            $processor->addStrategy($kashierStrategy, 'kashier');

            // Register Cash on Delivery strategy
            $codStrategy = new \App\Services\Payment\Strategies\CashOnDeliveryStrategy;
            $processor->addStrategy($codStrategy, 'cod');

            // Register InstaPay strategy
            $instaPayStrategy = new \App\Services\Payment\Strategies\InstaPayStrategy;
            $processor->addStrategy($instaPayStrategy, 'instapay');

            return $processor;
        });

        // Register Kashier webhook handler
        $this->app->bind(
            \App\Services\Payment\Webhooks\PaymentWebhookHandler::class,
            function ($app) {
                return new \App\Services\Payment\Webhooks\PaymentWebhookHandler(
                    $app->make(\App\Services\Payment\PaymentProcessor::class),
                    $app->make('kashier.validator')
                );
            }
        );

        // Register Paymob webhook handler
        $this->app->bind(
            \App\Services\Payment\Webhooks\PaymobWebhookHandler::class,
            function ($app) {
                return new \App\Services\Payment\Webhooks\PaymobWebhookHandler(
                    $app->make(\App\Services\Payment\PaymentProcessor::class),
                    $app->make('paymob.validator')
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
