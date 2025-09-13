import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useI18n } from '@/hooks/use-i18n';
import { MessageSquare } from 'lucide-react';

interface ReturnReasonCardProps {
  reason: string;
}

export function ReturnReasonCard({ reason }: ReturnReasonCardProps) {
  const { t } = useI18n();

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <MessageSquare className="h-5 w-5" />
          {t("return_reason", "Return Reason")}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <p className="text-sm text-muted-foreground bg-muted/50 p-4 rounded-lg">
          {reason}
        </p>
      </CardContent>
    </Card>
  );
}
