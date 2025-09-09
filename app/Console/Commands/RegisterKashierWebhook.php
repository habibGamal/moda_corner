<?php

namespace App\Console\Commands;

use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Services\Payment\Gateways\KashierGateway;
use Exception;
use Illuminate\Console\Command;

class RegisterKashierWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kashier:register-webhook
                            {--force : Force registration even if webhook is already registered}
                            {--check : Only check if webhook is registered without registering}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register webhook URL with Kashier payment system';

    /**
     * The Payment Gateway Factory instance.
     */
    protected PaymentGatewayFactoryInterface $gatewayFactory;

    /**
     * Create a new command instance.
     */
    public function __construct(PaymentGatewayFactoryInterface $gatewayFactory)
    {
        parent::__construct();
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Kashier Webhook Registration');
        $this->info('============================');

        try {
            // Get Kashier gateway
            $kashierGateway = $this->gatewayFactory->createGateway('kashier');

            if (! ($kashierGateway instanceof KashierGateway)) {
                $this->error('Failed to get Kashier gateway instance.');

                return Command::FAILURE;
            }

            // Check if we only want to check registration status
            if ($this->option('check')) {
                return $this->checkWebhookStatus($kashierGateway);
            }

            // Display current configuration
            $this->displayConfiguration($kashierGateway);

            $this->warn('Note: Webhook registration functionality needs to be implemented in the new KashierGateway.');
            $this->info('This command is temporarily disabled during the architecture migration.');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ Command failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Display current Kashier configuration
     */
    protected function displayConfiguration(KashierGateway $kashierGateway): void
    {
        $this->info('Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Gateway Name', $kashierGateway->getGatewayName()],
                ['Webhook URL', $kashierGateway->getWebhookUrl()],
                ['Supported Methods', implode(', ', $kashierGateway->getSupportedPaymentMethods())],
            ]
        );
        $this->newLine();
    }

    /**
     * Check webhook registration status
     */
    protected function checkWebhookStatus(KashierGateway $kashierGateway): int
    {
        $this->info('Checking webhook registration status...');

        $this->warn('Webhook status checking needs to be implemented in the new KashierGateway.');
        $this->info('Webhook URL: '.$kashierGateway->getWebhookUrl());

        return Command::SUCCESS;
    }

    /**
     * Confirm webhook registration with user
     */
    protected function confirmRegistration(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        return $this->confirm(
            'Do you want to register the webhook URL with Kashier?',
            true
        );
    }

    /**
     * Display success message
     */
    protected function success(string $message): void
    {
        $this->line("<fg=green>$message</>");
    }
}
