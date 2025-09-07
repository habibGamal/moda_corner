<?php

namespace App\DTOs;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

class OrderPlacementData
{
    /**
     * @param int $addressId The shipping address ID
     * @param PaymentMethod $paymentMethod The payment method used
     * @param string|null $couponCode Optional coupon code
     * @param string|null $notes Optional order notes
     * @param int|null $promotionId Optional promotion ID to apply
     */
    public function __construct(
        public readonly int $addressId,
        public readonly PaymentMethod $paymentMethod,
        public readonly ?string $couponCode = null,
        public readonly ?string $notes = null,
        public readonly ?int $promotionId = null,
    ) {
    }

    /**
     * Create from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            addressId: $data['addressId'],
            paymentMethod: is_string($data['paymentMethod']) ? PaymentMethod::from($data['paymentMethod']) : $data['paymentMethod'],
            couponCode: $data['couponCode'] ?? null,
            notes: $data['notes'] ?? null,
            promotionId: $data['promotionId'] ?? null,
        );
    }
}
