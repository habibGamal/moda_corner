import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { useI18n } from '@/hooks/use-i18n';
import { Package } from 'lucide-react';
import { App } from '@/types';

interface ReturnItemsListProps {
  returnItems: App.Models.ReturnOrderItem[];
  totalRefundAmount: number;
}

export function ReturnItemsList({ returnItems, totalRefundAmount }: ReturnItemsListProps) {
  const { t, getLocalizedField } = useI18n();

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Package className="h-5 w-5" />
          {t("returned_items", "Returned Items")}
        </CardTitle>
      </CardHeader>
      <CardContent>
        {!returnItems || returnItems.length === 0 ? (
          <p className="text-muted-foreground text-center py-8">
            {t("no_items_found", "No items found for this return.")}
          </p>
        ) : (
          <div className="space-y-4">
            {returnItems.map((returnItem) => (
              <div
                key={returnItem.id}
                className="flex items-center justify-between p-4 bg-muted/30 rounded-lg"
              >
                <div className="flex-1">
                  <h5 className="text-sm font-medium">
                    {getLocalizedField(returnItem.order_item?.product, 'name')}
                  </h5>
                  {returnItem.order_item?.variant && (
                    <div className="text-xs text-muted-foreground mt-1 space-x-2 rtl:space-x-reverse">
                      {returnItem.order_item.variant.color && (
                        <span>{t("color", "Color")}: {returnItem.order_item.variant.color}</span>
                      )}
                      {returnItem.order_item.variant.size && (
                        <span>{t("size", "Size")}: {returnItem.order_item.variant.size}</span>
                      )}
                      {returnItem.order_item.variant.capacity && (
                        <span>{t("capacity", "Capacity")}: {returnItem.order_item.variant.capacity}</span>
                      )}
                    </div>
                  )}
                  <div className="text-xs text-muted-foreground mt-1">
                    {t("unit_price", "Unit Price")}: EGP {Number(returnItem.unit_price).toFixed(2)}
                  </div>
                </div>
                <div className="text-right space-y-1 ltr:ml-4 rtl:mr-4">
                  <div className="text-sm font-medium">
                    {t("quantity", "Qty")}: {returnItem.quantity}
                  </div>
                  <div className="text-sm font-semibold">
                    EGP {Number(returnItem.total_price).toFixed(2)}
                  </div>
                </div>
              </div>
            ))}

            <Separator />

            <div className="flex items-center justify-between font-semibold">
              <span>{t("total_refund_amount", "Total Refund Amount")}:</span>
              <span className="text-lg text-green-600">
                EGP {totalRefundAmount.toFixed(2)}
              </span>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  );
}
