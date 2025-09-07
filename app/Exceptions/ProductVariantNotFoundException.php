<?php

namespace App\Exceptions;

use Exception;

class ProductVariantNotFoundException extends Exception
{
    public function __construct(int $variantId)
    {
        parent::__construct("Product variant with ID {$variantId} not found.");
    }
}
