import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { useI18n } from '@/hooks/use-i18n';
import { formatDate } from '@/lib/utils';
import { App } from '@/types';
import { CalendarDays, Package, Eye } from 'lucide-react';
import ReturnStatusBadge from './ReturnStatusBadge';

interface ReturnOrderCardProps {
  returnOrder: App.Models.ReturnOrder;
}

export default function ReturnOrderCard({ returnOrder }: ReturnOrderCardProps) {
  const { t } = useI18n();

  return (
    <Card className="overflow-hidden">
      <CardHeader className="bg-muted/30 pb-3">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <CardTitle className="text-base font-medium flex items-center gap-2">
            <Package className="h-5 w-5" />
            {t("return_request", "Return Request")} #{returnOrder.id}
          </CardTitle>
          <div className="flex items-center gap-2 text-sm text-muted-foreground flex-wrap">
            <span className="flex items-center gap-1">
              <CalendarDays className="h-4 w-4" />
              {formatDate(returnOrder.created_at)}
            </span>
            <span className="hidden sm:inline">•</span>
            <ReturnStatusBadge status={returnOrder.status} />
          </div>
        </div>
      </CardHeader>

      <CardContent className="pt-4">
        <div className="space-y-4">
          <div className="flex flex-col sm:flex-row justify-between gap-4">
            <div className="space-y-1">
              <p className="text-sm font-medium">
                {t("order_number", "Order")} #{returnOrder.order_id}
              </p>
              <p className="text-sm text-muted-foreground">
                {returnOrder.return_items?.length || 0} {t("items", "items")}
              </p>
            </div>
            {returnOrder.refund_amount && (
              <div className="space-y-1">
                <p className="text-sm font-medium">
                  {t("refund_amount", "Refund Amount")}
                </p>
                <p className="text-sm">
                  EGP {Number(returnOrder.refund_amount).toFixed(2)}
                </p>
              </div>
            )}
          </div>

          <div>
            <h4 className="text-sm font-medium mb-2">
              {t("return_reason", "Return Reason")}:
            </h4>
            <p className="text-sm text-muted-foreground bg-muted/50 p-3 rounded-lg">
              {returnOrder.reason}
            </p>
          </div>

          {returnOrder.admin_notes && (
            <div>
              <h4 className="text-sm font-medium mb-2">
                {t("admin_notes", "Admin Notes")}:
              </h4>
              <p className="text-sm text-muted-foreground bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                {returnOrder.admin_notes}
              </p>
            </div>
          )}

          {returnOrder.refunded_at && (
            <div className="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
              <CalendarDays className="h-4 w-4" />
              {t("refunded_on", "Refunded on")} {formatDate(returnOrder.refunded_at)}
            </div>
          )}

          <div className="flex items-center justify-end pt-2">
            <Link href={route('returns.show', returnOrder.id)}>
              <Button variant="outline" className="flex items-center gap-2">
                <Eye className="h-4 w-4" />
                {t("view_details", "View Details")}
              </Button>
            </Link>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
