<?php

namespace App\Services\Payment\Strategies;

use App\DTOs\PaymentResultData;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Interfaces\PaymentStrategyInterface;
use App\Models\Order;

/**
 * Strategy for InstaPay manual payment verification
 * Follows Strategy Pattern and Single Responsibility Principle
 * User uploads payment proof after order creation for admin verification
 */
class InstaPayStrategy implements PaymentStrategyInterface
{
    /**
     * Check if this strategy can handle the given order
     */
    public function canHandle(Order $order): bool
    {
        return $order->payment_method === PaymentMethod::INSTAPAY;
    }

    /**
     * Get the payment method identifier this strategy handles
     */
    public function getPaymentMethod(): string
    {
        return 'instapay';
    }

    /**
     * Execute the payment processing strategy
     * For InstaPay, redirect to upload form page
     */
    public function execute(Order $order): PaymentResultData
    {
        // For InstaPay, we redirect to upload form page
        // Payment status remains PENDING until admin verification
        return new PaymentResultData(
            merchantId: '',
            orderId: (string) $order->id,
            amount: number_format((float) $order->total, 2, '.', ''),
            currency: 'EGP',
            hash: '',
            mode: 'instapay',
            redirectUrl: route('instapay.upload', $order->id),
            failureUrl: route('orders.show', $order->id),
            webhookUrl: '',
            additionalParams: [
                'payment_method' => 'instapay',
                'message' => 'Please upload your payment proof',
                'requires_upload' => true,
            ]
        );
    }

    /**
     * Process successful payment callback
     * For InstaPay, this happens when admin confirms the payment
     */
    public function processSuccess(Order $order, array $paymentData): Order
    {
        $order->payment_status = PaymentStatus::PAID;

        // Merge existing payment_details with new data
        $existingDetails = json_decode($order->payment_details, true) ?? [];
        $order->payment_details = json_encode(array_merge($existingDetails, $paymentData, [
            'verified_at' => now()->toISOString(),
        ]));

        $order->save();

        return $order;
    }

    /**
     * Process failed payment callback
     * For InstaPay, this happens when admin rejects the payment
     */
    public function processFailure(Order $order, array $paymentData): Order
    {
        $order->payment_status = PaymentStatus::PENDING;

        // Merge existing payment_details with rejection data
        $existingDetails = json_decode($order->payment_details, true) ?? [];
        $order->payment_details = json_encode(array_merge($existingDetails, $paymentData, [
            'rejected_at' => now()->toISOString(),
            'can_reupload' => true,
        ]));

        $order->save();

        return $order;
    }
}
