import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { useI18n } from '@/hooks/use-i18n';
import { formatDate } from '@/lib/utils';
import { Package, CalendarDays } from 'lucide-react';
import { App } from '@/types';

interface ReturnOrderInfoProps {
  order: App.Models.Order;
}

export function ReturnOrderInfo({ order }: ReturnOrderInfoProps) {
  const { t } = useI18n();

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Package className="h-5 w-5" />
          {t("order_details", "Order Details")}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <Label className="text-sm font-medium">
              {t("order_number", "Order Number")}
            </Label>
            <p className="text-sm mt-1">#{order.id}</p>
          </div>
          <div>
            <Label className="text-sm font-medium">
              {t("order_date", "Order Date")}
            </Label>
            <p className="text-sm mt-1 flex items-center gap-1">
              <CalendarDays className="h-4 w-4" />
              {formatDate(order.created_at || "")}
            </p>
          </div>
          <div>
            <Label className="text-sm font-medium">
              {t("order_total", "Order Total")}
            </Label>
            <p className="text-sm mt-1">
              EGP {Number(order.total).toFixed(2)}
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
