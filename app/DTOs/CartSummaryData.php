<?php

namespace App\DTOs;

class CartSummaryData
{
    /**
     * @param int $totalItems The total number of items in the cart
     * @param float $totalPrice The total price of all items in the cart
     */
    public function __construct(
        public readonly int $totalItems,
        public readonly float $totalPrice
    ) {
    }
}
