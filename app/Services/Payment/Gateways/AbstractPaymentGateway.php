<?php

namespace App\Services\Payment\Gateways;

use App\DTOs\PaymentResultData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Interfaces\PaymentGatewayInterface;
use App\Interfaces\PaymentUrlProviderInterface;
use App\Interfaces\PaymentValidatorInterface;
use App\Models\Order;

/**
 * Abstract base class for payment gateways
 * Follows Template Method pattern and provides common functionality
 */
abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected PaymentValidatorInterface $validator;

    protected PaymentUrlProviderInterface $urlProvider;

    public function __construct(
        PaymentValidatorInterface $validator,
        PaymentUrlProviderInterface $urlProvider
    ) {
        $this->validator = $validator;
        $this->urlProvider = $urlProvider;
    }

    /**
     * Template method for payment initialization
     * Follows Template Method pattern
     */
    public function initializePayment(Order $order): PaymentResultData
    {
        $this->validateOrder($order);

        return $this->createPaymentData($order);
    }

    /**
     * Template method for processing successful payment
     */
    public function processSuccessfulPayment(Order $order, array $paymentData): Order
    {
        $this->validatePaymentData($paymentData);

        return $this->updateOrderAfterPayment($order, $paymentData);
    }

    /**
     * Default refund implementation - returns failure for gateways that don't support refunds
     * Can be overridden by concrete gateways that support refunds
     */
    public function processRefund(RefundRequestData $refundRequest): RefundResultData
    {
        if (! $this->supports('refunds')) {
            return RefundResultData::failure([
                'error' => 'Refunds not supported by this payment gateway',
                'gateway' => $this->getGatewayId(),
            ]);
        }

        return $this->executeRefund($refundRequest);
    }

    /**
     * Validate order before payment initialization
     */
    protected function validateOrder(Order $order): void
    {
        if ($order->payment_status->isPaid()) {
            throw new \InvalidArgumentException('Order has already been paid');
        }

        if ($order->total <= 0) {
            throw new \InvalidArgumentException('Order total must be greater than zero');
        }
    }

    /**
     * Validate payment data before processing
     */
    protected function validatePaymentData(array $paymentData): void
    {
        if (! $this->validator->validatePaymentResponse($paymentData)) {
            throw new \InvalidArgumentException('Invalid payment data provided');
        }
    }

    /**
     * Update order after successful payment
     */
    protected function updateOrderAfterPayment(Order $order, array $paymentData): Order
    {
        $order->payment_status = \App\Enums\PaymentStatus::PAID;
        $order->payment_details = json_encode($paymentData);

        // Handle different data structures (webhook vs direct response)
        $transactionId = $paymentData['transactionId']
            ?? $paymentData['paymentId']
            ?? $paymentData['kashierOrderId']
            ?? null;

        $order->payment_id = $transactionId;
        $order->save();

        return $order;
    }

    /**
     * Default feature support - can be overridden by concrete implementations
     */
    public function supports(string $feature): bool
    {
        return match ($feature) {
            'refunds' => false,
            'webhooks' => false,
            'recurring' => false,
            default => false,
        };
    }

    /**
     * Abstract method for creating payment data
     * Must be implemented by concrete gateways
     */
    abstract protected function createPaymentData(Order $order): PaymentResultData;

    /**
     * Abstract method for executing refunds
     * Only needs to be implemented by gateways that support refunds
     */
    protected function executeRefund(RefundRequestData $refundRequest): RefundResultData
    {
        throw new \BadMethodCallException('Refund functionality not implemented for this gateway');
    }
}
