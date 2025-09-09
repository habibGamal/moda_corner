<?php

namespace App\Services\Payment\Webhooks;

use App\Events\Payment\PaymentFailed;
use App\Events\Payment\PaymentSucceeded;
use App\Interfaces\PaymentValidatorInterface;
use App\Models\Order;
use App\Services\Payment\PaymentProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling payment webhooks
 * Follows Single Responsibility Principle
 */
class PaymentWebhookHandler
{
    public function __construct(
        private PaymentProcessor $paymentProcessor,
        private PaymentValidatorInterface $validator
    ) {}

    /**
     * Handle incoming webhook request
     */
    public function handle(Request $request): array
    {
        try {
            $rawPayload = $request->getContent();
            $headers = $request->headers->all();

            Log::info('Payment webhook received', [
                'payload' => $request->all(),
                'raw_payload_length' => strlen($rawPayload),
                'headers' => $headers,
            ]);

            if (! $this->validator->validateWebhookPayload($rawPayload, $headers)) {
                Log::warning('Invalid webhook signature', [
                    'params' => $request->all(),
                    'raw_payload' => $rawPayload,
                    'headers' => $headers,
                ]);

                return ['status' => 'error', 'message' => 'Invalid signature'];
            }

            return $this->processWebhookData($request->all());

        } catch (\Exception $e) {
            Log::error('Error processing webhook: '.$e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all(),
            ]);

            return ['status' => 'error', 'message' => 'Internal server error'];
        }
    }

    /**
     * Process validated webhook data
     */
    private function processWebhookData(array $webhookData): array
    {
        $merchantOrderId = $webhookData['data']['merchantOrderId'] ?? null;
        $status = $webhookData['data']['status'] ?? null;
        $transactionId = $webhookData['data']['transactionId'] ?? null;
        $amount = $webhookData['data']['amount'] ?? null;

        if (! $merchantOrderId) {
            Log::error('Missing merchantOrderId in webhook data', ['webhook_data' => $webhookData]);

            return ['status' => 'error', 'message' => 'Missing order reference'];
        }

        $orderId = str_replace(config('app.name').'-', '', $merchantOrderId);

        if (! $orderId) {
            Log::error('Could not extract order ID from merchantOrderId', [
                'merchantOrderId' => $merchantOrderId,
                'webhook_data' => $webhookData,
            ]);

            return ['status' => 'error', 'message' => 'Invalid order reference'];
        }

        $order = Order::find($orderId);

        if (! $order) {
            Log::error('Order not found', ['order_id' => $orderId, 'merchantOrderId' => $merchantOrderId]);

            return ['status' => 'error', 'message' => 'Order not found'];
        }

        if ($status === 'SUCCESS') {
            $this->handleSuccessfulPayment($order, $webhookData['data'], $transactionId, $amount);
        } else {
            $this->handleFailedPayment($order, $webhookData['data'], $status);
        }

        return ['status' => 'success'];
    }

    /**
     * Handle successful payment webhook
     */
    private function handleSuccessfulPayment(Order $order, array $paymentData, ?string $transactionId, ?string $amount): void
    {
        $updatedOrder = $this->paymentProcessor->processPaymentSuccess($order, $paymentData);

        PaymentSucceeded::dispatch($updatedOrder, $paymentData);

        Log::info('Payment confirmed via webhook', [
            'order_id' => $order->id,
            'transactionId' => $transactionId,
            'amount' => $amount,
        ]);
    }

    /**
     * Handle failed payment webhook
     */
    private function handleFailedPayment(Order $order, array $paymentData, ?string $status): void
    {
        $updatedOrder = $this->paymentProcessor->processPaymentFailure($order, $paymentData);

        PaymentFailed::dispatch($updatedOrder, $paymentData, "Webhook reported status: {$status}");

        Log::warning('Payment failed via webhook', [
            'order_id' => $order->id,
            'status' => $status,
            'webhook_data' => $paymentData,
        ]);
    }
}
