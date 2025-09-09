<?php

use App\DTOs\Payment\PaymentResponseData;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Models\Order;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
    ]);

    $this->shippingAddress = ShippingAddress::factory()->create([
        'user_id' => $this->user->id,
        'phone' => '01234567890',
        'city' => 'Cairo',
        'address_line_1' => '123 Test Street',
        'address_line_2' => 'Apt 4',
    ]);

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

describe('KashierGateway Direct Payment', function () {
    beforeEach(function () {
        $this->gateway = $this->factory->createGateway(PaymentMethod::CREDIT_CARD);
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,CARD
            'shipping_address_id' => $this->shippingAddress->id,
            'total' => 150.75,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::PENDING,
        ]);CARD

        $this->validCardData = [
            'cardNumber' => '5123450000000008',
            'expiryMonth' => '12',
            'expiryYear' => '25',
            'securityCode' => '123',
            'nameOnCard' => 'John Doe',
            'saveCard' => false,
        ];
    });

    it('processes successful payment without redirection', function () {
        // Mock successful Kashier API response
        Http::fake([
            'test-fep.kashier.io/v3/orders/' => Http::response([
                'response' => [
                    'result' => 'SUCCESS',
                    'status' => 'CAPTURED',
                    'transactionId' => 'TX-12345',
                    'amount' => 15075, // Amount in cents
                    'currency' => 'EGP',
                ],
                'transactionId' => 'TX-12345',
                'orderId' => $this->order->id,
            ], 200),
        ]);

        $result = $this->gateway->processPaymentWithoutRedirection($this->order, $this->validCardData);

        expect($result)->toBeInstanceOf(PaymentResponseData::class);
        expect($result->success)->toBeTrue();
        expect($result->transactionId)->toBe('TX-12345');
        expect($result->order->id)->toBe($this->order->id);

        // Verify order was updated
        $this->order->refresh();
        expect($this->order->payment_status)->toBe(PaymentStatus::PAID);
        expect($this->order->payment_id)->toBe('TX-12345');
        expect($this->order->payment_details)->not->toBeNull();
    });

    it('handles failed payment without redirection', function () {
        // Mock failed Kashier API response
        Http::fake([
            'test-fep.kashier.io/v3/orders/' => Http::response([
                'response' => [
                    'result' => 'FAILED',
                    'status' => 'DECLINED',
                    'transactionResponseMessage' => [
                        'en' => 'Card declined',
                    ],
                ],
                'message' => 'Payment failed',
            ], 200),
        ]);

        $result = $this->gateway->processPaymentWithoutRedirection($this->order, $this->validCardData);

        expect($result)->toBeInstanceOf(PaymentResponseData::class);
        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toBe('Card declined');

        // Verify order was not updated to paid
        $this->order->refresh();
        expect($this->order->payment_status)->toBe(PaymentStatus::PENDING);
    });

    it('handles 3DS authentication requirement', function () {
        // Mock 3DS response from Kashier
        Http::fake([
            'test-fep.kashier.io/v3/orders/' => Http::response([
                'response' => [
                    'result' => 'SUCCESS',
                    'status' => 'AUTHENTICATION_INITIATED',
                    'authentication' => [
                        'redirectUrl' => 'https://3ds.example.com/auth',
                        'redirectHtml' => '<form>...</form>',
                    ],
                ],
            ], 200),
        ]);

        $result = $this->gateway->processPaymentWithoutRedirection($this->order, $this->validCardData);

        expect($result)->toBeInstanceOf(PaymentResponseData::class);
        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toBe('3D Secure authentication required');
        expect($result->rawData['requires_3ds'])->toBeTrue();
        expect($result->rawData['redirect_url'])->toBe('https://3ds.example.com/auth');
    });

    it('validates required card fields', function () {
        $incompleteCardData = [
            'cardNumber' => '5123450000000008',
            // Missing other required fields
        ];

        $result = $this->gateway->processPaymentWithoutRedirection($this->order, $incompleteCardData);

        expect($result)->toBeInstanceOf(PaymentResponseData::class);
        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toContain('Missing required field:');
    });

    it('handles API connection errors', function () {
        // Mock HTTP failure
        Http::fake([
            'test-fep.kashier.io/v3/orders/' => Http::response([], 500),
        ]);

        $result = $this->gateway->processPaymentWithoutRedirection($this->order, $this->validCardData);

        expect($result)->toBeInstanceOf(PaymentResponseData::class);
        expect($result->success)->toBeFalse();
        expect($result->errorMessage)->toContain('Payment API call failed');
    });
});
