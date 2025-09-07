<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CustomersInsightsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $customerType = $this->filters['customerType'] ?? [];

        // Get period length for calculations
        $periodDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) ?: 1;

        // Customer acquisition rate
        $newCustomers = \App\Models\User::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->count();

        $acquisitionRate = round($newCustomers / $periodDays, 1);

        // Customer retention rate (customers who made multiple orders)
        $repeatCustomers = \App\Models\User::query()
            ->whereHas('orders', function($query) use ($startDate, $endDate) {
                $query->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                      ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));
            }, '>=', 2)
            ->count();

        $activeCustomers = \App\Models\User::query()
            ->whereHas('orders', function($query) use ($startDate, $endDate) {
                $query->when($startDate, fn($q) => $q->where('created_at', '>=', $startDate))
                      ->when($endDate, fn($q) => $q->where('created_at', '<=', $endDate));
            })
            ->count();

        $retentionRate = $activeCustomers > 0 ? round(($repeatCustomers / $activeCustomers) * 100, 1) : 0;

        // Average time between orders (simplified calculation)
        $ordersByUser = \App\Models\Order::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->selectRaw('user_id, COUNT(*) as order_count, DATEDIFF(MAX(created_at), MIN(created_at)) as days_span')
            ->groupBy('user_id')
            ->having('order_count', '>', 1)
            ->get();

        $avgTimeBetweenOrders = 0;
        if ($ordersByUser->count() > 0) {
            $totalAvgDays = $ordersByUser->sum(function($user) {
                return $user->order_count > 1 ? $user->days_span / ($user->order_count - 1) : 0;
            });
            $avgTimeBetweenOrders = $totalAvgDays / $ordersByUser->count();
        }

        // Top customer segments by location
        $topGovernorate = \App\Models\Order::query()
            ->leftJoin('addresses', 'orders.shipping_address_id', '=', 'addresses.id')
            ->leftJoin('areas', 'addresses.area_id', '=', 'areas.id')
            ->leftJoin('govs', 'areas.gov_id', '=', 'govs.id')
            ->when($startDate, fn($query) => $query->where('orders.created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('orders.created_at', '<=', $endDate))
            ->whereNotNull('govs.id')
            ->selectRaw('govs.name_ar, COUNT(DISTINCT orders.user_id) as customer_count')
            ->groupBy('govs.id', 'govs.name_ar')
            ->orderBy('customer_count', 'desc')
            ->first();

        // Customer satisfaction (based on delivered orders vs returns)
        $deliveredOrders = \App\Models\Order::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->where('order_status', \App\Enums\OrderStatus::DELIVERED)
            ->count();

        $returnedOrders = \App\Models\Order::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->whereNotNull('return_status')
            ->count();

        $satisfactionRate = $deliveredOrders > 0 ? round((($deliveredOrders - $returnedOrders) / $deliveredOrders) * 100, 1) : 0;

        return [
            Stat::make('معدل اكتساب العملاء', $acquisitionRate . ' يومياً')
                ->description("خلال {$periodDays} يوم")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('معدل الاحتفاظ', $retentionRate . '%')
                ->description($repeatCustomers . ' من ' . $activeCustomers . ' عميل')
                ->descriptionIcon('heroicon-m-heart')
                ->color($retentionRate >= 30 ? 'success' : ($retentionRate >= 20 ? 'warning' : 'danger')),

            Stat::make('متوسط الفترة بين الطلبات', round($avgTimeBetweenOrders) . ' يوم')
                ->description('للعملاء المتكررين')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),

            Stat::make('أعلى محافظة', $topGovernorate ? $topGovernorate->name_ar : 'غير محدد')
                ->description($topGovernorate ? $topGovernorate->customer_count . ' عميل' : 'لا توجد بيانات')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),

            Stat::make('معدل الرضا', $satisfactionRate . '%')
                ->description('بناءً على الطلبات المكتملة')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color($satisfactionRate >= 90 ? 'success' : ($satisfactionRate >= 80 ? 'warning' : 'danger')),

            Stat::make('العملاء المتكررون', number_format($repeatCustomers))
                ->description('أكثر من طلب واحد')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),
        ];
    }
}
