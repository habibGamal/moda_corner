<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomersSalesOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $customerType = $this->filters['customerType'] ?? [];

        $baseQuery = \App\Models\User::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate));

        // Previous period for comparison
        $previousStartDate = $startDate ? now()->parse($startDate)->subMonth() : now()->subMonths(2);
        $previousEndDate = $startDate ? now()->parse($startDate) : now()->subMonth();

        // Total Customers
        $totalCustomers = $baseQuery->clone()->count();
        $previousTotalCustomers = \App\Models\User::query()
            ->where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<=', $previousEndDate)
            ->count();

        $customersGrowth = $previousTotalCustomers > 0
            ? round((($totalCustomers - $previousTotalCustomers) / $previousTotalCustomers) * 100, 1)
            : 0;

        // Active Customers (who made orders)
        $activeCustomers = \App\Models\User::query()
            ->whereHas('orders', function($query) use ($startDate, $endDate) {
                $query->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                      ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));
            })
            ->count();

        // New vs Returning Customers
        $newCustomers = $baseQuery->clone()
            ->whereDoesntHave('orders', function($query) use ($startDate) {
                $query->where('created_at', '<', $startDate ?? now()->subMonth());
            })
            ->count();

        $returningCustomers = $activeCustomers - $newCustomers;

        // Customer Lifetime Value
        $totalRevenue = \App\Models\Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->when($startDate, fn($query) => $query->where('users.created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('users.created_at', '<=', $endDate))
            ->where('orders.payment_status', \App\Enums\PaymentStatus::PAID)
            ->sum('orders.total');

        $customerLifetimeValue = $totalCustomers > 0 ? round($totalRevenue / $totalCustomers) : 0;

        // Average Orders per Customer
        $totalOrders = \App\Models\Order::query()
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->when($startDate, fn($query) => $query->where('users.created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('users.created_at', '<=', $endDate))
            ->count();

        $avgOrdersPerCustomer = $activeCustomers > 0 ? round($totalOrders / $activeCustomers, 1) : 0;

        return [
            Stat::make('إجمالي العملاء', number_format($totalCustomers))
                ->description($customersGrowth >= 0 ? "+{$customersGrowth}% من الفترة السابقة" : "{$customersGrowth}% من الفترة السابقة")
                ->descriptionIcon($customersGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($customersGrowth >= 0 ? 'success' : 'danger')
                ->chart(array_fill(0, 7, rand(10, $totalCustomers))),

            Stat::make('العملاء النشطين', number_format($activeCustomers))
                ->description($totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100, 1) . '% من الإجمالي' : '0% من الإجمالي')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('العملاء الجدد', number_format($newCustomers))
                ->description($activeCustomers > 0 ? round(($newCustomers / $activeCustomers) * 100, 1) . '% من النشطين' : '0% من النشطين')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('العملاء العائدون', number_format($returningCustomers))
                ->description($activeCustomers > 0 ? round(($returningCustomers / $activeCustomers) * 100, 1) . '% من النشطين' : '0% من النشطين')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('القيمة مدى الحياة', 'ج.م ' . number_format($customerLifetimeValue))
                ->description('متوسط إيرادات العميل')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('متوسط الطلبات للعميل', $avgOrdersPerCustomer)
                ->description('طلب لكل عميل نشط')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('gray'),
        ];
    }
}
