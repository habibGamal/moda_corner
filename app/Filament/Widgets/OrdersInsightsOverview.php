<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class OrdersInsightsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $orderStatus = $this->filters['orderStatus'] ?? [];

        $baseQuery = \App\Models\Order::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->when(!empty($orderStatus), fn($query) => $query->whereIn('order_status', $orderStatus));

        // Get period length for calculations
        $periodDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) ?: 1;

        // Total customers who made orders
        $uniqueCustomers = $baseQuery->clone()->distinct('user_id')->count('user_id');

        // Orders per day average
        $totalOrders = $baseQuery->clone()->count();
        $ordersPerDay = round($totalOrders / $periodDays, 1);

        // Revenue per day average
        $totalRevenue = $baseQuery->clone()
            ->where('payment_status', PaymentStatus::PAID)
            ->sum('total');
        $revenuePerDay = round($totalRevenue / $periodDays, 2);

        // Conversion rate (delivered vs total)
        $deliveredOrders = $baseQuery->clone()
            ->where('order_status', OrderStatus::DELIVERED)
            ->count();
        $conversionRate = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100, 1) : 0;

        // Return rate
        $returnedOrders = \App\Models\Order::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->whereNotNull('return_status')
            ->count();
        $returnRate = $totalOrders > 0 ? round(($returnedOrders / $totalOrders) * 100, 1) : 0;

        // Average shipping cost
        $avgShippingCost = $baseQuery->clone()->avg('shipping_cost') ?: 0;        return [
            Stat::make('العملاء النشطين', number_format($uniqueCustomers))
                ->description('عدد العملاء الذين قاموا بطلبات')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('متوسط الطلبات يومياً', round($ordersPerDay, 1))
                ->description("خلال {$periodDays} يوم")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('متوسط الإيرادات يومياً', 'ج.م ' . number_format(round($revenuePerDay)))
                ->description("إجمالي ج.م " . number_format(round($totalRevenue)))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('معدل إتمام الطلبات', $conversionRate . '%')
                ->description($deliveredOrders . ' من ' . $totalOrders . ' طلب')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($conversionRate >= 80 ? 'success' : ($conversionRate >= 60 ? 'warning' : 'danger')),

            Stat::make('معدل الإرجاع', $returnRate . '%')
                ->description($returnedOrders . ' طلب مرتجع')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color($returnRate <= 5 ? 'success' : ($returnRate <= 10 ? 'warning' : 'danger')),

            Stat::make('متوسط تكلفة الشحن', 'ج.م ' . number_format(round($avgShippingCost)))
                ->description('لكل طلب')
                ->descriptionIcon('heroicon-m-truck')
                ->color('gray'),
        ];
    }
}
