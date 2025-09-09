<?php

use App\Enums\PaymentMethod;
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Models\Order;
use App\Models\User;
use App\Services\Payment\Gateways\KashierGateway;
use App\Services\Payment\Gateways\PaymobGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->factory = app(PaymentGatewayFactoryInterface::class);

    // Set up test configuration for both gateways
    config([
        'services.kashier' => [
            'merchant_id' => 'TEST-MID-12345',
            'api_key' => 'test-api-key-12345',
            'secret_key' => 'test-secret-key-12345',
            'mode' => 'test',
        ],
        'services.paymob' => [
            'api_key' => 'test-paymob-api-key',
            'secret_key' => 'test-paymob-secret-key',
            'public_key' => 'test-paymob-public-key',
            'integration_id' => 'test-integration-id',
            'mode' => 'test',
        ],
    ]);
});

describe('PaymentGatewayFactory', function () {
    it('creates Kashier gateway for Kashier payment method', function () {
        $gateway = $this->factory->createGateway(PaymentMethod::CARD);

        expect($gateway)->toBeInstanceOf(KashierGateway::class);
        expect($gateway->getGatewayName())->toBe('kashier');
    });

    it('creates Paymob gateway for credit card payment method', function () {
        $gateway = $this->factory->createGateway(PaymentMethod::CARD);

        expect($gateway)->toBeInstanceOf(PaymobGateway::class);
        expect($gateway->getGatewayName())->toBe('paymob');
    });

    it('creates gateway for order with Kashier payment method', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);CARD

        $gateway = $this->factory->createGatewayForOrder($order);

        expect($gateway)->toBeInstanceOf(KashierGateway::class);
    });

    it('creates gateway for order with credit card payment method', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);CARD

        $gateway = $this->factory->createGatewayForOrder($order);

        expect($gateway)->toBeInstanceOf(PaymobGateway::class);
    });

    it('throws exception for unsupported payment method', function () {
        expect(fn () => $this->factory->createGateway('unsupported_method'))
            ->toThrow(InvalidArgumentException::class, 'Payment method \'unsupported_method\' is not supported');
    });

    it('checks if payment method is supported', function () {
        expect($this->factory->isPaymentMethodSupported(PaymentMethod::CREDIT_CARD))->toBeTrue();
        expect($this->factory->isPaymentMethodSupported(PaymentMethod::CARD))->toBeTrue();
        expect($this->factory->isPaymentMethodSupported('unsupported'))CARD();
    });

    it('returns supported payment methods', function () {
        $supportedMethods = $this->factory->getSupportedPaymentMethods();

        expect($supportedMethods)->toContain(PaymentMethod::CREDIT_CARD);
        expect($supportedMethods)->toContain(PaymentMethod::CARD);
    });CARD

    it('returns available gateways', function () {
        $gateways = $this->factory->getAvailableGateways();

        expect($gateways)->toHaveKey('kashier');
        expect($gateways)->toHaveKey('credit_card');
        expect($gateways)->toHaveKey('paymob');
        expect($gateways['kashier'])->toBeInstanceOf(KashierGateway::class);
        expect($gateways['credit_card'])->toBeInstanceOf(PaymobGateway::class);
    });

    it('caches gateway instances', function () {
        $gateway1 = $this->factory->createGateway(PaymentMethod::CREDIT_CARD);
        $gateway2 = $this->factory->createGateway(PaymentMethod::CARD);
CARD
        expect($gateway1)->toBe($gateway2); // Same instance
    });
});

describe('KashierGateway', function () {
    beforeEach(function () {
        $this->gateway = $this->factory->createGateway(PaymentMethod::CREDIT_CARD);
    });CARD

    it('supports correct payment methods', function () {
        expect($this->gateway->supportsPaymentMethod('kashier'))->toBeTrue();
        expect($this->gateway->supportsPaymentMethod('card'))->toBeTrue();
        expect($this->gateway->supportsPaymentMethod('credit_card'))->toBeTrue();
        expect($this->gateway->supportsPaymentMethod('paymob'))->toBeFalse();
    });

    it('initializes payment for order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 150.75,
        ]);

        $paymentRequest = $this->gateway->initializePayment($order);

        expect($paymentRequest->gatewayName)->toBe('kashier');
        expect($paymentRequest->orderId)->toBe((string) $order->id);
        expect($paymentRequest->amount)->toBe('150.75');
        expect($paymentRequest->currency)->toBe('EGP');
        expect($paymentRequest->gatewaySpecificData)->toHaveKey('merchantId');
        expect($paymentRequest->gatewaySpecificData)->toHaveKey('hash');
    });
});
