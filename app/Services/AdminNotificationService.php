<?php

namespace App\Services;

use App\Models\Order;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderReturnRequestNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\AnonymousNotifiable;
use Exception;

class AdminNotificationService
{
    /**
     * Send order placed notification to admin
     *
     * @param Order $order
     * @return void
     */
    public function sendOrderPlacedNotification(Order $order): void
    {
        try {
            $adminEmail = config('mail.admin_email', env('MAIL_TO_ADMIN'));

            if (!$adminEmail) {
                Log::warning('Admin email not configured. Skipping order placed notification.', [
                    'order_id' => $order->id
                ]);
                return;
            }

            $anonymousNotifiable = new AnonymousNotifiable();
            $anonymousNotifiable->route('mail', $adminEmail);
            $anonymousNotifiable->notify(new OrderPlacedNotification($order));

            Log::info('Order placed notification sent to admin', [
                'order_id' => $order->id,
                'admin_email' => $adminEmail,
                'customer_id' => $order->user_id,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send order placed notification to admin', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception as the order placement itself was successful
        }
    }

    /**
     * Send order return request notification to admin
     *
     * @param Order $order
     * @return void
     */
    public function sendOrderReturnRequestNotification(Order $order): void
    {
        try {
            $adminEmail = config('mail.admin_email', env('MAIL_TO_ADMIN'));

            if (!$adminEmail) {
                Log::warning('Admin email not configured. Skipping return request notification.', [
                    'order_id' => $order->id
                ]);
                return;
            }

            $anonymousNotifiable = new AnonymousNotifiable();
            $anonymousNotifiable->route('mail', $adminEmail);
            $anonymousNotifiable->notify(new OrderReturnRequestNotification($order));

            Log::info('Order return request notification sent to admin', [
                'order_id' => $order->id,
                'admin_email' => $adminEmail,
                'customer_id' => $order->user_id,
                'return_reason' => $order->return_reason,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send return request notification to admin', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Don't throw exception as the return request itself was successful
        }
    }
}
