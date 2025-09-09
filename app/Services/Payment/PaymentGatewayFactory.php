<?php

namespace App\Services\Payment;

use App\Enums\PaymentMethod;
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Interfaces\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payment\Gateways\KashierGateway;
use Illuminate\Support\Facades\Log;

class PaymentGatewayFactory implements PaymentGatewayFactoryInterface
{
    /**
     * Mapping of payment methods to gateway classes
     */
    private const GATEWAY_MAP = [
        'kashier' => KashierGateway::class,
    ];

    /**
     * Cache of instantiated gateways
     */
    private array $gatewayInstances = [];

    /**
     * Create a payment gateway instance for the given payment method
     *
     * @throws \InvalidArgumentException If the payment method is not supported
     */
    public function createGateway(string $gatewayName): PaymentGatewayInterface
    {

        // Return cached instance if available
        if (isset($this->gatewayInstances[$gatewayName])) {
            return $this->gatewayInstances[$gatewayName];
        }

        $gatewayClass = self::GATEWAY_MAP[$gatewayName];

        try {
            /** @var PaymentGatewayInterface $gateway */
            $gateway = app($gatewayClass);

            // Cache the instance
            $this->gatewayInstances[$gatewayName] = $gateway;

            Log::info('Payment gateway created', [
                'gateway_class' => $gatewayClass,
                'gateway_name' => $gateway->getGatewayName(),
            ]);

            return $gateway;
        } catch (\Exception $e) {
            Log::error('Failed to create payment gateway', [
                'gateway_class' => $gatewayClass,
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException(
                "Failed to create gateway for payment gateway '{$gatewayName}': {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get the appropriate payment gateway for an order
     *
     * @throws \InvalidArgumentException If the order's payment method is not supported
     */
    public function createGatewayForOrder(Order $order): PaymentGatewayInterface
    {
        if (! $order->payment_method) {
            throw new \InvalidArgumentException("Order {$order->id} does not have a payment method specified");
        }

        return $this->createGateway($order->payment_method);
    }

    /**
     * Get all available payment gateways
     *
     * @return array<string, PaymentGatewayInterface>
     */
    public function getAvailableGateways(): array
    {
        return self::GATEWAY_MAP;
    }
}
