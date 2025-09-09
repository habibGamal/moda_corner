<?php

namespace App\Interfaces;

use App\Enums\PaymentMethod;
use App\Models\Order;

interface PaymentGatewayFactoryInterface
{
    /**
     * Create a payment gateway instance for the given payment method
     *
     * @throws \InvalidArgumentException If the payment method is not supported
     */
    public function createGateway(string $paymentMethod): PaymentGatewayInterface;

    /**
     * Get the appropriate payment gateway for an order
     *
     * @throws \InvalidArgumentException If the order's payment method is not supported
     */
    public function createGatewayForOrder(Order $order): PaymentGatewayInterface;

}
