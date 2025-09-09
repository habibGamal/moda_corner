<?php

namespace App\Interfaces;

use App\Models\Order;

/**
 * Strategy pattern interface for different payment processing strategies
 * Follows Strategy Pattern and Open/Closed Principle
 */
interface PaymentStrategyInterface
{
    /**
     * Check if this strategy can handle the given order
     */
    public function canHandle(Order $order): bool;

    /**
     * Get the payment method identifier this strategy handles
     */
    public function getPaymentMethod(): string;

    /**
     * Execute the payment processing strategy
     */
    public function execute(Order $order): \App\DTOs\PaymentResultData;

    /**
     * Process successful payment callback
     */
    public function processSuccess(Order $order, array $paymentData): Order;

    /**
     * Process failed payment callback
     */
    public function processFailure(Order $order, array $paymentData): Order;
}
