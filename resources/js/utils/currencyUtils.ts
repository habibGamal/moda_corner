/**
 * Currency utility functions for Egyptian Pounds (EGP)
 */

/**
 * Format amount as Egyptian Pounds
 * @param amount - The amount to format
 * @param includeSymbol - Whether to include the currency symbol
 * @param decimals - Number of decimal places
 * @returns Formatted currency string
 */
export function formatCurrency(
    amount: number | string,
    includeSymbol: boolean = true,
    decimals: number = 2
): string {
    const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    const formattedAmount = numericAmount.toFixed(decimals);

    if (includeSymbol) {
        return `EGP ${formattedAmount}`;
    }

    return formattedAmount;
}

/**
 * Format amount as Egyptian Pounds in Arabic context
 * @param amount - The amount to format
 * @param includeSymbol - Whether to include the currency symbol
 * @param decimals - Number of decimal places
 * @returns Formatted currency string
 */
export function formatCurrencyAr(
    amount: number | string,
    includeSymbol: boolean = true,
    decimals: number = 2
): string {
    const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    const formattedAmount = numericAmount.toFixed(decimals);

    if (includeSymbol) {
        return `ج.م ${formattedAmount}`;
    }

    return formattedAmount;
}

/**
 * Get currency symbol based on current locale
 * @param locale - Current locale (en/ar)
 * @returns Currency symbol
 */
export function getCurrencySymbol(locale: string = 'en'): string {
    return locale === 'ar' ? 'ج.م' : 'EGP';
}

/**
 * Format currency with localized symbol
 * @param amount - The amount to format
 * @param locale - Current locale
 * @param includeSymbol - Whether to include the currency symbol
 * @param decimals - Number of decimal places
 * @returns Formatted currency string
 */
export function formatCurrencyLocalized(
    amount: number | string,
    locale: string = 'en',
    includeSymbol: boolean = true,
    decimals: number = 2
): string {
    const numericAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
    const formattedAmount = numericAmount.toFixed(decimals);

    if (includeSymbol) {
        const symbol = getCurrencySymbol(locale);
        return `${symbol} ${formattedAmount}`;
    }

    return formattedAmount;
}
