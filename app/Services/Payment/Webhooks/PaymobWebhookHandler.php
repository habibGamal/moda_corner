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
 * Service for handling Paymob payment webhooks
 * Follows Single Responsibility Principle
 * Separate from Kashier webhook handler for clarity and maintainability
 */
class PaymobWebhookHandler
{
    public function __construct(
        private PaymentProcessor $paymentProcessor,
        private PaymentValidatorInterface $validator
    ) {}

    /**
     * Handle incoming Paymob webhook request
     */
    public function handle(Request $request): array
    {
        try {
            $rawPayload = $request->getContent();
            $headers = $request->headers->all();
            $queryParams = $request->query->all();

            Log::info('Paymob webhook received', [
                'payload' => $request->all(),
                'raw_payload_length' => strlen($rawPayload),
                'headers' => array_keys($headers),
                'query_params' => array_keys($queryParams),
            ]);

            if (!$this->validator->validateWebhookPayload($rawPayload, $headers, $queryParams)) {
                Log::warning('Invalid Paymob webhook signature', [
                    'params' => $request->all(),
                ]);

                return ['status' => 'error', 'message' => 'Invalid signature'];
            }

            return $this->processWebhookData($request->all());

        } catch (\Exception $e) {
            Log::error('Error processing Paymob webhook: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $request->all(),
            ]);

            return ['status' => 'error', 'message' => 'Internal server error'];
        }
    }

    /**
     * Process validated webhook data from Paymob
     */
    private function processWebhookData(array $webhookData): array
    {
        // Paymob sends data in 'obj' key
        if (!isset($webhookData['obj'])) {
            Log::error('Missing obj in Paymob webhook data', ['webhook_data' => $webhookData]);
            return ['status' => 'error', 'message' => 'Invalid webhook structure'];
        }

        $obj = $webhookData['obj'];

        // Extract order information
        $merchantOrderId = $obj['order']['merchant_order_id'] ?? null;

        // Fallback: check extras.ee field (where we store merchant order ID)
        if (!$merchantOrderId && isset($obj['order']['shipping_data']['extra_description'])) {
            $merchantOrderId = $obj['order']['shipping_data']['extra_description'];
        }

        $success = $obj['success'] ?? false;
        $transactionId = $obj['id'] ?? null;
        $amountCents = $obj['amount_cents'] ?? null;

        if (!$merchantOrderId) {
            Log::error('Missing merchant_order_id in Paymob webhook', ['obj' => $obj]);
            return ['status' => 'error', 'message' => 'Missing order reference'];
        }

        $orderId = extractOrderIdFromMerchantOrderNumber($merchantOrderId);

        if (!$orderId) {
            Log::error('Could not extract order ID from merchantOrderId', [
                'merchantOrderId' => $merchantOrderId,
                'webhook_data' => $webhookData,
            ]);

            return ['status' => 'error', 'message' => 'Invalid order reference'];
        }

        $order = Order::find($orderId);

        if (!$order) {
            Log::error('Order not found', ['order_id' => $orderId, 'merchantOrderId' => $merchantOrderId]);
            return ['status' => 'error', 'message' => 'Order not found'];
        }

        if ($success === true || $success === 'true') {
            $this->handleSuccessfulPayment($order, $obj, $transactionId, $amountCents);
        } else {
            $this->handleFailedPayment($order, $obj);
        }

        return ['status' => 'success'];
    }

    /**
     * Handle successful payment webhook from Paymob
     */
    private function handleSuccessfulPayment(Order $order, array $paymentData, ?string $transactionId, ?int $amountCents): void
    {
        $updatedOrder = $this->paymentProcessor->processPaymentSuccess($order, $paymentData);

        PaymentSucceeded::dispatch($updatedOrder, $paymentData);

        Log::info('Paymob payment confirmed via webhook', [
            'order_id' => $order->id,
            'transactionId' => $transactionId,
            'amount_cents' => $amountCents,
        ]);
    }

    /**
     * Handle failed payment webhook from Paymob
     */
    private function handleFailedPayment(Order $order, array $paymentData): void
    {
        $updatedOrder = $this->paymentProcessor->processPaymentFailure($order, $paymentData);

        $errorMessage = $paymentData['data']['message'] ?? 'Payment failed';
        PaymentFailed::dispatch($updatedOrder, $paymentData, "Paymob webhook: {$errorMessage}");

        Log::warning('Paymob payment failed via webhook', [
            'order_id' => $order->id,
            'error_message' => $errorMessage,
            'webhook_data' => $paymentData,
        ]);
    }
}
