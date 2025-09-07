<?php

namespace App\Exceptions;

use Exception;

class ProductNotFoundException extends Exception
{
    public function __construct(int $productId)
    {
        parent::__construct("Product with ID {$productId} not found.");
    }
}
