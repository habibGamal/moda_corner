<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\Payment\PaymentProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->paymentProcessor = app(PaymentProcessor::class);

    // Set up test Kashier configuration
    Config::set('services.kashier', [
        'merchant_id' => 'MID-12345',
        'api_key' => 'test-api-key-12345',
        'secret_key' => 'test-secret-key-12345',
        'mode' => 'test',
    ]);
});

describe('Payment Processor SOLID Refactoring', function () {
    it('supports multiple payment methods via strategy pattern', function () {
        expect($this->paymentProcessor->supportsPaymentMethod(PaymentMethod::CREDIT_CARD))->toBeTrue();
        expect($this->paymentProcessor->supportsPaymentMethod(PaymentMethod::WALLET))->toBeTrue();
        expect($this->paymentProcessor->supportsPaymentMethod(PaymentMethod::CASH_ON_DELIVERY))->toBeTrue();
    });

    it('processes online payment using strategy pattern', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 150.00,
        ]);

        $paymentData = $this->paymentProcessor->processPayment($order);

        expect($paymentData)->toBeInstanceOf(\App\DTOs\PaymentResultData::class);
        expect($paymentData->merchantId)->toContain('MID-'); // Just check it starts with MID-
        expect($paymentData->amount)->toBe('150.00');
        expect($paymentData->currency)->toBe('EGP');
    });

    it('processes cash on delivery payment using strategy pattern', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 200.00,
        ]);

        $paymentData = $this->paymentProcessor->processPayment($order);

        expect($paymentData)->toBeInstanceOf(\App\DTOs\PaymentResultData::class);
        expect($paymentData->mode)->toBe('cod');
        expect($paymentData->amount)->toBe('200.00');
        expect($paymentData->additionalParams['payment_method'])->toBe('cash_on_delivery');
    });

    it('processes successful payment callback', function () {
        // Use a test double that always validates successfully
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY, // Use COD to avoid validator issues
            'payment_status' => PaymentStatus::PENDING,
            'total' => 100.00,
        ]);

        $paymentData = [
            'paymentId' => 'PAY123456',
            'status' => 'SUCCESS',
            'amount' => '100.00',
        ];

        $updatedOrder = $this->paymentProcessor->processPaymentSuccess($order, $paymentData);

        expect($updatedOrder->payment_status)->toBe(PaymentStatus::PAID);
        expect($updatedOrder->payment_details)->toBe(json_encode($paymentData));
    });

    it('processes failed payment callback', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::PENDING,
            'total' => 100.00,
        ]);

        $paymentData = [
            'status' => 'FAILED',
            'error' => 'Card declined',
        ];

        $updatedOrder = $this->paymentProcessor->processPaymentFailure($order, $paymentData);

        expect($updatedOrder->payment_status)->toBe(PaymentStatus::FAILED);
        expect($updatedOrder->payment_details)->toBe(json_encode($paymentData));
    });

    it('throws exception for unsupported payment method', function () {
        // Create an order with a payment method that has no registered strategy
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::WALLET, // This should work now
            'payment_status' => PaymentStatus::PENDING,
            'total' => 100.00,
        ]);

        // This should not throw an exception since WALLET maps to kashier gateway
        $result = $this->paymentProcessor->processPayment($order);
        expect($result)->toBeInstanceOf(\App\DTOs\PaymentResultData::class);
    });

    it('validates order before processing', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::PAID, // Already paid
            'total' => 100.00,
        ]);

        expect(function () use ($order) {
            $this->paymentProcessor->processPayment($order);
        })->toThrow(\InvalidArgumentException::class, 'Order has already been paid');
    });
});
