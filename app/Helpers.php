<?php

/*
 * Here you can define your own helper functions.
 * Make sure to use the `function_exists` check to not declare the function twice.
 */

if (! function_exists('example')) {
    function example(): string
    {
        return 'This is an example function you can use in your project.';
    }
}

if (! function_exists('formatCurrency')) {
    /**
     * Format currency amount in Egyptian Pounds (EGP)
     *
     * @param float|int|string $amount
     * @param bool $includeSymbol Whether to include the currency symbol
     * @param int $decimals Number of decimal places
     * @return string
     */
    function formatCurrency($amount, bool $includeSymbol = true, int $decimals = 2): string
    {
        $formattedAmount = number_format((float) $amount, $decimals);

        if ($includeSymbol) {
            return 'ج.م ' . $formattedAmount;
        }

        return $formattedAmount;
    }
}

if (! function_exists('formatCurrencyEn')) {
    /**
     * Format currency amount in Egyptian Pounds (EGP) for English context
     *
     * @param float|int|string $amount
     * @param bool $includeSymbol Whether to include the currency symbol
     * @param int $decimals Number of decimal places
     * @return string
     */
    function formatCurrencyEn($amount, bool $includeSymbol = true, int $decimals = 2): string
    {
        $formattedAmount = number_format((float) $amount, $decimals);

        if ($includeSymbol) {
            return 'EGP ' . $formattedAmount;
        }

        return $formattedAmount;
    }
}

if (! function_exists('generateMerchantOrderNumber')) {
    /**
     * Generate a unique order number
     *
     * @return string
     */
    function generateMerchantOrderNumber($order_id): string
    {
        return config('app.name').'-'.$order_id;
    }
}
if (! function_exists('extractOrderIdFromMerchantOrderNumber')) {
    /**
     * Extract the order ID from a merchant order number
     *
     * @param string $merchantOrderNumber
     * @return int|null
     */
    function extractOrderIdFromMerchantOrderNumber(string $merchantOrderNumber): ?int
    {
        $prefix = config('app.name') . '-';
        if (str_starts_with($merchantOrderNumber, $prefix)) {
            $orderId = substr($merchantOrderNumber, strlen($prefix));
            return is_numeric($orderId) ? (int) $orderId : null;
        }
        return null;
    }
}

