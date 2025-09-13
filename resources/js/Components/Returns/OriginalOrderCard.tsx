import React from 'react';
import { Link } from '@inertiajs/react';
import { Card, CardContent } from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import { useI18n } from '@/hooks/use-i18n';
import { Package } from 'lucide-react';

interface OriginalOrderCardProps {
  orderId: number;
}

export function OriginalOrderCard({ orderId }: OriginalOrderCardProps) {
  const { t } = useI18n();

  return (
    <Card>
      <CardContent className="pt-6">
        <div className="flex items-center justify-between">
          <div>
            <h4 className="font-medium">{t("original_order", "Original Order")}</h4>
            <p className="text-sm text-muted-foreground">
              {t("view_original_order_details", "View the details of the original order")}
            </p>
          </div>
          <Link href={route('orders.show', orderId)}>
            <Button variant="outline" className="flex items-center gap-2">
              <Package className="h-4 w-4" />
              {t("view_order", "View Order")} #{orderId}
            </Button>
          </Link>
        </div>
      </CardContent>
    </Card>
  );
}
