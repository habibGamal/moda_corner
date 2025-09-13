import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { useI18n } from '@/hooks/use-i18n';
import { CheckCircle } from 'lucide-react';

interface ReturnSummaryCardProps {
  selectedCount: number;
  totalRefundAmount: number;
  className?: string;
}

export default function ReturnSummaryCard({
  selectedCount,
  totalRefundAmount,
  className
}: ReturnSummaryCardProps) {
  const { t } = useI18n();

  if (selectedCount === 0) {
    return null;
  }

  return (
    <Card className={className}>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <CheckCircle className="h-5 w-5 text-green-600" />
          {t("return_summary", "Return Summary")}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label className="text-sm font-medium">
              {t("selected_items", "Selected Items")}
            </Label>
            <p className="text-sm mt-1">
              {selectedCount} {t("items", "items")}
            </p>
          </div>
          <div>
            <Label className="text-sm font-medium">
              {t("estimated_refund", "Estimated Refund Amount")}
            </Label>
            <p className="text-sm mt-1 font-medium text-green-600">
              EGP {totalRefundAmount.toFixed(2)}
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
