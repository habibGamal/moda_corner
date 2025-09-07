import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { PageTitle } from '@/Components/ui/page-title';
import { useI18n } from '@/hooks/use-i18n';
import { formatDate } from '@/lib/utils';
import { App } from '@/types';
import { CalendarDays, Package, ArrowLeft, RotateCcw } from 'lucide-react';

interface ReturnHistoryItem {
  id: number;
  return_status: string;
  return_requested_at: string;
  return_reason: string;
  total: number;
  items: Array<{
    id: number;
    quantity: number;
    price: number;
    product: {
      name_ar: string;
      name_en: string;
    };
    variant?: {
      color?: string;
      size?: string;
      capacity?: string;
    };
  }>;
}

interface ReturnHistoryProps extends App.Interfaces.AppPageProps {
  returnHistory: ReturnHistoryItem[];
}

export default function ReturnHistory({ returnHistory }: ReturnHistoryProps) {
  const { t, getLocalizedField } = useI18n();

  // Helper function to get appropriate return status badge color
  const getReturnStatusBadgeColor = (status: string) => {
    switch (status) {
      case 'return_requested':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500';
      case 'return_approved':
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500';
      case 'return_rejected':
        return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500';
      case 'item_returned':
      case 'refund_processed':
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500';
      default:
        return 'bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500';
    }
  };  return (
    <>
      <Head title={t("return_history", "Return History")} />

      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <PageTitle
            title={t("return_history", "Return History")}
            icon={<RotateCcw className="h-6 w-6 text-primary" />}
          />
          <Link href={route('orders.index')}>
            <Button variant="outline" className="flex items-center gap-2">
              <ArrowLeft className="h-4 w-4" />
              {t("back_to_orders", "Back to Orders")}
            </Button>
          </Link>
        </div>

        {returnHistory.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-12">
              <Package className="h-16 w-16 text-muted-foreground mb-4" />
              <h2 className="text-xl font-semibold text-muted-foreground mb-2">
                {t("no_returns", "No Return Requests")}
              </h2>
              <p className="text-muted-foreground text-center">
                {t("no_returns_message", "You haven't requested any returns yet")}
              </p>
              <Link href={route('orders.index')} className="mt-4">
                <Button>{t("view_orders", "View Orders")}</Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {returnHistory.map((returnItem) => (
              <Card key={returnItem.id} className="overflow-hidden">
                <CardHeader className="bg-muted/30 pb-3">
                  <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <CardTitle className="text-base font-medium flex items-center gap-2">
                      <Package className="h-5 w-5" />
                      {t("order_number", "Order")} #{returnItem.id}
                    </CardTitle>
                    <div className="flex items-center gap-2 text-sm text-muted-foreground flex-wrap">
                      <span className="flex items-center gap-1">
                        <CalendarDays className="h-4 w-4" />
                        {formatDate(returnItem.return_requested_at)}
                      </span>
                      <span className="hidden sm:inline">•</span>
                      <Badge
                        variant="outline"
                        className={getReturnStatusBadgeColor(returnItem.return_status)}
                      >
                        {t(`return_status_${returnItem.return_status}`, returnItem.return_status)}
                      </Badge>
                    </div>
                  </div>
                </CardHeader>

                <CardContent className="pt-4">
                  <div className="space-y-4">
                    <div className="flex flex-col sm:flex-row justify-between gap-4">
                      <div className="space-y-1">
                        <p className="text-sm font-medium">
                          {t("items", "Items")}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          {returnItem.items?.length || 0} {t("items", "items")}
                        </p>
                      </div>
                      <div className="space-y-1">
                        <p className="text-sm font-medium">
                          {t("total_amount", "Total Amount")}
                        </p>
                        <p className="text-sm">
                          EGP {Number(returnItem.total).toFixed(2)}
                        </p>
                      </div>
                    </div>

                    <div>
                      <h4 className="text-sm font-medium mb-2">
                        {t("return_reason", "Return Reason")}:
                      </h4>
                      <p className="text-sm text-muted-foreground bg-muted/50 p-3 rounded-lg">
                        {returnItem.return_reason}
                      </p>
                    </div>

                    <div>
                      <h4 className="text-sm font-medium mb-3">
                        {t("returned_products", "Returned Products")}:
                      </h4>
                      <div className="space-y-2">
                        {returnItem.items.map((item) => (
                          <div
                            key={item.id}
                            className="flex items-center justify-between p-3 bg-muted/50 rounded-lg"
                          >
                            <div className="flex-1">
                              <h5 className="text-sm font-medium">
                                {getLocalizedField(item.product, 'name')}
                              </h5>
                              {item.variant && (
                                <div className="text-xs text-muted-foreground mt-1 space-x-2">
                                  {item.variant.color && (
                                    <span>{t("color", "Color")}: {item.variant.color}</span>
                                  )}
                                  {item.variant.size && (
                                    <span>{t("size", "Size")}: {item.variant.size}</span>
                                  )}
                                  {item.variant.capacity && (
                                    <span>{t("capacity", "Capacity")}: {item.variant.capacity}</span>
                                  )}
                                </div>
                              )}
                            </div>
                            <div className="text-right space-y-1">
                              <div className="text-xs text-muted-foreground">
                                {t("quantity", "Qty")}: {item.quantity}
                              </div>
                              <div className="text-sm font-medium">
                                EGP {Number(item.price).toFixed(2)}
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
