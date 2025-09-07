<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Enums\PromotionType;
use App\Enums\PromotionConditionType;
use App\Enums\PromotionRewardType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PromotionService
{
    /**
     * Validate and apply a promotion code to a cart
     *
     * @param string $code The promotion code to validate
     * @return array|null [discount amount, promotion] if valid, null if invalid
     */
    public function validatePromotionCode(string $code): ?array
    {
        $promotion = Promotion::where('code', $code)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->with(['conditions', 'rewards'])
            ->first();
        if (!$promotion) {
            return null;
        }

        // Check usage limit
        if ($promotion->usage_limit !== null && $promotion->usage_count >= $promotion->usage_limit) {
            return null;
        }

        // Get current user's cart
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $cart = $user->cart;
        if (!$cart || $cart->items->isEmpty()) {
            return null;
        }

        // Calculate discount amount
        $discountAmount = $this->calculateDiscountAmount($promotion, $cart);

        // Handle free shipping promotions specially
        if ($promotion->type === PromotionType::FREE_SHIPPING) {
            // For free shipping promotions, we don't return a monetary discount
            // The shipping discount will be handled separately
            return [0, $promotion];
        }

        if ($discountAmount <= 0) {
            return null;
        }


        return [$discountAmount, $promotion];
    }

    /**
     * Calculate the discount amount for a promotion applied to a cart
     *
     * @param Promotion $promotion
     * @param Cart $cart
     * @return float
     */
    public function calculateDiscountAmount(Promotion $promotion, Cart $cart): float
    {
        $cartService = app(CartService::class);
        $cartSummary = $cartService->getCartSummary();
        $subtotal = $cartSummary->totalPrice;
        // Check minimum order value
        if ($promotion->min_order_value !== null && $subtotal < $promotion->min_order_value) {
            return 0;
        }

        // Check if conditions are met
        if (!$this->checkPromotionConditions($promotion, $cart)) {
            return 0;
        }
        switch ($promotion->type) {
            case PromotionType::PERCENTAGE:
                return $subtotal * ($promotion->value / 100);

            case PromotionType::FIXED:
                return min($promotion->value, $subtotal);

            case PromotionType::FREE_SHIPPING:
                // Shipping cost will be handled separately in OrderService
                return 0;

            case PromotionType::BUY_X_GET_Y:
                return $this->calculateBuyXGetYDiscount($promotion, $cart);

            default:
                return 0;
        }
    }

    /**
     * Check if the promotion conditions are met
     *
     * @param Promotion $promotion
     * @param Cart $cart
     * @return bool
     */
    protected function checkPromotionConditions(Promotion $promotion, Cart $cart): bool
    {
        if ($promotion->conditions->isEmpty()) {
            return true;
        }
        // Group conditions by type
        $conditionsByType = $promotion->conditions->groupBy('type');

        foreach ($conditionsByType as $type => $conditions) {
            $conditionMet = false;
            switch ($type) {
                case PromotionConditionType::PRODUCT->value:
                    $conditionMet = $this->checkProductConditions($conditions, $cart);
                    break;

                case PromotionConditionType::CATEGORY->value:
                    $conditionMet = $this->checkCategoryConditions($conditions, $cart);
                    break;

                case PromotionConditionType::BRAND->value:
                    $conditionMet = $this->checkBrandConditions($conditions, $cart);
                    break;

                case PromotionConditionType::CUSTOMER->value:
                    $conditionMet = $this->checkCustomerConditions($conditions);
                    break;
            }


            if (!$conditionMet) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if product conditions are met
     *
     * @param Collection $conditions
     * @param Cart $cart
     * @return bool
     */
    protected function checkProductConditions(Collection $conditions, Cart $cart): bool
    {
        $productIds = $conditions->pluck('entity_id')->toArray();
        $cartItemsByProduct = $cart->items->groupBy('product_id');

        foreach ($conditions as $condition) {
            if (!isset($cartItemsByProduct[$condition->entity_id])) {
                return false;
            }

            $totalQuantity = $cartItemsByProduct[$condition->entity_id]->sum('quantity');

            if ($condition->quantity && $totalQuantity < $condition->quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if category conditions are met
     *
     * @param Collection $conditions
     * @param Cart $cart
     * @return bool
     */
    protected function checkCategoryConditions(Collection $conditions, Cart $cart): bool
    {
        $categoryIds = $conditions->pluck('entity_id')->toArray();
        $cartProducts = $cart->items->pluck('product');
        $cartProductsByCategoryId = $cartProducts->groupBy('category_id');

        foreach ($conditions as $condition) {
            if (!isset($cartProductsByCategoryId[$condition->entity_id])) {
                return false;
            }

            if ($condition->quantity) {
                $totalQuantity = 0;
                foreach ($cart->items as $item) {
                    if ($item->product->category_id == $condition->entity_id) {
                        $totalQuantity += $item->quantity;
                    }
                }

                if ($totalQuantity < $condition->quantity) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if brand conditions are met
     *
     * @param Collection $conditions
     * @param Cart $cart
     * @return bool
     */
    protected function checkBrandConditions(Collection $conditions, Cart $cart): bool
    {
        $brandIds = $conditions->pluck('entity_id')->toArray();
        $cartProducts = $cart->items->pluck('product');
        $cartProductsByBrandId = $cartProducts->groupBy('brand_id');

        foreach ($conditions as $condition) {
            if (!isset($cartProductsByBrandId[$condition->entity_id])) {
                return false;
            }

            if ($condition->quantity) {
                $totalQuantity = 0;
                foreach ($cart->items as $item) {
                    if ($item->product->brand_id == $condition->entity_id) {
                        $totalQuantity += $item->quantity;
                    }
                }

                if ($totalQuantity < $condition->quantity) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if customer conditions are met
     *
     * @param Collection $conditions
     * @return bool
     */
    protected function checkCustomerConditions(Collection $conditions): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        foreach ($conditions as $condition) {
            if ($condition->entity_id && $condition->entity_id != $user->id) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate the discount for Buy X Get Y promotions
     *
     * @param Promotion $promotion
     * @param Cart $cart
     * @return float
     */
    protected function calculateBuyXGetYDiscount(Promotion $promotion, Cart $cart): float
    {
        if ($promotion->rewards->isEmpty()) {
            return 0;
        }

        $totalDiscount = 0;

        foreach ($promotion->rewards as $reward) {
            switch ($reward->type) {
                case PromotionRewardType::PRODUCT:
                    $totalDiscount += $this->calculateProductRewardDiscount($reward, $cart);
                    break;

                case PromotionRewardType::CATEGORY:
                    $totalDiscount += $this->calculateCategoryRewardDiscount($reward, $cart);
                    break;

                case PromotionRewardType::BRAND:
                    $totalDiscount += $this->calculateBrandRewardDiscount($reward, $cart);
                    break;
            }
        }

        return $totalDiscount;
    }

    /**
     * Calculate discount for a product reward
     *
     * @param object $reward
     * @param Cart $cart
     * @return float
     */
    protected function calculateProductRewardDiscount($reward, Cart $cart): float
    {
        $productItems = $cart->items->where('product_id', $reward->entity_id);
        if ($productItems->isEmpty()) {
            return 0;
        }

        $totalDiscount = 0;
        $discountPercentage = $reward->discount_percentage ?? 100; // Default to 100% (free)

        foreach ($productItems as $item) {
            $productPrice = $item->product->sale_price ?? $item->product->price;
            if ($item->variant && $item->variant->price) {
                $productPrice = $item->variant->sale_price ?? $item->variant->price;
            }

            // Apply discount to the specified quantity or all items if quantity is null
            $quantityToDiscount = min($reward->quantity ?? $item->quantity, $item->quantity);
            $totalDiscount += $productPrice * $quantityToDiscount * ($discountPercentage / 100);
        }

        return $totalDiscount;
    }

    /**
     * Calculate discount for a category reward
     *
     * @param object $reward
     * @param Cart $cart
     * @return float
     */
    protected function calculateCategoryRewardDiscount($reward, Cart $cart): float
    {
        $categoryItems = $cart->items->filter(function ($item) use ($reward) {
            return $item->product->category_id == $reward->entity_id;
        });

        if ($categoryItems->isEmpty()) {
            return 0;
        }

        $totalDiscount = 0;
        $discountPercentage = $reward->discount_percentage ?? 100; // Default to 100% (free)
        $remainingQuantity = $reward->quantity ?? PHP_INT_MAX;

        // Sort items by price (ascending) to apply discount to cheapest items first
        $sortedItems = $categoryItems->sortBy(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            if ($item->variant && $item->variant->price) {
                $price = $item->variant->sale_price ?? $item->variant->price;
            }
            return $price;
        });

        foreach ($sortedItems as $item) {
            if ($remainingQuantity <= 0)
                break;

            $productPrice = $item->product->sale_price ?? $item->product->price;
            if ($item->variant && $item->variant->price) {
                $productPrice = $item->variant->sale_price ?? $item->variant->price;
            }

            $quantityToDiscount = min($remainingQuantity, $item->quantity);
            $totalDiscount += $productPrice * $quantityToDiscount * ($discountPercentage / 100);
            $remainingQuantity -= $quantityToDiscount;
        }

        return $totalDiscount;
    }

    /**
     * Calculate discount for a brand reward
     *
     * @param object $reward
     * @param Cart $cart
     * @return float
     */
    protected function calculateBrandRewardDiscount($reward, Cart $cart): float
    {
        $brandItems = $cart->items->filter(function ($item) use ($reward) {
            return $item->product->brand_id == $reward->entity_id;
        });

        if ($brandItems->isEmpty()) {
            return 0;
        }

        $totalDiscount = 0;
        $discountPercentage = $reward->discount_percentage ?? 100; // Default to 100% (free)
        $remainingQuantity = $reward->quantity ?? PHP_INT_MAX;

        // Sort items by price (ascending) to apply discount to cheapest items first
        $sortedItems = $brandItems->sortBy(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            if ($item->variant && $item->variant->price) {
                $price = $item->variant->sale_price ?? $item->variant->price;
            }
            return $price;
        });

        foreach ($sortedItems as $item) {
            if ($remainingQuantity <= 0)
                break;

            $productPrice = $item->product->sale_price ?? $item->product->price;
            if ($item->variant && $item->variant->price) {
                $productPrice = $item->variant->sale_price ?? $item->variant->price;
            }

            $quantityToDiscount = min($remainingQuantity, $item->quantity);
            $totalDiscount += $productPrice * $quantityToDiscount * ($discountPercentage / 100);
            $remainingQuantity -= $quantityToDiscount;
        }

        return $totalDiscount;
    }

    /**
     * Record a promotion usage for an order
     *
     * @param Order $order
     * @param Promotion $promotion
     * @param float $discountAmount
     * @return PromotionUsage
     */
    public function recordPromotionUsage(Order $order, Promotion $promotion, float $discountAmount): PromotionUsage
    {
        // Increment usage count
        $promotion->increment('usage_count');

        // Record the usage
        return PromotionUsage::create([
            'promotion_id' => $promotion->id,
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'discount_amount' => $discountAmount,
        ]);
    }

    /**
     * Get eligible promotions for a cart
     *
     * @param Cart $cart
     * @return Collection
     */
    public function getEligiblePromotions(Cart $cart): Collection
    {
        $promotions = Promotion::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->whereNull('code') // Automatic promotions don't have a code
            ->with(['conditions', 'rewards'])
            ->get();

        return $promotions->filter(function ($promotion) use ($cart) {
            $discountAmount = $this->calculateDiscountAmount($promotion, $cart);
            return $discountAmount > 0;
        });
    }

    /**
     * Apply the best automatic promotion to a cart
     *
     * @param Cart $cart
     * @return array|null [discount amount, promotion] if applied, null if none applicable
     */
    public function applyBestAutomaticPromotion(Cart $cart): ?array
    {
        $eligiblePromotions = $this->getEligiblePromotions($cart);

        if ($eligiblePromotions->isEmpty()) {
            return null;
        }

        // Find promotion with highest discount
        $bestPromotion = null;
        $highestDiscount = 0;

        foreach ($eligiblePromotions as $promotion) {
            $discountAmount = $this->calculateDiscountAmount($promotion, $cart);
            if ($discountAmount > $highestDiscount) {
                $highestDiscount = $discountAmount;
                $bestPromotion = $promotion;
            }
        }

        if ($bestPromotion && $highestDiscount > 0) {
            return [$highestDiscount, $bestPromotion];
        }

        return null;
    }
}
