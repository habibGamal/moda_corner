import React from 'react';
import { Badge } from '@/Components/ui/badge';
import { useI18n } from '@/hooks/use-i18n';
import {
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle
} from 'lucide-react';
import { App } from '@/types';

interface ReturnStatusBadgeProps {
  status: App.Models.ReturnOrderStatus;
  className?: string;
}

export default function ReturnStatusBadge({ status, className }: ReturnStatusBadgeProps) {
  const { t } = useI18n();

  // Helper function to get appropriate return status badge color and icon
  const getReturnStatusDisplay = (status: App.Models.ReturnOrderStatus) => {
    switch (status) {
      case 'requested':
        return {
          color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500',
          icon: <Clock className="h-4 w-4" />
        };
      case 'approved':
        return {
          color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-500',
          icon: <CheckCircle className="h-4 w-4" />
        };
      case 'rejected':
        return {
          color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-500',
          icon: <XCircle className="h-4 w-4" />
        };
      case 'completed':
        return {
          color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-500',
          icon: <CheckCircle className="h-4 w-4" />
        };
      default:
        return {
          color: 'bg-gray-100 text-gray-800 dark:bg-gray-800/30 dark:text-gray-500',
          icon: <AlertCircle className="h-4 w-4" />
        };
    }
  };

  const statusDisplay = getReturnStatusDisplay(status);

  return (
    <Badge
      variant="outline"
      className={`${statusDisplay.color} ${className || ''}`}
    >
      {statusDisplay.icon}
      <span className="ltr:ml-1 rtl:mr-1">
        {t(`return_status_${status}`, status)}
      </span>
    </Badge>
  );
}
