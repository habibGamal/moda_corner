<?php

namespace App\Services\Payment\Strategies;

use App\DTOs\PaymentResultData;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Interfaces\PaymentStrategyInterface;
use App\Models\Order;

/**
 * Strategy for Cash on Delivery payment processing
 * Follows Strategy Pattern and Single Responsibility Principle
 */
class CashOnDeliveryStrategy implements PaymentStrategyInterface
{
    /**
     * Check if this strategy can handle the given order
     */
    public function canHandle(Order $order): bool
    {
        return $order->payment_method === PaymentMethod::CASH_ON_DELIVERY;
    }

    /**
     * Get the payment method identifier this strategy handles
     */
    public function getPaymentMethod(): string
    {
        return 'cash_on_delivery';
    }

    /**
     * Execute the payment processing strategy
     * For COD, no immediate payment processing is needed
     */
    public function execute(Order $order): PaymentResultData
    {
        // For Cash on Delivery, we don't need to redirect to a payment gateway
        // The payment status remains PENDING until delivery
        return new PaymentResultData(
            merchantId: '',
            orderId: (string) $order->id,
            amount: number_format((float) $order->total, 2, '.', ''),
            currency: 'EGP',
            hash: '',
            mode: 'cod',
            redirectUrl: route('orders.show', $order->id),
            failureUrl: route('orders.show', $order->id),
            webhookUrl: '',
            additionalParams: [
                'payment_method' => 'cash_on_delivery',
                'message' => 'Payment will be collected upon delivery',
            ]
        );
    }

    /**
     * Process successful payment callback
     * For COD, this happens when the order is delivered
     */
    public function processSuccess(Order $order, array $paymentData): Order
    {
        $order->payment_status = PaymentStatus::PAID;
        $order->payment_details = json_encode($paymentData);
        $order->save();

        return $order;
    }

    /**
     * Process failed payment callback
     * For COD, this could happen if payment is refused on delivery
     */
    public function processFailure(Order $order, array $paymentData): Order
    {
        $order->payment_status = PaymentStatus::FAILED;
        $order->payment_details = json_encode($paymentData);
        $order->save();

        return $order;
    }
}
