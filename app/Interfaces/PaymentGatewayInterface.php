<?php

namespace App\Interfaces;

use App\DTOs\PaymentResultData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Models\Order;

/**
 * Core interface for payment gateway implementations
 * Follows Single Responsibility Principle - focused only on payment processing
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize payment for an order
     *
     * @param  Order  $order  The order to process payment for
     * @return PaymentResultData The payment initialization data
     */
    public function initializePayment(Order $order): PaymentResultData;

    /**
     * Process a successful payment for an order
     *
     * @param  Order  $order  The order to update
     * @param  array  $paymentData  The payment data from payment gateway
     * @return Order The updated order
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): Order;

    /**
     * Process a refund for an order
     *
     * @param  RefundRequestData  $refundRequest  The refund request data
     * @return RefundResultData The refund result
     */
    public function processRefund(RefundRequestData $refundRequest): RefundResultData;

    /**
     * Get the gateway identifier
     */
    public function getGatewayId(): string;

    /**
     * Check if the gateway supports a specific feature
     */
    public function supports(string $feature): bool;
}
