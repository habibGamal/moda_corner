import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { useI18n } from '@/hooks/use-i18n';
import { User } from 'lucide-react';

interface ReturnAdminNotesCardProps {
  adminNotes?: string;
}

export function ReturnAdminNotesCard({ adminNotes }: ReturnAdminNotesCardProps) {
  const { t } = useI18n();

  if (!adminNotes) {
    return null;
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <User className="h-5 w-5" />
          {t("admin_notes", "Admin Notes")}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <p className="text-sm text-muted-foreground bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
          {adminNotes}
        </p>
      </CardContent>
    </Card>
  );
}
