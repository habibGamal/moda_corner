import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { PageTitle } from '@/Components/ui/page-title';
import EmptyState from '@/Components/ui/empty-state';
import { ReturnOrderCard } from '@/Components/Returns';
import { useI18n } from '@/hooks/use-i18n';
import { App } from '@/types';
import { Plus, ArrowLeft, RotateCcw, Package } from 'lucide-react';

interface ReturnsIndexProps extends App.Interfaces.AppPageProps {
  returns: App.Models.ReturnOrder[];
}

export default function Index({ returns }: ReturnsIndexProps) {
  const { t } = useI18n();

  return (
    <>
      <Head title={t("my_returns", "My Returns")} />

      <div className="container mt-4">
        <div className="space-y-6">
          <div className="flex items-center justify-between">
            <PageTitle
              title={t("my_returns", "My Returns")}
              icon={<RotateCcw className="h-6 w-6 text-primary" />}
            />
            <div className="flex items-center gap-3">
              <Link href={route('returns.create')}>
                <Button className="flex items-center gap-2">
                  <Plus className="h-4 w-4" />
                  {t("request_return", "Request Return")}
                </Button>
              </Link>
              <Link href={route('orders.index')}>
                <Button variant="outline" className="flex items-center gap-2">
                  <ArrowLeft className="h-4 w-4 ltr:mr-0 ltr:rotate-0 rtl:ml-0 rtl:rotate-180" />
                  {t("back_to_orders", "Back to Orders")}
                </Button>
              </Link>
            </div>
          </div>

          {returns.length === 0 ? (
            <EmptyState
              icon={<Package className="h-16 w-16 text-muted-foreground" />}
              title={t("no_returns", "No Return Requests")}
              description={t("no_returns_message", "You haven't requested any returns yet")}
              action={
                <div className="flex items-center gap-3">
                  <Link href={route('returns.create')}>
                    <Button>{t("request_return", "Request Return")}</Button>
                  </Link>
                  <Link href={route('orders.index')}>
                    <Button variant="outline">{t("view_orders", "View Orders")}</Button>
                  </Link>
                </div>
              }
            />
          ) : (
            <div className="space-y-4">
              {returns.map((returnOrder) => (
                <ReturnOrderCard key={returnOrder.id} returnOrder={returnOrder} />
              ))}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
