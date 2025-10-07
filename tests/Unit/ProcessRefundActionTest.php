<?php

use App\Actions\Orders\ProcessRefundAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->processRefundAction = app(ProcessRefundAction::class);
});

describe('ProcessRefundAction', function () {
    it('successfully processes refund for a valid cancelled order', function () {
        // Arrange
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'total' => 100.00,
            'cancelled_at' => now(),
        ]);

        // Act
        $result = $this->processRefundAction->execute($order);

        // Assert
        expect($result)->toBeInstanceOf(Order::class);
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
        expect($result->refunded_at)->not->toBeNull();

        // Verify database changes
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => PaymentStatus::REFUNDED->value,
        ]);
    });

    it('throws exception for non-cancelled orders', function () {
        // Arrange
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);

        // Act & Assert
        expect(fn () => $this->processRefundAction->execute($order))
            ->toThrow(Exception::class, 'Only cancelled orders can be refunded.');
    });

    it('throws exception for unpaid orders', function () {
        // Arrange
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::PENDING,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);

        // Act & Assert
        expect(fn () => $this->processRefundAction->execute($order))
            ->toThrow(Exception::class, 'Only paid orders can be refunded.');
    });

    it('throws exception for cash on delivery orders', function () {
        // Arrange
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
        ]);

        // Act & Assert
        expect(fn () => $this->processRefundAction->execute($order))
            ->toThrow(Exception::class, 'Cash on delivery orders do not require refunds.');
    });

    it('throws exception for already refunded orders', function () {
        // Arrange
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::REFUNDED,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);

        // Act & Assert
        expect(fn () => $this->processRefundAction->execute($order))
            ->toThrow(Exception::class, 'This order has already been refunded.');
    });

    it('successfully processes refund for Kashier payment method', function () {
        // Arrange
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'total' => 250.50,
            'cancelled_at' => now(),
        ]);

        // Act
        $result = $this->processRefundAction->execute($order);        // Assert
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
        expect($result->refunded_at)->not->toBeNull();
        expect((float) $result->total)->toBe(250.50);
    });

    it('maintains order data integrity during refund process', function () {
        // Arrange
        $originalOrderData = [
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => PaymentStatus::PAID,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'subtotal' => 85.00,
            'shipping_cost' => 15.00,
            'total' => 100.00,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Customer request',
        ];

        $order = Order::factory()->create($originalOrderData);

        // Act
        $result = $this->processRefundAction->execute($order);        // Assert - verify only payment_status and refunded_at changed
        expect($result->user_id)->toBe($originalOrderData['user_id']);
        expect($result->order_status)->toBe($originalOrderData['order_status']);
        expect($result->payment_method)->toBe($originalOrderData['payment_method']);
        expect((float) $result->subtotal)->toBe($originalOrderData['subtotal']);
        expect((float) $result->shipping_cost)->toBe($originalOrderData['shipping_cost']);
        expect((float) $result->total)->toBe($originalOrderData['total']);
        expect($result->cancellation_reason)->toBe($originalOrderData['cancellation_reason']);
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
        expect($result->refunded_at)->not->toBeNull();
    });
});
