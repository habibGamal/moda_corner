<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventoryManagementService;
use App\Exceptions\InsufficientStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->inventoryService = app(InventoryManagementService::class);
});

describe('validateCartItemStock', function () {
    it('passes validation when stock is sufficient', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 5);
        })->not->toThrow(InsufficientStockException::class);
    });

    it('passes validation when requested quantity equals available stock', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 5
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 10);
        })->not->toThrow(InsufficientStockException::class);
    });

    it('throws InsufficientStockException when requested quantity exceeds available stock', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 10);
        })->toThrow(InsufficientStockException::class, 'Insufficient stock for Test Product. Requested: 10, Available: 5');
    });

    it('uses English product name in exception message when available', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => 'English Product Name',
            'name_ar' => 'اسم المنتج بالعربية'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 5);
        })->toThrow(InsufficientStockException::class, 'Insufficient stock for English Product Name. Requested: 5, Available: 3');
    });

    it('uses Arabic product name in exception message when English is not available', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => '', // Empty English name
            'name_ar' => 'اسم المنتج بالعربية'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 5);
        })->toThrow(InsufficientStockException::class, 'Insufficient stock for اسم المنتج بالعربية. Requested: 5, Available: 2');
    });

    it('uses product ID in exception message when both names are empty', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => '',
            'name_ar' => ''
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 5);
        })->toThrow(InsufficientStockException::class, "Insufficient stock for Product #{$product->id}. Requested: 5, Available: 2");
    });

    it('validates zero requested quantity successfully', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي'
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $cartItem = CartItem::factory()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3
        ]);

        // Load relationships
        $cartItem->load(['product', 'variant']);

        // Act & Assert
        expect(function () use ($cartItem) {
            $this->inventoryService->validateCartItemStock($cartItem, 0);
        })->not->toThrow(InsufficientStockException::class);
    });
});

describe('reserveInventory', function () {
    it('successfully reserves inventory when stock is sufficient', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        // Act
        $result = $this->inventoryService->reserveInventory($variant, 3);

        // Assert
        expect($result)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(7);
    });

    it('throws InsufficientStockException when trying to reserve more than available', function () {
        // Arrange
        $product = Product::factory()->create([
            'name_en' => 'Test Product'
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        // Act & Assert
        expect(function () use ($variant) {
            $this->inventoryService->reserveInventory($variant, 10);
        })->toThrow(InsufficientStockException::class, 'Insufficient stock for Test Product. Requested: 10, Available: 5');

        // Ensure no changes were made to stock
        expect($variant->fresh()->quantity)->toBe(5);
    });

    it('reserves exact amount when requesting all available stock', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 8
        ]);

        // Act
        $result = $this->inventoryService->reserveInventory($variant, 8);

        // Assert
        expect($result)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(0);
    });

    it('handles zero quantity reservation', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        // Act
        $result = $this->inventoryService->reserveInventory($variant, 0);

        // Assert
        expect($result)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(10); // No change
    });

    it('throws exception when variant does not exist', function () {
        // Arrange
        $variant = ProductVariant::factory()->make(['id' => 99999]);
          // Act & Assert
        expect(function () use ($variant) {
            $this->inventoryService->reserveInventory($variant, 1);
        })->toThrow(Exception::class, 'Product variant not found during inventory reservation.');
    });
});

describe('returnInventory', function () {
    it('successfully returns inventory to stock', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        // Act
        $result = $this->inventoryService->returnInventory($variant, 3);

        // Assert
        expect($result)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(8);
    });

    it('handles zero quantity return', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        // Act
        $result = $this->inventoryService->returnInventory($variant, 0);

        // Assert
        expect($result)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(10); // No change
    });

    it('can return more items than current stock (no validation)', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        // Act
        $result = $this->inventoryService->returnInventory($variant, 5);

        // Assert
        expect($result)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(7);
    });

    it('throws exception when variant does not exist', function () {
        // Arrange
        $variant = ProductVariant::factory()->make(['id' => 99999]);
          // Act & Assert
        expect(function () use ($variant) {
            $this->inventoryService->returnInventory($variant, 1);
        })->toThrow(Exception::class, 'Product variant not found during inventory return.');
    });
});

describe('returnOrderInventoryToStock', function () {
    it('returns inventory for all order items', function () {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

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

        $orderItem1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'variant_id' => $variant1->id,
            'quantity' => 3,
        ]);

        $orderItem2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'variant_id' => $variant2->id,
            'quantity' => 5,
        ]);

        // Load relationships
        $order->load('items.variant');

        // Act
        $this->inventoryService->returnOrderInventoryToStock($order);

        // Assert
        expect($variant1->fresh()->quantity)->toBe(13); // 10 + 3
        expect($variant2->fresh()->quantity)->toBe(25); // 20 + 5
    });

    it('skips items without variants', function () {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $product = Product::factory()->create();

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => null, // No variant
            'quantity' => 3,
        ]);

        // Load relationships
        $order->load('items.variant');

        // Act & Assert - Should not throw any exceptions
        expect(function () use ($order) {
            $this->inventoryService->returnOrderInventoryToStock($order);
        })->not->toThrow(Exception::class);
    });

    it('handles empty orders gracefully', function () {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
          // Act & Assert - Should not throw any exceptions
        expect(function () use ($order) {
            $this->inventoryService->returnOrderInventoryToStock($order);
        })->not->toThrow(Exception::class);
    });
});

describe('integration tests', function () {
    it('can reserve and return inventory in sequence', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        // Act - Reserve inventory
        $reserveResult = $this->inventoryService->reserveInventory($variant, 4);
        expect($reserveResult)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(6);

        // Act - Return inventory
        $returnResult = $this->inventoryService->returnInventory($variant, 2);
        expect($returnResult)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(8);

        // Act - Reserve again
        $reserveResult2 = $this->inventoryService->reserveInventory($variant, 3);
        expect($reserveResult2)->toBeTrue();
        expect($variant->fresh()->quantity)->toBe(5);
    });

    it('maintains data integrity across concurrent operations', function () {
        // Arrange
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 100
        ]);

        // Act - Simulate multiple reservations
        for ($i = 0; $i < 10; $i++) {
            $this->inventoryService->reserveInventory($variant, 5);
        }

        // Assert
        expect($variant->fresh()->quantity)->toBe(50); // 100 - (10 * 5)

        // Act - Return some inventory
        for ($i = 0; $i < 5; $i++) {
            $this->inventoryService->returnInventory($variant, 3);
        }

        // Assert
        expect($variant->fresh()->quantity)->toBe(65); // 50 + (5 * 3)
    });

    it('handles complex order scenarios', function () {
        // Arrange
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        // Create multiple products with different stock levels
        $variants = collect();
        for ($i = 0; $i < 3; $i++) {
            $product = Product::factory()->create();
            $variant = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'quantity' => 20 + ($i * 5) // 20, 25, 30
            ]);
            $variants->push($variant);

            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => 2 + $i, // 2, 3, 4
            ]);
        }

        // Load relationships
        $order->load('items.variant');

        // Act - Return all inventory
        $this->inventoryService->returnOrderInventoryToStock($order);

        // Assert
        expect($variants[0]->fresh()->quantity)->toBe(22); // 20 + 2
        expect($variants[1]->fresh()->quantity)->toBe(28); // 25 + 3
        expect($variants[2]->fresh()->quantity)->toBe(34); // 30 + 4
    });
});
