<?php

use App\DTOs\OrderEvaluationData;
use App\Enums\PromotionType;
use App\Models\Address;
use App\Models\Area;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Gov;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\ShippingCost;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderEvaluationService;
use App\Services\PromotionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->orderEvaluationService = app(OrderEvaluationService::class);
    $this->cartService = app(CartService::class);
    $this->promotionService = app(PromotionService::class);
    Auth::login($this->user);
});

describe('evaluateOrderCalculation', function () {
    it('successfully calculates order total without promotions', function () {
        // Setup: Create necessary data
        $product = Product::factory()->create([
            'price' => 120.0,  // This is what Cart->getTotalPrice() uses
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => null,  // Use product price
            'sale_price' => null
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 15.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation($address->id);

        expect($result)->toBeInstanceOf(OrderEvaluationData::class);
        expect($result->address->id)->toBe($address->id);
        expect($result->subtotal)->toBe(240.0); // 2 * 120.0
        expect($result->shippingCost->id)->toBe($shippingCost->id);
        expect($result->finalShippingCost)->toBe(15.0);
        expect($result->discount)->toBe(0.0);
        expect($result->total)->toBe(255.0); // 240 + 15
        expect($result->appliedPromotion)->toBeNull();
    });
    it('calculates order total with percentage coupon discount', function () {
        $product = Product::factory()->create([
            'price' => 100.0,
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => null,  // Use product price
            'sale_price' => null
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 20.0
        ]);
        $promotion = Promotion::create([
            'name_en' => 'Save 20%',
            'name_ar' => 'وفر 20%',
            'code' => 'SAVE20',
            'type' => PromotionType::PERCENTAGE,
            'value' => 20.0,
            'is_active' => true,
            'min_order_value' => 50.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            'SAVE20'
        );

        expect($result->subtotal)->toBe(300.0); // 3 * 100.0
        expect($result->discount)->toBe(60.0); // 20% of 300
        expect($result->finalShippingCost)->toBe(20.0);
        expect($result->total)->toBe(260.0); // 300 + 20 - 60
        expect($result->appliedPromotion->id)->toBe($promotion->id);
    });
    it('calculates order total with fixed amount coupon discount', function () {
        $product = Product::factory()->create([
            'price' => 80.0,
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => null,  // Use product price
            'sale_price' => null
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 25.0
        ]);
        $promotion = Promotion::create([
            'name_en' => 'Fixed Discount',
            'name_ar' => 'خصم ثابت',
            'code' => 'FIXED50',
            'type' => PromotionType::FIXED,
            'value' => 50.0,
            'is_active' => true,
            'min_order_value' => 100.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            'FIXED50'
        );

        expect($result->subtotal)->toBe(160.0); // 2 * 80.0
        expect($result->discount)->toBe(50.0);
        expect($result->finalShippingCost)->toBe(25.0);
        expect($result->total)->toBe(135.0); // 160 + 25 - 50
        expect($result->appliedPromotion->id)->toBe($promotion->id);
    });

    it('calculates order total with free shipping promotion', function () {
        $product = Product::factory()->create([
            'price' => 150.0,
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 150.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);
        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 30.0
        ]);
        $promotion = Promotion::factory()->freeShipping()->create([
            'code' => 'FREESHIP',
        ]);
        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            'FREESHIP'
        );
        expect($result->subtotal)->toBe(300.0); // 2 * 150.0
        expect((float) $result->discount)->toBe(0.0); // FREE_SHIPPING type returns 0 discount
        expect((float) $result->finalShippingCost)->toBe(0.0); // Without free_shipping field, this won't be 0
        expect($result->total)->toBe(300.0);
        expect($result->appliedPromotion->id)->toBe($promotion->id);
    });

    it('calculates order total with promotion by ID', function () {
        $product = Product::factory()->create([
            'price' => 200.0,
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 200.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 40.0
        ]);
        $promotion = Promotion::create([
            'name_en' => 'Percentage Discount',
            'name_ar' => 'خصم نسبة مئوية',
            'type' => PromotionType::PERCENTAGE,
            'value' => 15.0,
            'is_active' => true,
            'min_order_value' => 300.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            null,
            $promotion->id
        );

        expect($result->subtotal)->toBe(400.0); // 2 * 200.0
        expect($result->discount)->toBe(60.0); // 15% of 400
        expect($result->finalShippingCost)->toBe(40.0);
        expect($result->total)->toBe(380.0); // 400 + 40 - 60
        expect($result->appliedPromotion->id)->toBe($promotion->id);
    });
    it('applies best automatic promotion when no specific promotion provided', function () {
        $product = Product::factory()->create([
            'price' => 100.0,
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 100.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 20.0
        ]);        // Create multiple promotions - the service should choose the best one
        $promotion1 = Promotion::create([
            'name_en' => 'Promotion 1',
            'name_ar' => 'عرض 1',
            'type' => PromotionType::PERCENTAGE,
            'value' => 10.0,
            'is_active' => true,
            'min_order_value' => 200.0
        ]);

        $promotion2 = Promotion::create([
            'name_en' => 'Promotion 2',
            'name_ar' => 'عرض 2',
            'type' => PromotionType::PERCENTAGE,
            'value' => 15.0,
            'is_active' => true,
            'min_order_value' => 250.0
        ]);        // Add items to cart (subtotal will be 300)
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation($address->id);

        expect($result->subtotal)->toBe(300.0); // 3 * 100.0
        expect($result->discount)->toBe(45.0); // Should apply the better 15% discount
        expect($result->finalShippingCost)->toBe(20.0);
        expect($result->total)->toBe(275.0); // 300 + 20 - 45
        expect($result->appliedPromotion->id)->toBe($promotion2->id);
    });
    it('prioritizes coupon code over promotion ID', function () {
        $product = Product::factory()->create([
            'price' => 100.0,
            'sale_price' => null
        ]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 100.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 15.0
        ]);
        $couponPromotion = Promotion::create([
            'name_en' => 'Coupon Promotion',
            'name_ar' => 'عرض كوبون',
            'code' => 'COUPON25',
            'type' => PromotionType::PERCENTAGE,
            'value' => 25.0,
            'is_active' => true,
            'min_order_value' => 100.0
        ]);

        $idPromotion = Promotion::create([
            'name_en' => 'ID Promotion',
            'name_ar' => 'عرض برقم',
            'type' => PromotionType::PERCENTAGE,
            'value' => 30.0,
            'is_active' => true,
            'min_order_value' => 100.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            'COUPON25',
            $idPromotion->id
        );

        expect($result->subtotal)->toBe(200.0);
        expect($result->discount)->toBe(50.0); // Should apply coupon (25%) not ID promotion (30%)
        expect($result->appliedPromotion->id)->toBe($couponPromotion->id);
    });

    it('throws exception when user is not authenticated', function () {
        Auth::logout();

        $address = Address::factory()->create();

        expect(function () use ($address) {
            $this->orderEvaluationService->evaluateOrderCalculation($address->id);
        })->toThrow(Exception::class, 'User not authenticated');
    });

    it('throws exception when cart is empty', function () {
        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        expect(function () use ($address) {
            $this->orderEvaluationService->evaluateOrderCalculation($address->id);
        })->toThrow(Exception::class, 'Cart is empty');
    });
    it('throws exception when address does not exist', function () {
        $product = Product::factory()->create(['price' => 100.0]);
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        expect(function () {
            $this->orderEvaluationService->evaluateOrderCalculation(999); // Non-existent address
        })->toThrow(ModelNotFoundException::class);
    });

    it('throws exception when address belongs to different user', function () {
        $otherUser = User::factory()->create();
        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $otherUser->id,
            'area_id' => $area->id
        ]);
        $product = Product::factory()->create(['price' => 100.0]);
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        expect(function () use ($address) {
            $this->orderEvaluationService->evaluateOrderCalculation($address->id);
        })->toThrow(ModelNotFoundException::class);
    });

    it('throws exception when no shipping cost is defined for area', function () {
        $product = Product::factory()->create(['price' => 100.0]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 100.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        // Note: Not creating ShippingCost for this area        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1
        ]);

        expect(function () use ($address) {
            $this->orderEvaluationService->evaluateOrderCalculation($address->id);
        })->toThrow(Exception::class, 'No shipping cost defined for this area');
    });

    it('handles invalid coupon code gracefully', function () {
        $product = Product::factory()->create(['price' => 100.0]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 100.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 15.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            'INVALID_COUPON'
        );

        // Should calculate normally without any discount when coupon is invalid
        expect($result->subtotal)->toBe(200.0);
        expect($result->discount)->toBe(0.0);
        expect($result->finalShippingCost)->toBe(15.0);
        expect($result->total)->toBe(215.0);
        expect($result->shippingDiscount)->toBeFalse();
        expect($result->appliedPromotion)->toBeNull();
    });

    it('handles inactive promotion by ID gracefully', function () {
        $product = Product::factory()->create(['price' => 100.0]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 100.0
        ]);

        $gov = Gov::factory()->create();
        $area = Area::factory()->create(['gov_id' => $gov->id]);
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'area_id' => $area->id
        ]);

        $shippingCost = ShippingCost::factory()->create([
            'area_id' => $area->id,
            'value' => 15.0
        ]);
        $inactivePromotion = Promotion::create([
            'name_en' => 'Inactive Promotion',
            'name_ar' => 'عرض غير نشط',
            'type' => PromotionType::PERCENTAGE,
            'value' => 20.0,
            'is_active' => false, // Inactive promotion
            'min_order_value' => 100.0
        ]);        // Add items to cart
        $cart = $this->cartService->getOrCreateCart();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2
        ]);

        $result = $this->orderEvaluationService->evaluateOrderCalculation(
            $address->id,
            null,
            $inactivePromotion->id
        );

        // Should calculate normally without any discount when promotion is inactive
        expect($result->subtotal)->toBe(200.0);
        expect($result->discount)->toBe(0.0);
        expect($result->finalShippingCost)->toBe(15.0);
        expect($result->total)->toBe(215.0);
        expect($result->shippingDiscount)->toBeFalse();
        expect($result->appliedPromotion)->toBeNull();
    });
});
