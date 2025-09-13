import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Separator } from '@/Components/ui/separator';
import { useI18n } from '@/hooks/use-i18n';
import { formatDate } from '@/lib/utils';
import { App } from '@/types';
import { CalendarDays, RotateCcw, CreditCard } from 'lucide-react';
import ReturnStatusBadge from './ReturnStatusBadge';

interface ReturnOrderDetailsProps {
  returnOrder: App.Models.ReturnOrder;
}

export default function ReturnOrderDetails({ returnOrder }: ReturnOrderDetailsProps) {
  const { t } = useI18n();

  const totalItemsCount = returnOrder.return_items?.reduce((sum, item) => sum + item.quantity, 0) || 0;

  return (
    <Card>
      <CardHeader>
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <CardTitle className="flex items-center gap-2">
            <RotateCcw className="h-5 w-5" />
            {t("return_information", "Return Information")}
          </CardTitle>
          <ReturnStatusBadge status={returnOrder.status} />
        </div>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="space-y-1">
            <p className="text-sm font-medium">{t("return_id", "Return ID")}</p>
            <p className="text-sm text-muted-foreground">#{returnOrder.id}</p>
          </div>
          <div className="space-y-1">
            <p className="text-sm font-medium">{t("order_number", "Order Number")}</p>
            <Link
              href={route('orders.show', returnOrder.order_id)}
              className="text-sm text-primary hover:underline"
            >
              #{returnOrder.order_id}
            </Link>
          </div>
          <div className="space-y-1">
            <p className="text-sm font-medium">{t("request_date", "Request Date")}</p>
            <p className="text-sm text-muted-foreground flex items-center gap-1">
              <CalendarDays className="h-4 w-4" />
              {formatDate(returnOrder.created_at)}
            </p>
          </div>
          <div className="space-y-1">
            <p className="text-sm font-medium">{t("total_items", "Total Items")}</p>
            <p className="text-sm text-muted-foreground">{totalItemsCount} {t("items", "items")}</p>
          </div>
        </div>

        {returnOrder.refund_amount && (
          <>
            <Separator className="my-4" />
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <p className="text-sm font-medium flex items-center gap-2">
                  <CreditCard className="h-4 w-4" />
                  {t("refund_amount", "Refund Amount")}
                </p>
                <p className="text-lg font-semibold text-green-600">
                  EGP {Number(returnOrder.refund_amount).toFixed(2)}
                </p>
              </div>
              {returnOrder.refunded_at && (
                <div className="text-right space-y-1">
                  <p className="text-sm font-medium">{t("refunded_on", "Refunded On")}</p>
                  <p className="text-sm text-muted-foreground flex items-center gap-1">
                    <CalendarDays className="h-4 w-4" />
                    {formatDate(returnOrder.refunded_at)}
                  </p>
                </div>
              )}
            </div>
          </>
        )}
      </CardContent>
    </Card>
  );
}
