<?php

namespace App\Console\Commands;

use App\Interfaces\PaymentUrlProviderInterface;
use App\Services\Payment\Gateways\KashierPaymentGateway;
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
     * The Kashier payment gateway instance.
     */
    protected KashierPaymentGateway $kashierGateway;

    /**
     * The URL provider instance.
     */
    protected PaymentUrlProviderInterface $urlProvider;

    /**
     * Create a new command instance.
     */
    public function __construct(KashierPaymentGateway $kashierGateway, PaymentUrlProviderInterface $urlProvider)
    {
        parent::__construct();
        $this->kashierGateway = $kashierGateway;
        $this->urlProvider = $urlProvider;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->error('This command needs to be updated to work with the new payment system.');
        $this->info('The new SOLID payment system is now active. This command will be updated in a future version.');

        return Command::FAILURE;
    }
}
