<?php

use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionCondition;
use App\Models\PromotionReward;
use App\Models\PromotionUsage;
use App\Models\User;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->promotionService = app(PromotionService::class);
    Auth::login($this->user);
});

describe('validatePromotionCode', function () {
    it('returns null for non-existent promotion code', function () {
        $result = $this->promotionService->validatePromotionCode('INVALID');

        expect($result)->toBeNull();
    });

    it('returns null for inactive promotion', function () {
        $promotion = Promotion::factory()->inactive()->create(['code' => 'INACTIVE']);

        $result = $this->promotionService->validatePromotionCode('INACTIVE');

        expect($result)->toBeNull();
    });

    it('returns null for expired promotion', function () {
        $promotion = Promotion::factory()->expired()->create(['code' => 'EXPIRED']);

        $result = $this->promotionService->validatePromotionCode('EXPIRED');

        expect($result)->toBeNull();
    });

    it('returns null for promotion that has not started', function () {
        $promotion = Promotion::factory()->notStarted()->create(['code' => 'FUTURE']);

        $result = $this->promotionService->validatePromotionCode('FUTURE');

        expect($result)->toBeNull();
    });

    it('returns null for promotion that has reached usage limit', function () {
        $promotion = Promotion::factory()->usageLimitReached()->create(['code' => 'MAXED']);

        $result = $this->promotionService->validatePromotionCode('MAXED');

        expect($result)->toBeNull();
    });

    it('returns null when user has no cart', function () {
        $promotion = Promotion::factory()->percentage(20)->create(['code' => 'VALID']);

        $result = $this->promotionService->validatePromotionCode('VALID');

        expect($result)->toBeNull();
    });

    it('returns null when cart is empty', function () {
        $promotion = Promotion::factory()->percentage(20)->create(['code' => 'VALID']);
        Cart::factory()->create(['user_id' => $this->user->id]);

        $result = $this->promotionService->validatePromotionCode('VALID');

        expect($result)->toBeNull();
    });

    it('returns discount amount and promotion for valid percentage promotion', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create(['code' => 'VALID20']);

        $result = $this->promotionService->validatePromotionCode('VALID20');

        expect($result)->toBeArray();
        expect($result[0])->toBe(40.0); // 20% of 200
        expect($result[1])->toBeInstanceOf(Promotion::class);
        expect($result[1]->id)->toBe($promotion->id);
    });

    it('returns zero discount amount for free shipping promotion', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->freeShipping()->create(['code' => 'FREESHIP']);

        $result = $this->promotionService->validatePromotionCode('FREESHIP');

        expect($result)->toBeArray();
        expect($result[0])->toBe(0);

        expect($result[1])->toBeInstanceOf(Promotion::class);
    });

    it('returns null when minimum order value is not met', function () {
        $product = Product::factory()->create(['price' => 50, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create([
            'code' => 'MIN100',
            'min_order_value' => 100,
        ]);

        $result = $this->promotionService->validatePromotionCode('MIN100');

        expect($result)->toBeNull();
    });
});

describe('calculateDiscountAmount', function () {
    it('returns zero when minimum order value is not met', function () {
        $product = Product::factory()->create(['price' => 50, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create(['min_order_value' => 100]);

        $discount = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($discount)->toBe(0.0);
    });

    it('calculates percentage discount correctly', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(25)->create();

        $discount = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($discount)->toBe(50.0); // 25% of 200
    });

    it('calculates fixed discount correctly', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->fixed(30)->create();

        $discount = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($discount)->toBe(30.0);
    });

    it('limits fixed discount to subtotal amount', function () {
        $product = Product::factory()->create(['price' => 50, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->fixed(100)->create(); // Discount more than cart value

        $discount = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($discount)->toBe(50.0); // Limited to cart total
    });

    it('returns zero for free shipping promotion', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->freeShipping()->create();

        $discount = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($discount)->toBe(0.0);
    });
});

describe('checkPromotionConditions', function () {
    it('returns true when promotion has no conditions', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBeGreaterThan(0);
    });

    it('returns false when product condition is not met', function () {
        $product1 = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $product2 = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->product($product2->id)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(0.0);
    });

    it('returns true when product condition is met', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->product($product->id, 1)->create(['promotion_id' => $promotion->id]);
        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(40.0); // 20% of 200
    });

    it('returns false when product quantity condition is not met', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->product($product->id, 3)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(0.0);
    });

    it('returns true when category condition is met', function () {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null, 'category_id' => $category->id]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->category($category->id, 1)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(40.0); // 20% of 200
    });

    it('returns true when brand condition is met', function () {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null, 'brand_id' => $brand->id]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->brand($brand->id, 1)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(40.0); // 20% of 200
    });

    it('returns true when customer condition is met', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->customer($this->user->id)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(40.0); // 20% of 200
    });

    it('returns false when customer condition is not met', function () {
        $otherUser = User::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->percentage(20)->create();
        PromotionCondition::factory()->customer($otherUser->id)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(0.0);
    });
});

