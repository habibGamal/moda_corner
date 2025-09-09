<?php

namespace App\Services;

use App\DTOs\RefundRequestData;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Services\Payment\PaymentProcessor;
use Exception;
use Illuminate\Support\Facades\Log;

class RefundService
{
    protected PaymentProcessor $paymentProcessor;

    /**
     * Create a new service instance.
     */
    public function __construct(PaymentProcessor $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }

    /**
     * Process refund for an order
     *
     * @throws Exception
     */
    public function processRefund(Order $order): bool
    {
        try {
            // Check if the payment method requires online gateway processing
            if ($order->payment_method->requiresOnlineGateway()) {
                return $this->processOnlinePaymentRefund($order);
            } else {
                // COD orders don't need refund processing
                Log::info('COD order - no refund needed', ['order_id' => $order->id]);
                return true;
            }
        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method->value,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process refund through online payment gateway
     *
     * @throws Exception
     */
    protected function processOnlinePaymentRefund(Order $order): bool
    {
        try {
            // Create refund request DTO
            $refundRequest = new RefundRequestData(
                orderId: $order->id,
                amount: (float) $order->total,
                reason: 'Order return refund'
            );

            // Call payment processor refund using new system
            $refundResult = $this->paymentProcessor->processRefund($refundRequest);

            if ($refundResult->success) {
                Log::info('Payment refund processed successfully', [
                    'order_id' => $order->id,
                    'payment_method' => $order->payment_method->value,
                    'payment_id' => $order->payment_id,
                    'transaction_id' => $refundResult->transactionId,
                    'card_order_id' => $refundResult->cardOrderId,
                    'order_reference' => $refundResult->orderReference,
                    'gateway_code' => $refundResult->gatewayCode,
                    'total_refunded_amount' => $refundResult->totalRefundedAmount,
                    'order_status' => $refundResult->orderStatus,
                ]);

                return true;
            } else {
                throw new Exception('Payment refund failed: '.($refundResult->messageEn ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Payment refund failed', [
                'order_id' => $order->id,
                'payment_id' => $order->payment_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if refund is possible for an order
     */
    public function canProcessRefund(Order $order): bool
    {
        // COD orders don't need refunds
        if ($order->payment_method === PaymentMethod::CASH_ON_DELIVERY) {
            return false;
        }

        // Check if payment was successful
        if ($order->payment_status !== \App\Enums\PaymentStatus::PAID) {
            return false;
        }

        // Check if payment ID exists for online payments
        if ($order->payment_method !== PaymentMethod::CASH_ON_DELIVERY && ! $order->payment_id) {
            return false;
        }

        return true;
    }
}
