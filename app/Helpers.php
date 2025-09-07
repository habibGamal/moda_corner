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
