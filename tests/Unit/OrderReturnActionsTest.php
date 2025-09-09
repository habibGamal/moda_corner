<?php

use App\Actions\Orders\ApproveReturnAction;
use App\Actions\Orders\CompleteReturnAction;
use App\Actions\Orders\RejectReturnAction;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderReturnService;
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

    $this->orderReturnService = app(OrderReturnService::class);
    $this->approveReturnAction = app(ApproveReturnAction::class);
    $this->completeReturnAction = app(CompleteReturnAction::class);
    $this->rejectReturnAction = app(RejectReturnAction::class);

    Auth::login($this->admin);
});

describe('ApproveReturnAction', function () {
    it('successfully approves a return request', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_requested_at' => now()->subHours(2),
            'return_reason' => 'Product damaged',
        ]);

        $result = $this->approveReturnAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_APPROVED);
        expect($result->id)->toBe($order->id);
    });

    it('throws exception when order is not in return requested status', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_APPROVED,
        ]);

        expect(fn () => $this->approveReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });

    it('throws exception when order has no return status', function () {
        $order = Order::factory()->create([
            'return_status' => null,
        ]);

        expect(fn () => $this->approveReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });

    it('throws exception when order is already rejected', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REJECTED,
        ]);

        expect(fn () => $this->approveReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });

    it('throws exception when return is already completed', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::ITEM_RETURNED,
        ]);

        expect(fn () => $this->approveReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });
});

describe('RejectReturnAction', function () {
    it('successfully rejects a return request without reason', function () {
        $originalReason = 'Product damaged';
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_reason' => $originalReason,
        ]);

        $result = $this->rejectReturnAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_REJECTED);
        expect($result->return_reason)->toBe($originalReason);
        expect($result->id)->toBe($order->id);
    });

    it('successfully rejects a return request with rejection reason', function () {
        $originalReason = 'Product damaged';
        $rejectionReason = 'No damage found upon inspection';
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_reason' => $originalReason,
        ]);

        $result = $this->rejectReturnAction->execute($order, $rejectionReason);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::RETURN_REJECTED);
        expect($result->return_reason)->toBe($originalReason.' | Rejection: '.$rejectionReason);
        expect($result->id)->toBe($order->id);
    });

    it('throws exception when order is not in return requested status', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_APPROVED,
        ]);

        expect(fn () => $this->rejectReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for rejection.');
    });

    it('throws exception when order has no return status', function () {
        $order = Order::factory()->create([
            'return_status' => null,
        ]);

        expect(fn () => $this->rejectReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for rejection.');
    });

    it('throws exception when order is already rejected', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REJECTED,
        ]);

        expect(fn () => $this->rejectReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for rejection.');
    });
});

describe('CompleteReturnAction', function () {
    it('successfully completes return for COD order', function () {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
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

        $result = $this->completeReturnAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::ITEM_RETURNED);
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
        expect($result->id)->toBe($order->id);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(12); // 10 + 2 returned
    });

    it('successfully completes return for paid order with refund', function () {
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
                    'en' => 'Refund successful',
                ],
            ], 200),
        ]);

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_APPROVED,
            'payment_method' => PaymentMethod::CREDIT_CARD,
            'payment_status' => PaymentStatus::CARD
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $result = $this->completeReturnAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::REFUND_PROCESSED);
        expect($result->payment_status)->toBe(PaymentStatus::REFUNDED);
        expect($result->id)->toBe($order->id);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(6); // 5 + 1 returned
    });

    it('throws exception when order is not in approved status', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        expect(fn () => $this->completeReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return must be approved before it can be completed.');
    });

    it('throws exception when order has no return status', function () {
        $order = Order::factory()->create([
            'return_status' => null,
        ]);

        expect(fn () => $this->completeReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return must be approved before it can be completed.');
    });

    it('throws exception when order is rejected', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REJECTED,
        ]);

        expect(fn () => $this->completeReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return must be approved before it can be completed.');
    });

    it('throws exception when return is already completed', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::ITEM_RETURNED,
        ]);

        expect(fn () => $this->completeReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return must be approved before it can be completed.');
    });

    it('handles multiple order items correctly', function () {
        $product1 = Product::factory()->create();
        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 10,
        ]);

        $product2 = Product::factory()->create();
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 20,
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

        $result = $this->completeReturnAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->return_status)->toBe(ReturnStatus::ITEM_RETURNED);

        // Verify both variants had stock returned
        $variant1->refresh();
        $variant2->refresh();
        expect($variant1->quantity)->toBe(13); // 10 + 3
        expect($variant2->quantity)->toBe(25); // 20 + 5
    });
});

describe('Actions Integration', function () {
    it('can complete full return workflow using actions', function () {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_reason' => 'Product defective',
            'payment_method' => PaymentMethod::CASH_ON_DELIVERY,
            'payment_status' => PaymentStatus::PENDING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Step 1: Approve the return
        $approvedOrder = $this->approveReturnAction->execute($order);
        expect($approvedOrder->return_status)->toBe(ReturnStatus::RETURN_APPROVED);

        // Step 2: Complete the return
        $completedOrder = $this->completeReturnAction->execute($approvedOrder);
        expect($completedOrder->return_status)->toBe(ReturnStatus::ITEM_RETURNED);
        expect($completedOrder->payment_status)->toBe(PaymentStatus::REFUNDED);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(12); // 10 + 2 returned
    });

    it('can reject return workflow using actions', function () {
        $originalReason = 'Product defective';
        $rejectionReason = 'Product appears to be in good condition';

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'return_status' => ReturnStatus::RETURN_REQUESTED,
            'return_reason' => $originalReason,
        ]);

        // Reject the return
        $rejectedOrder = $this->rejectReturnAction->execute($order, $rejectionReason);
        expect($rejectedOrder->return_status)->toBe(ReturnStatus::RETURN_REJECTED);
        expect($rejectedOrder->return_reason)->toBe($originalReason.' | Rejection: '.$rejectionReason);
    });

    it('prevents completing return without approval', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::RETURN_REQUESTED,
        ]);

        expect(fn () => $this->completeReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return must be approved before it can be completed.');
    });

    it('prevents approving already processed returns', function () {
        $order = Order::factory()->create([
            'return_status' => ReturnStatus::REFUND_PROCESSED,
        ]);

        expect(fn () => $this->approveReturnAction->execute($order))
            ->toThrow(Exception::class, 'Return request is not in a valid state for approval.');
    });
});
