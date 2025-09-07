<?php

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\MarkOrderAsDeliveredAction;
use App\Actions\Orders\MarkOrderAsShippedAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();

    $this->cancelOrderAction = app(CancelOrderAction::class);
    $this->markOrderAsShippedAction = app(MarkOrderAsShippedAction::class);
    $this->markOrderAsDeliveredAction = app(MarkOrderAsDeliveredAction::class);

    Auth::login($this->admin);
});

describe('CancelOrderAction', function () {
    it('successfully cancels a processing order', function () {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $result = $this->cancelOrderAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->order_status)->toBe(OrderStatus::CANCELLED);
        expect($result->id)->toBe($order->id);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(13); // 10 + 3 returned
    });

    it('successfully cancels a shipped order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::SHIPPED,
        ]);

        $result = $this->cancelOrderAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->order_status)->toBe(OrderStatus::CANCELLED);
    });

    it('throws exception when trying to cancel delivered order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::DELIVERED,
        ]);

        expect(fn() => $this->cancelOrderAction->execute($order))
            ->toThrow(Exception::class, 'Only orders in processing status can be cancelled');
    });

    it('throws exception when trying to cancel already cancelled order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::CANCELLED,
        ]);

        expect(fn() => $this->cancelOrderAction->execute($order))
            ->toThrow(Exception::class, 'Only orders in processing status can be cancelled');
    });

    it('returns inventory to stock for multiple items', function () {
        $product1 = Product::factory()->create();
        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product1->id,
            'quantity' => 5
        ]);

        $product2 = Product::factory()->create();
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product2->id,
            'quantity' => 15
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'variant_id' => $variant1->id,
            'quantity' => 2,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'variant_id' => $variant2->id,
            'quantity' => 4,
        ]);

        $this->cancelOrderAction->execute($order);

        // Verify both variants had stock returned
        $variant1->refresh();
        $variant2->refresh();
        expect($variant1->quantity)->toBe(7); // 5 + 2
        expect($variant2->quantity)->toBe(19); // 15 + 4
    });

    it('handles orders without variants gracefully', function () {
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 2,
        ]);

        $result = $this->cancelOrderAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->order_status)->toBe(OrderStatus::CANCELLED);
    });
});

describe('MarkOrderAsShippedAction', function () {
    it('successfully marks processing order as shipped', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        $result = $this->markOrderAsShippedAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->order_status)->toBe(OrderStatus::SHIPPED);
        expect($result->id)->toBe($order->id);
    });

    it('throws exception when trying to ship non-processing order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::SHIPPED,
        ]);

        expect(fn() => $this->markOrderAsShippedAction->execute($order))
            ->toThrow(Exception::class, 'Only processing orders can be marked as shipped.');
    });

    it('throws exception when trying to ship delivered order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::DELIVERED,
        ]);

        expect(fn() => $this->markOrderAsShippedAction->execute($order))
            ->toThrow(Exception::class, 'Only processing orders can be marked as shipped.');
    });

    it('throws exception when trying to ship cancelled order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::CANCELLED,
        ]);

        expect(fn() => $this->markOrderAsShippedAction->execute($order))
            ->toThrow(Exception::class, 'Only processing orders can be marked as shipped.');
    });
});

describe('MarkOrderAsDeliveredAction', function () {
    it('successfully marks processing order as delivered', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
            'delivered_at' => null,
        ]);

        $result = $this->markOrderAsDeliveredAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->order_status)->toBe(OrderStatus::DELIVERED);
        expect($result->delivered_at)->not->toBeNull();
        expect($result->id)->toBe($order->id);
    });

    it('successfully marks shipped order as delivered', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::SHIPPED,
            'delivered_at' => null,
        ]);

        $result = $this->markOrderAsDeliveredAction->execute($order);

        expect($result)->toBeInstanceOf(Order::class);
        expect($result->order_status)->toBe(OrderStatus::DELIVERED);
        expect($result->delivered_at)->not->toBeNull();
    });

    it('sets delivered_at timestamp correctly', function () {
        $beforeTime = now()->subSecond();

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::SHIPPED,
            'delivered_at' => null,
        ]);

        $result = $this->markOrderAsDeliveredAction->execute($order);
        $afterTime = now()->addSecond();

        expect($result->delivered_at)->toBeInstanceOf(Carbon::class);
        expect($result->delivered_at->between($beforeTime, $afterTime))->toBeTrue();
    });

    it('throws exception when trying to deliver already delivered order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::DELIVERED,
        ]);

        expect(fn() => $this->markOrderAsDeliveredAction->execute($order))
            ->toThrow(Exception::class, 'Only processing or shipped orders can be marked as delivered.');
    });

    it('throws exception when trying to deliver cancelled order', function () {
        $order = Order::factory()->create([
            'order_status' => OrderStatus::CANCELLED,
        ]);

        expect(fn() => $this->markOrderAsDeliveredAction->execute($order))
            ->toThrow(Exception::class, 'Only processing or shipped orders can be marked as delivered.');
    });
});

describe('Order Status Workflow Integration', function () {
    it('can complete full order lifecycle from processing to delivered', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
            'delivered_at' => null,
        ]);

        // Step 1: Mark as shipped
        $shippedOrder = $this->markOrderAsShippedAction->execute($order);
        expect($shippedOrder->order_status)->toBe(OrderStatus::SHIPPED);

        // Step 2: Mark as delivered
        $deliveredOrder = $this->markOrderAsDeliveredAction->execute($shippedOrder);
        expect($deliveredOrder->order_status)->toBe(OrderStatus::DELIVERED);
        expect($deliveredOrder->delivered_at)->not->toBeNull();
    });

    it('can cancel order at processing stage', function () {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $cancelledOrder = $this->cancelOrderAction->execute($order);
        expect($cancelledOrder->order_status)->toBe(OrderStatus::CANCELLED);

        // Verify stock was returned
        $variant->refresh();
        expect($variant->quantity)->toBe(13); // 10 + 3 returned
    });

    it('can cancel order at shipped stage', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::SHIPPED,
        ]);

        $cancelledOrder = $this->cancelOrderAction->execute($order);
        expect($cancelledOrder->order_status)->toBe(OrderStatus::CANCELLED);
    });

    it('prevents invalid state transitions', function () {
        $deliveredOrder = Order::factory()->create([
            'order_status' => OrderStatus::DELIVERED,
        ]);

        // Cannot cancel delivered order
        expect(fn() => $this->cancelOrderAction->execute($deliveredOrder))
            ->toThrow(Exception::class, 'Only orders in processing status can be cancelled');

        // Cannot mark delivered order as shipped
        expect(fn() => $this->markOrderAsShippedAction->execute($deliveredOrder))
            ->toThrow(Exception::class, 'Only processing orders can be marked as shipped.');
    });

    it('allows direct delivery from processing without shipping', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'order_status' => OrderStatus::PROCESSING,
        ]);

        $deliveredOrder = $this->markOrderAsDeliveredAction->execute($order);
        expect($deliveredOrder->order_status)->toBe(OrderStatus::DELIVERED);
        expect($deliveredOrder->delivered_at)->not->toBeNull();
    });
});
