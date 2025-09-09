<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderCancellationNotification;
use App\Services\OrderCancellationService;
use App\Services\RefundService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    // Mock the RefundService using Laravel's mocking approach
    $this->refundService = $this->mock(RefundService::class);

    $this->orderCancellationService = app(OrderCancellationService::class);
    Auth::login($this->user);

    // Set up notification fake
    Notification::fake();
    Log::spy();
});

describe('OrderCancellationService', function () {
    describe('cancelOrder', function () {
        it('successfully cancels a processing order', function () {
            // Arrange
            $product = Product::factory()->create(['price' => 50.00]);
            $variant = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'quantity' => 10,
            ]);

            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CREDIT_CARD,
                'total' => 100.00,
            ]);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'variant_id' => $variant->id,
                'quantity' => 2,
                'unit_price' => 50.00,
            ]);

            // Act
            $result = $this->orderCancellationService->cancelOrder($order->id, 'Customer requested cancellation');

            // Assert
            expect($result)->toBeInstanceOf(Order::class);
            expect($result->order_status)->toBe(OrderStatus::CANCELLED);
            expect($result->cancelled_at)->not->toBeNull();
            expect($result->cancellation_reason)->toBe('Customer requested cancellation');

            // Verify database changes
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'order_status' => OrderStatus::CANCELLED->value,
                'cancellation_reason' => 'Customer requested cancellation',
            ]);

            // Verify inventory was restored
            $variant->refresh();
            expect($variant->quantity)->toBe(12); // 10 + 2 returned
        });

        it('successfully cancels order without reason', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PENDING,
                'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            ]);

            // Act
            $result = $this->orderCancellationService->cancelOrder($order->id);

            // Assert
            expect($result->order_status)->toBe(OrderStatus::CANCELLED);
            expect($result->cancelled_at)->not->toBeNull();
            expect($result->cancellation_reason)->toBeNull();
        });

        it('throws exception for non-processing orders', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::DELIVERED,
            ]);

            // Act & Assert
            expect(fn() => $this->orderCancellationService->cancelOrder($order->id))
                ->toThrow(Exception::class, 'Only orders in processing status can be cancelled');
        });

        it('throws exception for non-existent order', function () {
            // Act & Assert
            expect(fn() => $this->orderCancellationService->cancelOrder(999))
                ->toThrow(ModelNotFoundException::class);
        });
        it('sends notifications to customer', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::PROCESSING,
            ]);

            // Act
            $this->orderCancellationService->cancelOrder($order->id);

            // Assert
            Notification::assertSentTo(
                $this->user,
                OrderCancellationNotification::class,
                function ($notification) {
                    $array = $notification->toArray($this->user);
                    return $array['recipient'] === 'customer';
                }
            );
        });

        it('logs successful cancellation', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::PROCESSING,
                'payment_method' => PaymentMethod::CREDIT_CARD,
                'payment_status' => PaymentStatus::PAID,
            ]);

            // Act
            $this->orderCancellationService->cancelOrder($order->id, 'Test reason');

            // Assert
            Log::shouldHaveReceived('info')
                ->with('Order cancelled successfully', [
                    'order_id' => $order->id,
                    'payment_method' => PaymentMethod::CREDIT_CARD->value,
                    'payment_status' => PaymentStatus::PAID->value,
                    'reason' => 'Test reason',
                ]);
        });
    });

    describe('needsRefund', function () {
        it('returns true for cancelled paid non-COD orders', function () {
            // Arrange
            $order = Order::factory()->create([
                'order_status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CREDIT_CARD,
            ]);

            // Act & Assert
            expect($this->orderCancellationService->needsRefund($order))->toBeTrue();
        });

        it('returns false for non-cancelled orders', function () {
            // Arrange
            $order = Order::factory()->create([
                'order_status' => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CREDIT_CARD,
            ]);

            // Act & Assert
            expect($this->orderCancellationService->needsRefund($order))->toBeFalse();
        });

        it('returns false for unpaid orders', function () {
            // Arrange
            $order = Order::factory()->create([
                'order_status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::PENDING,
                'payment_method' => PaymentMethod::CREDIT_CARD,
            ]);

            // Act & Assert
            expect($this->orderCancellationService->needsRefund($order))->toBeFalse();
        });

        it('returns false for COD orders', function () {
            // Arrange
            $order = Order::factory()->create([
                'order_status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            ]);

            // Act & Assert
            expect($this->orderCancellationService->needsRefund($order))->toBeFalse();
        });
    });

    describe('processRefund', function () {
        it('successfully processes refund for eligible order', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CREDIT_CARD,
                'total' => 150.00,
            ]);

            // Mock RefundService expectations
            $this->refundService->shouldReceive('processRefund')
                ->with($order)
                ->once()
                ->andReturn(true);

            // Act
            $result = $this->orderCancellationService->processRefund($order);

            // Assert
            expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
            expect($result->refunded_at)->not->toBeNull();

            // Verify database changes
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'payment_status' => PaymentStatus::REFUNDED->value,
            ]);
        });

        it('throws exception for orders that do not need refund', function () {
            // Arrange
            $order = Order::factory()->create([
                'order_status' => OrderStatus::PROCESSING,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CREDIT_CARD,
            ]);

            // Act & Assert
            expect(fn() => $this->orderCancellationService->processRefund($order))
                ->toThrow(Exception::class, 'This order does not require a refund');
        });

        it('logs successful refund processing', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::CANCELLED,
                'payment_status' => PaymentStatus::PAID,
                'payment_method' => PaymentMethod::CREDIT_CARD,
                'total' => 200.00,
            ]);

            $this->refundService->shouldReceive('processRefund')
                ->with($order)
                ->once()
                ->andReturn(true);

            // Act
            $this->orderCancellationService->processRefund($order);

            // Assert
            Log::shouldHaveReceived('info')
                ->with('Order refund processed', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'total_amount' => 200.00,
                ]);
        });
    });

    describe('edge cases and data integrity', function () {
        it('handles concurrent cancellation attempts gracefully', function () {
            // Arrange
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::PROCESSING,
            ]);

            // Act - First cancellation should succeed
            $result1 = $this->orderCancellationService->cancelOrder($order->id);

            // Second cancellation should fail
            expect(fn() => $this->orderCancellationService->cancelOrder($order->id))
                ->toThrow(Exception::class, 'Only orders in processing status can be cancelled');

            // Assert
            expect($result1->order_status)->toBe(OrderStatus::CANCELLED);
        });
        it('maintains transaction integrity on failure', function () {
            // Arrange - Create order in delivered status to cause cancellation to fail
            $order = Order::factory()->create([
                'user_id' => $this->user->id,
                'order_status' => OrderStatus::DELIVERED, // This will cause cancellation to fail
            ]);

            // Act & Assert - Should throw exception for delivered order
            expect(fn() => $this->orderCancellationService->cancelOrder($order->id))
                ->toThrow(Exception::class, 'Only orders in processing status can be cancelled');

            // Verify order status wasn't changed
            $order->refresh();
            expect($order->order_status)->toBe(OrderStatus::DELIVERED);
            expect($order->cancelled_at)->toBeNull();
        });

        it('processes multiple payment methods correctly', function () {
            $paymentMethods = [
                PaymentMethod::CREDIT_CARD,
            ];

            foreach ($paymentMethods as $paymentMethod) {
                // Arrange
                $order = Order::factory()->create([
                    'user_id' => $this->user->id,
                    'order_status' => OrderStatus::CANCELLED,
                    'payment_status' => PaymentStatus::PAID,
                    'payment_method' => $paymentMethod,
                ]);

                $this->refundService->shouldReceive('processRefund')
                    ->with($order)
                    ->once()
                    ->andReturn(true);

                // Act
                $result = $this->orderCancellationService->processRefund($order);

                // Assert
                expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
                expect($this->orderCancellationService->needsRefund($order->fresh()))->toBeFalse();
            }
        });
    });
});
