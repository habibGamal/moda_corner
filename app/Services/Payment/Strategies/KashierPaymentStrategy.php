<?php

namespace App\Services\Payment\Strategies;

use App\Enums\PaymentMethod;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;

/**
 * Strategy for Kashier payment processing
 * Follows Strategy Pattern and Single Responsibility Principle
 */
class KashierPaymentStrategy extends AbstractOnlinePaymentStrategy
{
    public function __construct(PaymentGatewayInterface $gateway)
    {
        parent::__construct($gateway);
    }

    /**
     * Check if this strategy can handle the given order
     */
    public function canHandle(Order $order): bool
    {
        return $order->payment_method === PaymentMethod::CREDIT_CARD
            || $order->payment_method === PaymentMethod::WALLET;
    }

    /**
     * Get the payment method identifier this strategy handles
     */
    public function getPaymentMethod(): string
    {
        return 'kashier';
    }

    /**
     * Get the payment method enum this strategy handles
     * Note: This strategy handles both CREDIT_CARD and WALLET
     */
    protected function getPaymentMethodEnum(): PaymentMethod
    {
        // Return null since this strategy handles multiple payment methods
        return PaymentMethod::CREDIT_CARD; // Default for compatibility
    }

    /**
     * Override validateOrder to handle multiple payment methods
     */
    protected function validateOrder(Order $order): void
    {
        if ($order->payment_status->isPaid()) {
            throw new \InvalidArgumentException('Order has already been paid');
        }

        // Check if this strategy can handle the order's payment method
        if (! $this->canHandle($order)) {
            throw new \InvalidArgumentException(
                "Order payment method {$order->payment_method->value} is not supported by this strategy"
            );
        }
    }
}
