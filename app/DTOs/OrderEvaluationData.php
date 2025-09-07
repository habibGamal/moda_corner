<?php

namespace App\DTOs;

use App\Models\Address;
use App\Models\Promotion;
use App\Models\ShippingCost;

class OrderEvaluationData
{
    /**
     * @param Address $address The shipping address
     * @param float $subtotal The order subtotal
     * @param ShippingCost $shippingCost The shipping cost object
     * @param float $finalShippingCost The final shipping cost after any discounts
     * @param float $discount The applied discount amount
     * @param float $total The final order total
     * @param bool $shippingDiscount Whether shipping is discounted
     * @param Promotion|null $appliedPromotion The applied promotion, if any
     */
    public function __construct(
        public readonly Address $address,
        public readonly float $subtotal,
        public readonly ShippingCost $shippingCost,
        public readonly float $finalShippingCost,
        public readonly float $discount,
        public readonly float $total,
        public readonly bool $shippingDiscount = false,
        public readonly ?Promotion $appliedPromotion = null
    ) {
    }
}
