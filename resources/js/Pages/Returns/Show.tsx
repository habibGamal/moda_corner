import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { PageTitle } from '@/Components/ui/page-title';
import {
  ReturnOrderDetails,
  ReturnReasonCard,
  ReturnAdminNotesCard,
  ReturnItemsList,
  OriginalOrderCard
} from '@/Components/Returns';
import { useI18n } from '@/hooks/use-i18n';
import { App } from '@/types';
import { ArrowLeft, RotateCcw } from 'lucide-react';

interface ReturnShowProps extends App.Interfaces.AppPageProps {
  returnOrder: App.Models.ReturnOrder;
}

export default function Show({ returnOrder }: ReturnShowProps) {
  const { t, getLocalizedField } = useI18n();

  const totalRefundAmount = returnOrder.return_items?.reduce((sum, item) => sum + parseFloat(item.total_price), 0) || 0;

  return (
    <>
      <Head title={t("return_details", "Return Details")} />

      <div className="container mt-4">
        <div className="space-y-6">
          <div className="flex items-center justify-between">
            <PageTitle
              title={`${t("return_request", "Return Request")} #${returnOrder.id}`}
              icon={<RotateCcw className="h-6 w-6 text-primary" />}
            />
            <Link href={route('returns.index')}>
              <Button variant="outline" className="flex items-center gap-2">
                <ArrowLeft className="h-4 w-4 ltr:mr-0 ltr:rotate-0 rtl:ml-0 rtl:rotate-180" />
                {t("back_to_returns", "Back to Returns")}
              </Button>
            </Link>
          </div>

          {/* Return Status & Summary */}
          <ReturnOrderDetails returnOrder={returnOrder} />

          {/* Return Reason */}
          <ReturnReasonCard reason={returnOrder.reason} />

          {/* Admin Notes */}
          <ReturnAdminNotesCard adminNotes={returnOrder.admin_notes} />

          {/* Returned Items */}
          <ReturnItemsList
            returnItems={returnOrder.return_items || []}
            totalRefundAmount={totalRefundAmount}
          />

          {/* Related Order Link */}
          <OriginalOrderCard orderId={returnOrder.order_id} />
        </div>
      </div>
    </>
  );
}
