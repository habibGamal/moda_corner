<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReturnOrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnOrder;
use App\Models\User;
use App\Services\ReturnOrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->address = Address::factory()->create(['user_id' => $this->user->id]);

    $this->returnOrderService = app(ReturnOrderService::class);

    // Create a delivered order for testing
    $this->order = Order::factory()->create([
        'user_id' => $this->user->id,
        'order_status' => OrderStatus::DELIVERED,
        'payment_status' => PaymentStatus::PAID,
        'payment_method' => PaymentMethod::CREDIT_CARD,
        'delivered_at' => Carbon::now()->subDays(5), // Delivered 5 days ago
        'shipping_address_id' => $this->address->id,
    ]);

    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'quantity' => 100,
    ]);

    $this->orderItem = OrderItem::factory()->create([
        'order_id' => $this->order->id,
        'product_id' => $this->product->id,
        'variant_id' => $this->variant->id,
        'quantity' => 3,
        'unit_price' => 50.00,
        'subtotal' => 150.00,
    ]);
});

it('can check if an order is eligible for return', function () {
    expect($this->returnOrderService->isOrderEligibleForReturn($this->order))->toBeTrue();
});

it('returns false for orders not delivered', function () {
    $this->order->update(['order_status' => OrderStatus::PROCESSING]);

    expect($this->returnOrderService->isOrderEligibleForReturn($this->order))->toBeFalse();
});

it('returns false for orders delivered more than 14 days ago', function () {
    $this->order->update(['delivered_at' => Carbon::now()->subDays(15)]);

    expect($this->returnOrderService->isOrderEligibleForReturn($this->order))->toBeFalse();
});

it('can request a return successfully', function () {
    $this->actingAs($this->user);

    $returnItems = [
        [
            'order_item_id' => $this->orderItem->id,
            'quantity' => 2,
            'reason' => 'Defective item',
        ],
    ];

    $returnOrder = $this->returnOrderService->requestReturn(
        $this->order->id,
        $returnItems,
        'General dissatisfaction'
    );

    expect($returnOrder)->toBeInstanceOf(ReturnOrder::class);
    expect($returnOrder->status)->toBe(ReturnOrderStatus::REQUESTED);
    expect($returnOrder->order_id)->toBe($this->order->id);
    expect($returnOrder->user_id)->toBe($this->user->id);
    expect($returnOrder->total_amount)->toBe(100.00); // 2 items × $50
    expect($returnOrder->return_number)->toStartWith('RET-');

    // Check return item was created
    expect($returnOrder->returnItems)->toHaveCount(1);
    expect($returnOrder->returnItems->first()->quantity)->toBe(2);
    expect($returnOrder->returnItems->first()->unit_price)->toBe(50.00);
    expect($returnOrder->returnItems->first()->subtotal)->toBe(100.00);
});

it('can approve a return request', function () {
    $returnOrder = ReturnOrder::factory()->create([
        'order_id' => $this->order->id,
        'user_id' => $this->user->id,
        'status' => ReturnOrderStatus::REQUESTED,
    ]);

    $approvedReturn = $this->returnOrderService->approveReturn($returnOrder->id);

    expect($approvedReturn->status)->toBe(ReturnOrderStatus::APPROVED);
    expect($approvedReturn->approved_at)->not()->toBeNull();
});

it('can get return statistics', function () {
    ReturnOrder::factory()->count(2)->create(['status' => ReturnOrderStatus::REQUESTED]);
    ReturnOrder::factory()->count(1)->create(['status' => ReturnOrderStatus::APPROVED]);
    ReturnOrder::factory()->count(3)->create(['status' => ReturnOrderStatus::COMPLETED, 'refund_amount' => 100.00]);
    ReturnOrder::factory()->count(1)->create(['status' => ReturnOrderStatus::REJECTED]);

    $stats = $this->returnOrderService->getReturnStatistics();

    expect($stats['pending_requests'])->toBe(2);
    expect($stats['approved_returns'])->toBe(1);
    expect($stats['completed_returns'])->toBe(3);
    expect($stats['rejected_returns'])->toBe(1);
    expect($stats['total_refund_amount'])->toBe(300.00);
});
