<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(int $requestedQuantity, int $availableQuantity, string $itemName = 'المنتج')
    {
        parent::__construct(
            "مخزون غير كافي لـ {$itemName}. المطلوب: {$requestedQuantity}، المتاح: {$availableQuantity}"
        );
    }
}
