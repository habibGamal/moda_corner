<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderReturnService;
use App\Services\RefundService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();

    // Set up test configuration for Kashier
    Config::set('services.kashier', [
        'merchant_id' => 'MID-12345',
        'api_key' => 'test-api-key-12345',
        'secret_key' => 'test-secret-key-12345',
        'mode' => 'test',
    ]);

    $this->refundService = app(RefundService::class);
    $this->orderReturnService = app(OrderReturnService::class);
    Auth::login($this->user);
});

describe('isOrderEligibleForReturn', function () {
    it('returns true for eligible delivered order within 14 days', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(10),
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeTrue();
    });

    it('returns false for order that is not delivered', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
            'delivered_at' => null,
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeFalse();
    });

    it('returns false for order already returned', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(5),
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeFalse();
    });

    it('returns false for order without delivery date', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => null,
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeFalse();
    });

    it('returns false for order delivered more than 14 days ago', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(15),
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeFalse();
    });

    it('returns false for order delivered exactly 15 days ago', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(15),
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeFalse();
    });
    it('returns true for order delivered exactly 14 days ago', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(14)->addHours(1),
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->isOrderEligibleForReturn($order);

        expect($result)->toBeTrue();
    });
});

describe('requestReturn', function () {
    it('successfully requests return for eligible order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(7),
            'return_status' => null,
        ]);

        $reason = 'Product damaged on arrival';
        $result = $this->orderReturnService->requestReturn($order->id, $reason);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_REQUESTED);
        expect($result->return_reason)->toBe($reason);
        expect($result->return_requested_at)->not->toBeNull();
    });

    it('throws exception for unauthenticated user', function () {
        Auth::logout();

        $order = Order::factory()->create([
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(7),
            'return_status' => null,
        ]);

        expect(fn() => $this->orderReturnService->requestReturn($order->id, 'Test reason'))
            ->toThrow(Exception::class, 'User must be authenticated to request return.');
    });

    it('throws exception for order not owned by user', function () {
        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(7),
            'return_status' => null,
        ]);

        expect(fn() => $this->orderReturnService->requestReturn($order->id, 'Test reason'))
            ->toThrow(ModelNotFoundException::class);
    });

    it('throws exception for ineligible order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
            'delivered_at' => null,
            'return_status' => null,
        ]);

        expect(fn() => $this->orderReturnService->requestReturn($order->id, 'Test reason'))
            ->toThrow(Exception::class, 'This order is not eligible for return.');
    });

    it('throws exception for order already returned', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::DELIVERED,
            'delivered_at' => now()->subDays(7),
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        expect(fn() => $this->orderReturnService->requestReturn($order->id, 'Test reason'))
            ->toThrow(Exception::class, 'This order is not eligible for return.');
    });
});

describe('approveReturn', function () {
    it('successfully approves return request', function () {
        Auth::login($this->admin);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => now()->subHours(2),
            'return_reason' => 'Product damaged',
        ]);

        $result = $this->orderReturnService->approveReturn($order->id);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_APPROVED);
    });

    it('throws exception for non-existent order', function () {
        expect(fn() => $this->orderReturnService->approveReturn(99999))
            ->toThrow(ModelNotFoundException::class);
    });

    it('throws exception for order not in return requested status', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_APPROVED,
        ]);

        expect(fn() => $this->orderReturnService->approveReturn($order->id))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });

    it('throws exception for order without return status', function () {
        $order = Order::factory()->create([
            'return_status' => null,
        ]);

        expect(fn() => $this->orderReturnService->approveReturn($order->id))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });
});

describe('rejectReturn', function () {
    it('successfully rejects return request without rejection reason', function () {
        Auth::login($this->admin);

        $originalReason = 'Product damaged';
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_reason' => $originalReason,
        ]);

        $result = $this->orderReturnService->rejectReturn($order->id);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_REJECTED);
        expect($result->return_reason)->toBe($originalReason);
    });
    it('successfully rejects return request with rejection reason', function () {
        Auth::login($this->admin);

        $originalReason = 'Product damaged';
        $rejectionReason = 'No damage found upon inspection';
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_reason' => $originalReason,
        ]);

        $result = $this->orderReturnService->rejectReturn($order->id, $rejectionReason);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_REJECTED);
        expect($result->return_reason)->toBe($originalReason . ' | Rejection: ' . $rejectionReason);
    });

    it('throws exception for order not in return requested status', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_APPROVED,
        ]);

        expect(fn() => $this->orderReturnService->rejectReturn($order->id))
            ->toThrow(Exception::class, 'Return request is not in a valid state for rejection.');
    });
});

