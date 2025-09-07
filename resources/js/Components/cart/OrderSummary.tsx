import { Button } from "@/Components/ui/button";
import { Card } from "@/Components/ui/card";
import { useI18n } from "@/hooks/use-i18n";
import { App } from "@/types";
import { router } from "@inertiajs/react";

interface OrderSummaryProps {
  cartSummary: App.Models.CartSummary;
}

export function OrderSummary({ cartSummary }: OrderSummaryProps) {
  const { t } = useI18n();

  return (
    <div className="lg:col-span-1">
      <Card className="p-6">
        <h3 className="text-lg font-semibold mb-4">{t('order_summary', 'Order Summary')}</h3>
        <div className="space-y-4">
          <div className="flex justify-between">
            <span className="text-muted-foreground">{t('subtotal', 'Subtotal')}</span>
            <span>EGP {cartSummary.totalPrice.toFixed(2)}</span>
          </div>

          <div className="flex justify-between">
            <span className="text-muted-foreground">{t('shipping', 'Shipping')}</span>
            <span>{t('calculated_at_checkout', 'Calculated at checkout')}</span>
          </div>

          <div className="border-t pt-4 flex justify-between font-semibold">
            <span>{t('total', 'Total')}</span>
            <span>EGP {cartSummary.totalPrice.toFixed(2)}</span>
          </div>
          <Button onClick={()=>router.get(route('checkout.index'))} className="w-full" size="lg">
            {t('proceed_to_checkout', 'Proceed to Checkout')}
          </Button>
        </div>
      </Card>
    </div>
  );
}
