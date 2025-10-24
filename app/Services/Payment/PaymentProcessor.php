<?php

namespace App\Services\Payment;

use App\DTOs\PaymentResultData;
use App\DTOs\RefundRequestData;
use App\DTOs\RefundResultData;
use App\Enums\PaymentMethod;
use App\Interfaces\PaymentStrategyInterface;
use App\Models\Order;
use Illuminate\Support\Collection;

/**
 * Payment processor that orchestrates payment processing using strategy pattern
 * Follows Single Responsibility Principle and Strategy Pattern
 */
class PaymentProcessor
{
    private Collection $strategies;

    private Collection $gatewayMappings;

    public function __construct()
    {
        $this->strategies = collect();
        $this->gatewayMappings = collect();
        $this->initializeGatewayMappings();
    }

    /**
     * Initialize payment method to gateway mappings from config
     */
    private function initializeGatewayMappings(): void
    {
        $defaultGateway = config('services.payment.default_gateway', 'kashier');

        // Map payment methods to gateways
        $this->gatewayMappings = collect([
            PaymentMethod::CASH_ON_DELIVERY->value => 'cod', // Special case for COD
            PaymentMethod::CREDIT_CARD->value => $defaultGateway,
            PaymentMethod::WALLET->value => $defaultGateway,
            PaymentMethod::INSTAPAY->value => 'instapay', // Manual payment verification
        ]);
    }

    /**
     * Register a payment strategy for a specific gateway
     */
    public function addStrategy(PaymentStrategyInterface $strategy, ?string $gatewayName = null): void
    {
        // If no gateway name provided, use the strategy's payment method as the key
        $key = $gatewayName ?? $strategy->getPaymentMethod();
        $this->strategies->put($key, $strategy);
    }

    /**
     * Get the gateway name for a payment method
     */
    public function getGatewayForPaymentMethod(PaymentMethod $paymentMethod): string
    {
        return $this->gatewayMappings->get($paymentMethod->value, 'kashier');
    }

    /**
     * Get all registered strategies
     */
    public function getStrategies(): Collection
    {
        return $this->strategies;
    }

    /**
     * Process payment for an order using the appropriate strategy
     */
    public function processPayment(Order $order): PaymentResultData
    {
        $strategy = $this->findStrategyForOrder($order);

        if (! $strategy) {
            throw new \InvalidArgumentException(
                "No payment strategy found for payment method: {$order->payment_method->value}"
            );
        }

        return $strategy->execute($order);
    }

    /**
     * Process successful payment callback
     */
    public function processPaymentSuccess(Order $order, array $paymentData): Order
    {
        $strategy = $this->findStrategyForOrder($order);

        if (! $strategy) {
            throw new \InvalidArgumentException(
                "No payment strategy found for payment method: {$order->payment_method->value}"
            );
        }

        return $strategy->processSuccess($order, $paymentData);
    }

    /**
     * Process failed payment callback
     */
    public function processPaymentFailure(Order $order, array $paymentData): Order
    {
        $strategy = $this->findStrategyForOrder($order);

        if (! $strategy) {
            throw new \InvalidArgumentException(
                "No payment strategy found for payment method: {$order->payment_method->value}"
            );
        }

        return $strategy->processFailure($order, $paymentData);
    }

    /**
     * Process refund for an order
     */
    public function processRefund(RefundRequestData $refundRequest): RefundResultData
    {
        // For refunds, we need to identify the gateway by the order's payment method
        $order = Order::find($refundRequest->orderId);

        if (! $order) {
            return RefundResultData::failure([
                'error' => 'Order not found',
                'order_id' => $refundRequest->orderId,
            ]);
        }

        $strategy = $this->findStrategyForOrder($order);
        if (! $strategy) {
            return RefundResultData::failure([
                'error' => "No payment strategy found for payment method: {$order->payment_method->value}",
                'order_id' => $refundRequest->orderId,
            ]);
        }

        // Check if the strategy supports refunds
        if (! method_exists($strategy, 'processRefund')) {
            return RefundResultData::failure([
                'error' => 'Refunds not supported for this payment method',
                'payment_method' => $order->payment_method->value,
                'order_id' => $refundRequest->orderId,
            ]);
        }

        return $strategy->processRefund($refundRequest);
    }

    /**
     * Find the appropriate strategy for an order
     */
    private function findStrategyForOrder(Order $order): ?PaymentStrategyInterface
    {
        // Special case for COD
        if ($order->payment_method === PaymentMethod::CASH_ON_DELIVERY) {
            return $this->strategies->get('cod');
        }

        // Special case for InstaPay
        if ($order->payment_method === PaymentMethod::INSTAPAY) {
            return $this->strategies->get('instapay');
        }

        // For online payment methods, get the gateway
        $gatewayName = $this->getGatewayForPaymentMethod($order->payment_method);
        $strategy = $this->strategies->get($gatewayName);

        // Fallback to checking if strategy can handle the order
        if (! $strategy) {
            $strategy = $this->strategies->first(function (PaymentStrategyInterface $strategy) use ($order) {
                return $strategy->canHandle($order);
            });
        }

        return $strategy;
    }

    /**
     * Check if a payment method is supported
     */
    public function supportsPaymentMethod(PaymentMethod $paymentMethod): bool
    {
        // COD is always supported if COD strategy exists
        if ($paymentMethod === PaymentMethod::CASH_ON_DELIVERY) {
            return $this->strategies->has('cod');
        }

        // InstaPay is supported if InstaPay strategy exists
        if ($paymentMethod === PaymentMethod::INSTAPAY) {
            return $this->strategies->has('instapay');
        }

        // For online methods, check if the configured gateway exists
        $gatewayName = $this->getGatewayForPaymentMethod($paymentMethod);

        return $this->strategies->has($gatewayName);
    }

    /**
     * Get supported payment methods
     */
    public function getSupportedPaymentMethods(): array
    {
        return $this->strategies->keys()->toArray();
    }
}