describe('completeReturn', function () {
    it('successfully completes return for COD order', function () {
        Auth::login($this->admin);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $result = $this->orderReturnService->completeReturn($order->id);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::ITEM_RETURNED);
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(12); // 10 + 2 returned
    });
    it('successfully completes return for paid order with refund', function () {
        Auth::login($this->admin);

        // Mock HTTP calls for Kashier refund
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

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::PAID,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $result = $this->orderReturnService->completeReturn($order->id);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::REFUND_PROCESSED);
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(6); // 5 + 1 returned
    });

    it('throws exception for order not in approved status', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        expect(fn() => $this->orderReturnService->completeReturn($order->id))
            ->toThrow(Exception::class, 'Return must be approved before it can be completed.');
    });

    it('handles multiple order items correctly', function () {
        Auth::login($this->admin);

        $product1 = Product::factory()->create();
        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 10
        ]);

        $product2 = Product::factory()->create();
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 20
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'variant_id' => $variant1->id,
            'quantity' => 3,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'variant_id' => $variant2->id,
            'quantity' => 5,
        ]);

        $this->orderReturnService->completeReturn($order->id);

        // Verify both variants had stock returned
        $variant1->refresh();
        $variant2->refresh();
        expect($variant1->quantity)->toBe(13); // 10 + 3
        expect($variant2->quantity)->toBe(25); // 20 + 5
    });
});

describe('getPendingReturnOrders', function () {
    it('returns orders with return requested status', function () {
        $order1 = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => now()->subHours(2),
        ]);

        $order2 = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => now()->subHours(1),
        ]);

        Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_APPROVED,
        ]);

        $result = $this->orderReturnService->getPendingReturnOrders();

        expect($result)->toHaveCount(2);
        expect($result->pluck('id'))->toContain($order1->id, $order2->id);
        // Should be ordered by return_requested_at ascending (oldest first)
        expect($result->first()->id)->toBe($order1->id);
    });

    it('returns empty collection when no pending returns', function () {
        Order::factory()->create(['return_status' => ReturnStatus::RETURN_APPROVED]);
        Order::factory()->create(['return_status' => null]);

        $result = $this->orderReturnService->getPendingReturnOrders();

        expect($result)->toHaveCount(0);
    });
});

describe('getReturnStatistics', function () {
    it('returns correct statistics', function () {
        // Create orders with different return statuses
        Order::factory()->count(3)->create(['return_status' => ReturnStatus::RETURN_REQUESTED]);
        Order::factory()->count(2)->create(['return_status' => ReturnStatus::RETURN_APPROVED]);
        Order::factory()->count(1)->create(['return_status' => ReturnStatus::RETURN_REJECTED]);
        Order::factory()->count(2)->create(['return_status' => ReturnStatus::ITEM_RETURNED]);
        Order::factory()->count(1)->create(['return_status' => ReturnStatus::REFUND_PROCESSED]);
        Order::factory()->count(5)->create(['return_status' => null]); // No return status

        $result = $this->orderReturnService->getReturnStatistics();

        expect($result)->toBeArray();
        expect($result['pending_requests'])->toBe(3);
        expect($result['approved_returns'])->toBe(2);
        expect($result['completed_returns'])->toBe(3); // ITEM_RETURNED + REFUND_PROCESSED
        expect($result['rejected_returns'])->toBe(1);
    });

    it('returns zero statistics when no orders with return status', function () {
        Order::factory()->count(5)->create(['return_status' => null]);

        $result = $this->orderReturnService->getReturnStatistics();

        expect($result['pending_requests'])->toBe(0);
        expect($result['approved_returns'])->toBe(0);
        expect($result['completed_returns'])->toBe(0);
        expect($result['rejected_returns'])->toBe(0);
    });
});

describe('getUserReturnHistory', function () {
    it('returns user return history for authenticated user', function () {
        $order1 = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => now()->subDays(2),
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::REFUND_PROCESSED,
            'return_requested_at' => now()->subDays(1),
        ]);

        // Order from different user
        Order::factory()->create([
            'user_id' => User::factory()->create()->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
        ]);

        // Order without return status
        Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->getUserReturnHistory();

        expect($result)->toHaveCount(2);
        expect($result->pluck('id'))->toContain($order1->id, $order2->id);
        // Should be ordered by return_requested_at descending (newest first)
        expect($result->first()->id)->toBe($order2->id);
    });

    it('returns user return history for specific user', function () {
        $otherUser = User::factory()->create();

        $order1 = Order::factory()->create([
            'user_id' => $otherUser->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'return_requested_at' => now()->subDays(1),
        ]);

        Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        $result = $this->orderReturnService->getUserReturnHistory($otherUser->id);

        expect($result)->toHaveCount(1);
        expect($result->first()->id)->toBe($order1->id);
    });

    it('returns empty collection when user has no return history', function () {
        Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => null,
        ]);

        $result = $this->orderReturnService->getUserReturnHistory();

        expect($result)->toHaveCount(0);
    });
});
