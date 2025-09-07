<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\KashierPaymentService;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    // Set up test configuration
    Config::set('services.kashier', [
        'merchant_id' => 'MID-12345',
        'api_key' => 'test-api-key-12345',
        'secret_key' => 'test-secret-key-12345',
        'mode' => 'test',
    ]);

    $this->kashierService = app(KashierPaymentService::class);
    $this->refundService = app(RefundService::class);
});

describe('Refund Processing', function () {
    it('processes Kashier refund successfully', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 150.00,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'payment_id' => 'kashier-payment-123',
        ]);

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => [
                    'status' => 'REFUNDED',
                    'transactionId' => 'TX-69827599',
                    'gatewayCode' => 'APPROVED',
                    'cardOrderId' => 'card-order-123',
                    'orderReference' => 'TEST-ORD-37089',
                ],
                'messages' => [
                    'en' => 'Refund successful'
                ]
            ], 200)
        ]);

        $result = $this->refundService->processRefund($order);

        expect($result)->toBeTrue();
    });

    it('processes COD order without refund API call', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 150.00,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        // Should not make any HTTP calls for COD
        Http::fake();

        $result = $this->refundService->processRefund($order);

        expect($result)->toBeTrue();
        Http::assertNothingSent();
    });

    it('constructs correct Kashier order ID', function () {
        $order = Order::factory()->create([
            'id' => 12345,
            'user_id' => $this->user->id,
            'total' => 150.75,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'payment_id' => 'kashier-payment-123',
        ]);

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'SUCCESS',
                'response' => ['status' => 'REFUNDED'],
                'messages' => ['en' => 'Success']
            ], 200)
        ]);

        $this->refundService->processRefund($order);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '12345');
        });
    });

    it('throws exception when Kashier refund fails', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total' => 150.00,
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'payment_id' => 'kashier-payment-123',
        ]);

        Http::fake([
            'https://test-fep.kashier.io/v3/orders/*' => Http::response([
                'status' => 'FAILED',
                'response' => [
                    'status' => 'FAILED',
                    'gatewayCode' => 'DECLINED'
                ],
                'messages' => [
                    'en' => 'Refund declined by gateway'
                ]
            ], 400)
        ]);

        expect(fn() => $this->refundService->processRefund($order))
            ->toThrow(Exception::class, 'Kashier refund failed: Refund declined by gateway');
    });
});

describe('Refund Eligibility Check', function () {
    it('returns false for COD orders', function () {
        $order = Order::factory()->create([
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $canRefund = $this->refundService->canProcessRefund($order);

        expect($canRefund)->toBeFalse();
    });

    it('returns false for unpaid orders', function () {
        $order = Order::factory()->create([
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        $canRefund = $this->refundService->canProcessRefund($order);

        expect($canRefund)->toBeFalse();
    });

    it('returns false for Kashier orders without payment ID', function () {
        $order = Order::factory()->create([
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'payment_id' => null,
        ]);

        $canRefund = $this->refundService->canProcessRefund($order);

        expect($canRefund)->toBeFalse();
    });

    it('returns true for eligible Kashier orders', function () {
        $order = Order::factory()->create([
            'payment_method' => PaymentMethod::KASHIER,
            'payment_status' => PaymentStatus::PAID,
            'payment_id' => 'kashier-payment-123',
        ]);

        $canRefund = $this->refundService->canProcessRefund($order);

        expect($canRefund)->toBeTrue();
    });
});
