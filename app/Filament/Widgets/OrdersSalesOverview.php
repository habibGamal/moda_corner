<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersSalesOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $orderStatus = $this->filters['orderStatus'] ?? [];

        $baseQuery = \App\Models\Order::query()
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->when(! empty($orderStatus), fn ($query) => $query->whereIn('order_status', $orderStatus));

        // Total Orders
        $totalOrders = $baseQuery->clone()->count();

        // Previous period for comparison
        $previousStartDate = $startDate ? now()->parse($startDate)->subMonth() : now()->subMonths(2);
        $previousEndDate = $startDate ? now()->parse($startDate) : now()->subMonth();

        $previousTotalOrders = \App\Models\Order::query()
            ->where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<=', $previousEndDate)
            ->when(! empty($orderStatus), fn ($query) => $query->whereIn('order_status', $orderStatus))
            ->count();

        $ordersGrowth = $previousTotalOrders > 0
            ? round((($totalOrders - $previousTotalOrders) / $previousTotalOrders) * 100, 1)
            : 0;

        // Total Revenue (only paid orders)
        $totalRevenue = $baseQuery->clone()
            ->where('payment_status', \App\Enums\PaymentStatus::PAID)
            ->sum('total');

        $previousRevenue = \App\Models\Order::query()
            ->where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<=', $previousEndDate)
            ->where('payment_status', \App\Enums\PaymentStatus::PAID)
            ->when(! empty($orderStatus), fn ($query) => $query->whereIn('order_status', $orderStatus))
            ->sum('total');

        $revenueGrowth = $previousRevenue > 0
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : 0;

        // Average Order Value
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Pending Orders (processing + shipped)
        $pendingOrders = $baseQuery->clone()
            ->whereIn('order_status', [\App\Enums\OrderStatus::PROCESSING, \App\Enums\OrderStatus::SHIPPED])
            ->count();

        // Completed Orders
        $completedOrders = $baseQuery->clone()
            ->where('order_status', \App\Enums\OrderStatus::DELIVERED)
            ->count();

        // Cancelled Orders
        $cancelledOrders = $baseQuery->clone()
            ->where('order_status', \App\Enums\OrderStatus::CANCELLED)
            ->count();

        return [
            Stat::make('إجمالي الطلبات', number_format($totalOrders))
                ->description($ordersGrowth >= 0 ? "+{$ordersGrowth}% من الفترة السابقة" : "{$ordersGrowth}% من الفترة السابقة")
                ->descriptionIcon($ordersGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersGrowth >= 0 ? 'success' : 'danger')
                ->chart(array_fill(0, 7, rand(10, $totalOrders))),

            Stat::make('إجمالي الإيرادات', 'ج.م '.number_format(round($totalRevenue)))
                ->description($revenueGrowth >= 0 ? "+{$revenueGrowth}% من الفترة السابقة" : "{$revenueGrowth}% من الفترة السابقة")
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart(array_fill(0, 7, rand(1000, round($totalRevenue)))),

            Stat::make('متوسط قيمة الطلب', 'ج.م '.number_format(round($averageOrderValue)))
                ->description('من إجمالي الطلبات المدفوعة')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('طلبات قيد التنفيذ', number_format($pendingOrders))
                ->description('قيد المعالجة والشحن')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('طلبات مكتملة', number_format($completedOrders))
                ->description('تم التوصيل بنجاح')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('طلبات ملغاة', number_format($cancelledOrders))
                ->description($totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 1).'% من الإجمالي' : '0% من الإجمالي')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
