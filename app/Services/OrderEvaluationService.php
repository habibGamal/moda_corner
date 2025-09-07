<?php

namespace App\Services;

use App\DTOs\OrderEvaluationData;
use App\DTOs\CartSummaryData;
use App\Models\Address;
use App\Models\Cart;
use App\Models\ShippingCost;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrderEvaluationService
{
    protected CartService $cartService;
    protected PromotionService $promotionService;
    protected DirectPromotionService $directPromotionService;

    /**
     * Create a new service instance.
     *
     * @param CartService $cartService
     * @param PromotionService $promotionService
     * @param DirectPromotionService $directPromotionService
     */
    public function __construct(
        CartService $cartService,
        PromotionService $promotionService,
        DirectPromotionService $directPromotionService
    ) {
        $this->cartService = $cartService;
        $this->promotionService = $promotionService;
        $this->directPromotionService = $directPromotionService;
    }

    /**
     * Calculate the total for an order based on address and optional coupon
     *
     * @param int $addressId The shipping address ID
     * @param string|null $couponCode Optional coupon code
     * @param int|null $promotionId Optional promotion ID
     * @return OrderEvaluationData Order calculation result with subtotal, shipping, discount and total
     * @throws ModelNotFoundException If the address doesn't exist
     * @throws Exception If there was an error calculating the order
     */
    public function evaluateOrderCalculation(
        int $addressId,
        ?string $couponCode = null,
        ?int $promotionId = null
    ): OrderEvaluationData {
        $user = Auth::user();
        if (!$user) {
            throw new Exception('User not authenticated');
        }

        // Get cart from the service
        $cart = $this->cartService->getCart();
        if (!$cart || $cart->items->isEmpty()) {
            throw new Exception('Cart is empty');
        }

        // Get the shipping address and validate it exists
        $address = Address::where('user_id', $user->id)
            ->findOrFail($addressId);

        // Get shipping cost
        $shippingCost = $this->getShippingCost($address);

        // Get cart totals
        $cartSummary = $this->cartService->getCartSummary();
        $subtotal = $cartSummary->totalPrice;

        // Apply promotions/discounts
        $promotionData = $this->applyPromotions($cart, $couponCode, $promotionId);
        $discount = $promotionData['discount'];
        $appliedPromotion = $promotionData['appliedPromotion'];
        $shippingDiscount = $promotionData['shippingDiscount'];

        // Apply shipping discount if applicable
        $finalShippingCost = $shippingDiscount ? 0 : $shippingCost->value;

        // Check for direct promotion free shipping
        if (!$shippingDiscount && $this->directPromotionService->qualifiesForFreeShipping($subtotal)) {
            $finalShippingCost = 0;
            $shippingDiscount = true;
        }

        // Calculate the final total
        $total = $subtotal + $finalShippingCost - $discount;

        // Return order evaluation data
        return new OrderEvaluationData(
            address: $address,
            subtotal: $subtotal,
            shippingCost: $shippingCost,
            finalShippingCost: $finalShippingCost,
            discount: $discount,
            total: $total,
            shippingDiscount: $shippingDiscount,
            appliedPromotion: $appliedPromotion
        );
    }

    /**
     * Get shipping cost for an address
     *
     * @param Address $address
     * @return ShippingCost
     * @throws Exception If no shipping cost is defined for the area
     */
    protected function getShippingCost(Address $address): ShippingCost
    {
        $shippingCost = ShippingCost::where('area_id', $address->area_id)->first();

        if (!$shippingCost) {
            throw new Exception('No shipping cost defined for this area');
        }

        return $shippingCost;
    }

    /**
     * Apply promotions to the cart
     *
     * @param Cart $cart
     * @param string|null $couponCode
     * @param int|null $promotionId
     * @return array With discount amount, applied promotion, and shipping discount flag
     */
    protected function applyPromotions($cart, ?string $couponCode = null, ?int $promotionId = null): array
    {
        $discount = 0;
        $appliedPromotion = null;
        $shippingDiscount = false;

        // Try to apply promotions in the following order:
        // 1. Specific coupon code if provided
        // 2. Specific promotion ID if provided
        // 3. Best automatic promotion

        // Apply coupon code if provided
        if ($couponCode) {
            $this->applyPromotionCode($couponCode, $discount, $appliedPromotion, $shippingDiscount);
        }
        // Apply specific promotion if provided and no coupon was applied
        elseif ($promotionId && !$appliedPromotion) {
            $this->applyPromotionById($promotionId, $cart, $discount, $appliedPromotion, $shippingDiscount);
        }
        // Apply best automatic promotion if no specific promotion was applied
        elseif (!$appliedPromotion) {
            $this->applyAutomaticPromotion($cart, $discount, $appliedPromotion, $shippingDiscount);
        }

        return [
            'discount' => $discount,
            'appliedPromotion' => $appliedPromotion,
            'shippingDiscount' => $shippingDiscount
        ];
    }

    /**
     * Apply promotion by coupon code
     *
     * @param string $couponCode
     * @param float &$discount
     * @param Promotion|null &$appliedPromotion
     * @param bool &$shippingDiscount
     * @return void
     */
    protected function applyPromotionCode(
        string $couponCode,
        float &$discount,
        ?Promotion &$appliedPromotion,
        bool &$shippingDiscount
    ): void {
        $validationResult = $this->promotionService->validatePromotionCode($couponCode);

        if ($validationResult) {
            list($discountAmount, $promotion) = $validationResult;
            $discount = $discountAmount;
            $appliedPromotion = $promotion;

            // Check if promotion provides free shipping
            if ($promotion->isFreeShipping()) {
                $shippingDiscount = true;
            }
        }
    }

    /**
     * Apply promotion by ID
     *
     * @param int $promotionId
     * @param Cart $cart
     * @param float &$discount
     * @param Promotion|null &$appliedPromotion
     * @param bool &$shippingDiscount
     * @return void
     */
    protected function applyPromotionById(
        int $promotionId,
        Cart $cart,
        float &$discount,
        ?Promotion &$appliedPromotion,
        bool &$shippingDiscount
    ): void {
        $promotion = Promotion::find($promotionId);

        if ($promotion && $promotion->is_active) {
            $discountAmount = $this->promotionService->calculateDiscountAmount($promotion, $cart);

            if ($discountAmount > 0) {
                $discount = $discountAmount;
                $appliedPromotion = $promotion;

                // Check if promotion provides free shipping
                if ($promotion->isFreeShipping()) {
                    $shippingDiscount = true;
                }
            }
        }
    }

    /**
     * Apply the best automatic promotion
     *
     * @param Cart $cart
     * @param float &$discount
     * @param Promotion|null &$appliedPromotion
     * @param bool &$shippingDiscount
     * @return void
     */
    protected function applyAutomaticPromotion(
        Cart $cart,
        float &$discount,
        ?Promotion &$appliedPromotion,
        bool &$shippingDiscount
    ): void {
        $bestPromotion = $this->promotionService->applyBestAutomaticPromotion($cart);

        if ($bestPromotion) {
            list($discountAmount, $promotion) = $bestPromotion;
            $discount = $discountAmount;
            $appliedPromotion = $promotion;

            // Check if promotion provides free shipping
            if ($promotion->isFreeShipping()) {
                $shippingDiscount = true;
            }
        }
    }
}
