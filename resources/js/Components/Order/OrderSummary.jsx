import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslation } from 'react-i18next';
import { formatCurrency } from '@/lib/utils';

/**
 * Order Summary Component
 * Shows the order summary details including subtotal, shipping, discounts and total
 */
export default function OrderSummary({
  subtotal,
  shipping,
  discount,
  total,
  showCheckoutButton = true,
  onCheckoutClick
}) {
  const { t } = useTranslation();

  return (
    <Card className="shadow-md">
      <CardHeader>
        <CardTitle>{t('Order Summary')}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          <div className="flex justify-between">
            <span className="text-muted-foreground">{t('Subtotal')}</span>
            <span className="font-medium">{formatCurrency(subtotal)}</span>
          </div>

          <div className="flex justify-between">
            <span className="text-muted-foreground">{t('Shipping')}</span>
            <span className="font-medium">{formatCurrency(shipping)}</span>
          </div>

          {discount > 0 && (
            <div className="flex justify-between">
              <span className="text-muted-foreground">{t('Discount')}</span>
              <span className="font-medium text-green-600">-{formatCurrency(discount)}</span>
            </div>
          )}

          <div className="border-t pt-4">
            <div className="flex justify-between font-medium">
              <span className="text-lg">{t('Total')}</span>
              <span className="text-lg">{formatCurrency(total)}</span>
            </div>
          </div>

          {showCheckoutButton && onCheckoutClick && (
            <button
              onClick={onCheckoutClick}
              className="w-full bg-primary text-primary-foreground py-2 px-4 rounded-md hover:bg-primary/90"
            >
              {t('Proceed to Checkout')}
            </button>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
