<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\AdminNotificationService;
use Illuminate\Console\Command;

class TestAdminNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:admin-notifications {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test admin notifications for order placement and return requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');

        try {
            $order = Order::findOrFail($orderId);
            $adminNotificationService = app(AdminNotificationService::class);

            $this->info("Testing notifications for Order #{$order->id}");
            $this->info('Admin email: '.config('mail.admin_email'));

            // Test order placed notification
            $this->info('Sending order placed notification...');
            $adminNotificationService->sendOrderPlacedNotification($order);
            $this->info('✓ Order placed notification sent');

            // Test return request notification
            $this->info('Sending return request notification...');
            $adminNotificationService->sendOrderReturnRequestNotification($order);
            $this->info('✓ Return request notification sent');

            $this->info('All notifications sent successfully!');

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
