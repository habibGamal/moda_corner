<?php

namespace App\DTOs;

class RefundRequestData
{
    public function __construct(
        public readonly string $orderId,
        public readonly float $amount,
        public readonly ?string $reason = null
    ) {}
}
