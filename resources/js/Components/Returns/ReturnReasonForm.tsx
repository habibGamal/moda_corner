import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { useI18n } from '@/hooks/use-i18n';

interface ReturnReasonFormProps {
  reason: string;
  onReasonChange: (reason: string) => void;
  error?: string;
}

export function ReturnReasonForm({ reason, onReasonChange, error }: ReturnReasonFormProps) {
  const { t } = useI18n();

  return (
    <Card>
      <CardHeader>
        <CardTitle>
          {t("return_reason", "Return Reason")}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-2">
          <Label htmlFor="reason">
            {t(
              "reason_for_return",
              "Please explain why you want to return these items"
            )}
          </Label>
          <Textarea
            id="reason"
            placeholder={t(
              "return_reason_placeholder",
              "Please provide a detailed reason for the return..."
            )}
            value={reason}
            onChange={(e) => onReasonChange(e.target.value)}
            rows={4}
            className={error ? "border-red-500" : ""}
          />
          {error && (
            <p className="text-sm text-red-500">{error}</p>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