describe('calculateBuyXGetYDiscount', function () {
    it('calculates product reward discount correctly', function () {
        $product1 = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $product2 = Product::factory()->create(['price' => 50, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $promotion = Promotion::factory()->buyXGetY()->create();
        PromotionCondition::factory()->product($product1->id, 2)->create(['promotion_id' => $promotion->id]);
        PromotionReward::factory()->product($product2->id, 1, 100)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(50.0); // One free product2 (100% discount)
    });

    it('calculates category reward discount correctly', function () {
        $category = Category::factory()->create();
        $product1 = Product::factory()->create(['price' => 100, 'sale_price' => null, 'category_id' => $category->id]);
        $product2 = Product::factory()->create(['price' => 50, 'sale_price' => null, 'category_id' => $category->id]);
        $product3 = Product::factory()->create(['price' => 30, 'sale_price' => null, 'category_id' => $category->id]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product3->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->buyXGetY()->create();
        PromotionCondition::factory()->category($category->id, 3)->create(['promotion_id' => $promotion->id]);
        PromotionReward::factory()->category($category->id, 1, 100)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(30.0); // Cheapest product gets 100% discount
    });

    it('calculates brand reward discount correctly', function () {
        $brand = Brand::factory()->create();
        $product1 = Product::factory()->create(['price' => 100, 'sale_price' => null, 'brand_id' => $brand->id]);
        $product2 = Product::factory()->create(['price' => 60, 'sale_price' => null, 'brand_id' => $brand->id]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $promotion = Promotion::factory()->buyXGetY()->create();
        PromotionCondition::factory()->brand($brand->id, 2)->create(['promotion_id' => $promotion->id]);
        PromotionReward::factory()->brand($brand->id, 1, 50)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(30.0); // 50% discount on cheapest product (60 * 0.5 = 30)
    });

    it('handles variant pricing in reward calculations', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 80,
            'sale_price' => 70,
        ]);

        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $promotion = Promotion::factory()->buyXGetY()->create();
        PromotionCondition::factory()->product($product->id, 1)->create(['promotion_id' => $promotion->id]);
        PromotionReward::factory()->product($product->id, 1, 100)->create(['promotion_id' => $promotion->id]);

        $result = $this->promotionService->calculateDiscountAmount($promotion, $cart);

        expect($result)->toBe(70.0); // Free variant at sale price
    });
});

describe('recordPromotionUsage', function () {
    it('records promotion usage and increments usage count', function () {
        $promotion = Promotion::factory()->create(['usage_count' => 5]);
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $discountAmount = 25.0;

        $usage = $this->promotionService->recordPromotionUsage($order, $promotion, $discountAmount);

        expect($usage)->toBeInstanceOf(PromotionUsage::class);
        expect($usage->promotion_id)->toBe($promotion->id);
        expect($usage->order_id)->toBe($order->id);
        expect($usage->user_id)->toBe($this->user->id);
        expect($usage->discount_amount)->toBe($discountAmount);

        // Check that usage count was incremented
        expect($promotion->fresh()->usage_count)->toBe(6);
    });
});

describe('getEligiblePromotions', function () {
    it('returns only automatic promotions without codes', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $automaticPromotion = Promotion::factory()->automatic()->percentage(20)->create();
        $codePromotion = Promotion::factory()->percentage(30)->create(['code' => 'CODE30']);

        $eligiblePromotions = $this->promotionService->getEligiblePromotions($cart);

        expect($eligiblePromotions)->toHaveCount(1);
        expect($eligiblePromotions->first()->id)->toBe($automaticPromotion->id);
    });

    it('filters out promotions with zero discount', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $goodPromotion = Promotion::factory()->automatic()->percentage(20)->create();
        $badPromotion = Promotion::factory()->automatic()->percentage(20)->create(['min_order_value' => 200]);

        $eligiblePromotions = $this->promotionService->getEligiblePromotions($cart);

        expect($eligiblePromotions)->toHaveCount(1);
        expect($eligiblePromotions->first()->id)->toBe($goodPromotion->id);
    });

    it('excludes inactive promotions', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $activePromotion = Promotion::factory()->automatic()->percentage(20)->create();
        $inactivePromotion = Promotion::factory()->automatic()->inactive()->percentage(30)->create();

        $eligiblePromotions = $this->promotionService->getEligiblePromotions($cart);

        expect($eligiblePromotions)->toHaveCount(1);
        expect($eligiblePromotions->first()->id)->toBe($activePromotion->id);
    });

    it('excludes expired promotions', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $validPromotion = Promotion::factory()->automatic()->percentage(20)->create();
        $expiredPromotion = Promotion::factory()->automatic()->expired()->percentage(30)->create();

        $eligiblePromotions = $this->promotionService->getEligiblePromotions($cart);

        expect($eligiblePromotions)->toHaveCount(1);
        expect($eligiblePromotions->first()->id)->toBe($validPromotion->id);
    });
});

describe('applyBestAutomaticPromotion', function () {
    it('returns null when no eligible promotions exist', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Create promotion with high minimum order value
        Promotion::factory()->automatic()->percentage(20)->create(['min_order_value' => 500]);

        $result = $this->promotionService->applyBestAutomaticPromotion($cart);

        expect($result)->toBeNull();
    });

    it('returns the promotion with highest discount', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $lowPromotion = Promotion::factory()->automatic()->percentage(10)->create();
        $highPromotion = Promotion::factory()->automatic()->percentage(25)->create();
        $mediumPromotion = Promotion::factory()->automatic()->fixed(30)->create();

        $result = $this->promotionService->applyBestAutomaticPromotion($cart);

        expect($result)->toBeArray();
        expect($result[0])->toBe(50.0); // 25% of 200
        expect($result[1]->id)->toBe($highPromotion->id);
    });

    it('handles empty cart gracefully', function () {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);

        $result = $this->promotionService->applyBestAutomaticPromotion($cart);

        expect($result)->toBeNull();
    });

    it('compares fixed and percentage discounts correctly', function () {
        $product = Product::factory()->create(['price' => 100, 'sale_price' => null]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3, // Total: 300
        ]);

        $percentagePromotion = Promotion::factory()->automatic()->percentage(20)->create(); // 60 discount
        $fixedPromotion = Promotion::factory()->automatic()->fixed(80)->create(); // 80 discount

        $result = $this->promotionService->applyBestAutomaticPromotion($cart);

        expect($result)->toBeArray();
        expect($result[0])->toBe(80.0);
        expect($result[1]->id)->toBe($fixedPromotion->id);
    });
});
