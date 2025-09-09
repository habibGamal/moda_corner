<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;

class RefundService
{
    protected PaymentGatewayFactoryInterface $gatewayFactory;

    /**
     * Create a new service instance.
     */
    public function __construct(PaymentGatewayFactoryInterface $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Process refund for an order
     *
     * @throws Exception
     */
    public function processRefund(Order $order): bool
    {
        try {
            switch ($order->payment_method) {
                case PaymentMethod::CARD:
                    return $this->processOnlinePaymentRefund($order);

                case PaymentMethod::CASH_ON_DELIVERY:
                    // COD orders don't need refund processing
                    Log::info('COD order - no refund needed', ['order_id' => $order->id]);

                    return true;

                default:
                    throw new Exception('Unsupported payment method for refund: '.$order->payment_method->value);
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
     * Process refund through payment gateway
     *
     * @throws Exception
     */
    protected function processOnlinePaymentRefund(Order $order): bool
    {
        try {
            // Get the appropriate payment gateway
            $gateway = $this->gatewayFactory->createGatewayForOrder($order);

            // For now, log the refund request - actual refund implementation depends on gateway capabilities
            Log::info('Processing refund through gateway', [
                'order_id' => $order->id,
                'gateway' => $gateway->getGatewayName(),
                'payment_method' => $order->payment_method->value,
                'amount' => $order->total,
            ]);

            // TODO: Implement actual refund logic based on gateway capabilities
            // This would involve calling gateway-specific refund methods

            return true;

        } catch (Exception $e) {
            Log::error('Gateway refund failed', [
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
        if (in_array($order->payment_method, [PaymentMethod::CARD]) && ! $order->payment_id) {
            return false;
        }

        return true;
    }
}
