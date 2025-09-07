<?php

namespace App\Console\Commands;

use App\Services\KashierPaymentService;
use Illuminate\Console\Command;
use Exception;

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
     * The Kashier payment service instance.
     */
    protected KashierPaymentService $kashierService;

    /**
     * Create a new command instance.
     */
    public function __construct(KashierPaymentService $kashierService)
    {
        parent::__construct();
        $this->kashierService = $kashierService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Kashier Webhook Registration');
        $this->info('============================');

        // Check if we only want to check registration status
        if ($this->option('check')) {
            return $this->checkWebhookStatus();
        }

        // Display current configuration
        $this->displayConfiguration();

        // Check if webhook is already registered (unless forced)
        if (!$this->option('force')) {
            $isRegistered = $this->kashierService->isWebhookRegistered();

            if ($isRegistered === true) {
                $this->warn('Webhook is already registered with Kashier.');
                $this->info('Use --force option to re-register or --check to verify status.');
                return Command::SUCCESS;
            } elseif ($isRegistered === null) {
                $this->warn('Unable to check webhook registration status.');
                $this->info('Proceeding with registration...');
            }
        }

        // Confirm registration
        if (!$this->confirmRegistration()) {
            $this->info('Registration cancelled.');
            return Command::SUCCESS;
        }

        // Register the webhook
        try {
            $this->info('Registering webhook URL with Kashier...');

            $response = $this->kashierService->registerWebhookUrl();

            if (isset($response['status']) && $response['status'] === 200) {
                $this->success('✅ Webhook URL registered successfully!');

                // Display registration details
                if (isset($response['body']['webhook'])) {
                    $webhook = $response['body']['webhook'];
                    $this->info('Registration Details:');
                    $this->table(
                        ['Property', 'Value'],
                        [
                            ['URL', $webhook['url'] ?? 'N/A'],
                            ['Enabled', $webhook['isEnabled'] ? 'Yes' : 'No'],
                            ['Merchant ID', $webhook['MID'] ?? 'N/A'],
                        ]
                    );
                }

                return Command::SUCCESS;
            } else {
                $this->error('❌ Registration failed. Check logs for details.');
                return Command::FAILURE;
            }

        } catch (Exception $e) {
            $this->error('❌ Registration failed: ' . $e->getMessage());
            $this->info('Check the logs for more details.');
            return Command::FAILURE;
        }
    }

    /**
     * Display current Kashier configuration
     */
    protected function displayConfiguration(): void
    {
        $this->info('Current Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Mode', $this->kashierService->getMode()],
                ['Merchant ID', $this->kashierService->getMerchantId()],
                ['API Base URL', $this->kashierService->getApiBaseUrl()],
                ['Webhook URL', $this->kashierService->getWebhookUrl()],
            ]
        );
        $this->newLine();
    }

    /**
     * Check webhook registration status
     */
    protected function checkWebhookStatus(): int
    {
        $this->info('Checking webhook registration status...');

        $isRegistered = $this->kashierService->isWebhookRegistered();

        if ($isRegistered === true) {
            $this->success('✅ Webhook is registered and enabled.');
        } elseif ($isRegistered === false) {
            $this->warn('⚠️  Webhook is not registered or not enabled.');
        } else {
            $this->error('❌ Unable to check webhook registration status.');
            return Command::FAILURE;
        }

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
