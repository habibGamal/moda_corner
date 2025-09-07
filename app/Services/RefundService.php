<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\PaymentMethod;
use App\Services\KashierPaymentService;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use Illuminate\Support\Facades\Log;
use Exception;

class RefundService
{
    protected KashierPaymentService $kashierPaymentService;

    /**
     * Create a new service instance.
     *
     * @param KashierPaymentService $kashierPaymentService
     */
    public function __construct(KashierPaymentService $kashierPaymentService)
    {
        $this->kashierPaymentService = $kashierPaymentService;
    }

    /**
     * Process refund for an order
     *
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    public function processRefund(Order $order): bool
    {
        try {
            switch ($order->payment_method) {
                case PaymentMethod::KASHIER:
                    return $this->processKashierRefund($order);

                case PaymentMethod::CASH_ON_DELIVERY:
                    // COD orders don't need refund processing
                    Log::info('COD order - no refund needed', ['order_id' => $order->id]);
                    return true;

                default:
                    throw new Exception('Unsupported payment method for refund: ' . $order->payment_method->value);
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
     * Process refund through Kashier
     *
     * @param Order $order
     * @return bool
     * @throws Exception
     */
    protected function processKashierRefund(Order $order): bool
    {
        try {
            // Create refund request DTO
            $refundRequest = new RefundRequestData(
                orderId: $order->id,
                amount: $order->total,
                reason: 'Order return refund'
            );

            // Call Kashier refund API with DTO
            $refundResult = $this->kashierPaymentService->processRefund($refundRequest);

            if ($refundResult->success) {
                Log::info('Kashier refund processed successfully', [
                    'order_id' => $order->id,
                    'kashier_order_id' => $order->id,
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
                throw new Exception('Kashier refund failed: ' . ($refundResult->messageEn ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('Kashier refund failed', [
                'order_id' => $order->id,
                'payment_id' => $order->payment_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if refund is possible for an order
     *
     * @param Order $order
     * @return bool
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
        if ($order->payment_method === PaymentMethod::KASHIER && !$order->payment_id) {
            return false;
        }

        return true;
    }
}
