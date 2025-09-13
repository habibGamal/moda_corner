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
        return $this->processPartialRefund($order, (float) $order->total);
    }

    /**
     * Process partial refund for an order with specific amount
     *
     * @throws Exception
     */
    public function processPartialRefund(Order $order, float $refundAmount): bool
    {
        try {
            // Validate refund amount
            if ($refundAmount <= 0) {
                throw new Exception('Refund amount must be greater than zero');
            }

            if ($refundAmount > (float) $order->total) {
                throw new Exception('Refund amount cannot exceed order total');
            }

            // Check if the payment method requires online gateway processing
            if ($order->payment_method->requiresOnlineGateway()) {
                return $this->processOnlinePaymentRefund($order, $refundAmount);
            } else {
                // COD orders don't need refund processing
                Log::info('COD order - no refund needed', [
                    'order_id' => $order->id,
                    'refund_amount' => $refundAmount,
                ]);

                return true;
            }
        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method->value,
                'refund_amount' => $refundAmount,
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
    protected function processOnlinePaymentRefund(Order $order, ?float $refundAmount = null): bool
    {
        try {
            // Use order total if no specific amount provided
            $amount = $refundAmount ?? (float) $order->total;

            // Create refund request DTO
            $refundRequest = new RefundRequestData(
                orderId: $order->id,
                amount: $amount,
                reason: $refundAmount === (float) $order->total
                    ? 'Order return refund'
                    : 'Partial order return refund'
            );

            // Call payment processor refund using new system
            $refundResult = $this->paymentProcessor->processRefund($refundRequest);

            if ($refundResult->success) {
                Log::info('Payment refund processed successfully', [
                    'order_id' => $order->id,
                    'payment_method' => $order->payment_method->value,
                    'payment_id' => $order->payment_id,
                    'refund_amount' => $amount,
                    'is_partial' => $amount < (float) $order->total,
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
                'refund_amount' => $refundAmount ?? (float) $order->total,
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

    /**
     * Get the maximum refundable amount for an order
     */
    public function getMaxRefundableAmount(Order $order): float
    {
        if (! $this->canProcessRefund($order)) {
            return 0.0;
        }

        // For now, return the full order amount
        // In a more complex scenario, you might want to track previous refunds
        return (float) $order->total;
    }

    /**
     * Calculate refund amount for specific return items
     */
    public function calculateRefundAmountForItems(array $returnItems): float
    {
        return array_reduce($returnItems, function ($total, $item) {
            return $total + ((float) $item['unit_price'] * $item['quantity']);
        }, 0.0);
    }
}
