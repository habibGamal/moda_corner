<?php

namespace App\Services\Payment\Strategies;

use App\DTOs\PaymentResultData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Interfaces\PaymentGatewayInterface;
use App\Interfaces\PaymentStrategyInterface;
use App\Models\Order;

/**
 * Abstract base strategy for online payment methods
 * Implements common functionality for gateway-based payments
 */
abstract class AbstractOnlinePaymentStrategy implements PaymentStrategyInterface
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Template method for executing payment
     */
    public function execute(Order $order): PaymentResultData
    {
        $this->validateOrder($order);

        return $this->gateway->initializePayment($order);
    }

    /**
     * Template method for processing successful payment
     */
    public function processSuccess(Order $order, array $paymentData): Order
    {
        return $this->gateway->processSuccessfulPayment($order, $paymentData);
    }

    /**
     * Template method for processing failed payment
     */
    public function processFailure(Order $order, array $paymentData): Order
    {
        $order->payment_status = PaymentStatus::FAILED;
        $order->payment_details = json_encode($paymentData);
        $order->save();

        return $order;
    }

    /**
     * Process refund through the gateway
     */
    public function processRefund(RefundRequestData $refundRequest): RefundResultData
    {
        return $this->gateway->processRefund($refundRequest);
    }

    /**
     * Validate order before processing
     */
    protected function validateOrder(Order $order): void
    {
        if ($order->payment_status->isPaid()) {
            throw new \InvalidArgumentException('Order has already been paid');
        }

        if ($order->payment_method !== $this->getPaymentMethodEnum()) {
            throw new \InvalidArgumentException(
                "Order payment method {$order->payment_method->value} does not match strategy {$this->getPaymentMethod()}"
            );
        }
    }

    /**
     * Get the payment method enum this strategy handles
     */
    abstract protected function getPaymentMethodEnum(): PaymentMethod;
}
