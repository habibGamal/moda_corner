<?php

namespace App\Filament\Widgets;

use App\Models\ProductDesire;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProductDesiresOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $categoryId = $this->filters['categoryId'] ?? null;

        $baseQuery = ProductDesire::query()
            ->when($startDate, fn ($query) => $query->where('product_desires.created_at', '>=', $startDate))
            // ->when($endDate, fn ($query) => $query->where('product_desires.created_at', '<=', $endDate))
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->join('products', 'product_desires.product_id', '=', 'products.id')
                    ->where('products.category_id', $categoryId);
            });

        // Total Desires
        $totalDesires = $baseQuery->clone()->count();

        // Previous period for comparison
        $previousStartDate = $startDate ? now()->parse($startDate)->subMonth() : now()->subMonths(2);
        $previousEndDate = $startDate ? now()->parse($startDate) : now()->subMonth();

        $previousTotalDesires = ProductDesire::query()
            ->where('product_desires.created_at', '>=', $previousStartDate)
            ->where('product_desires.created_at', '<=', $previousEndDate)
            ->count();

        $desiresGrowth = $previousTotalDesires > 0
            ? round((($totalDesires - $previousTotalDesires) / $previousTotalDesires) * 100, 1)
            : 0;

        // Unique Products
        $uniqueProducts = $baseQuery->clone()->distinct('product_id')->count('product_id');

        // Unique Users/Emails
        $uniqueEmails = $baseQuery->clone()->distinct('email')->count('email');

        // Desire Intensity (average desires per product)
        $desireIntensity = $uniqueProducts > 0 ? round($totalDesires / $uniqueProducts, 1) : 0;

        // Repeat Desire Rate (users who subscribed multiple times)
        $totalUsers = $baseQuery->clone()->distinct('email')->count('email');
        $usersWithMultipleDesires = DB::table(
            DB::raw("({$baseQuery->clone()->select('email')->toSql()}) as filtered_desires")
        )
            ->mergeBindings($baseQuery->clone()->select('email')->getQuery())
            ->select('email', DB::raw('COUNT(*) as desire_count'))
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        $repeatRate = $totalUsers > 0 ? round(($usersWithMultipleDesires / $totalUsers) * 100, 1) : 0;

        // Conversion Rate (desires that led to purchases)
        $convertedDesires = $baseQuery->clone()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                    ->whereColumn('order_items.product_id', 'product_desires.product_id')
                    ->where(function ($q) {
                        $q->whereColumn('orders.user_id', 'product_desires.user_id')
                            ->orWhereColumn('users.email', 'product_desires.email');
                    })
                    ->where('orders.created_at', '>=', DB::raw('product_desires.created_at'));
            })
            ->count();

        $conversionRate = $totalDesires > 0 ? round(($convertedDesires / $totalDesires) * 100, 1) : 0;

        return [
            Stat::make('إجمالي الرغبات المسجلة', number_format($totalDesires))
                ->description($desiresGrowth >= 0 ? "+{$desiresGrowth}% من الفترة السابقة" : "{$desiresGrowth}% من الفترة السابقة")
                ->descriptionIcon($desiresGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($desiresGrowth >= 0 ? 'success' : 'danger')
                ->chart(array_fill(0, 7, rand(10, max($totalDesires, 10)))),

            Stat::make('المنتجات المرغوبة', number_format($uniqueProducts))
                ->description('عدد المنتجات التي سجل فيها العملاء رغبة')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('العملاء المهتمين', number_format($uniqueEmails))
                ->description('عدد العملاء الفريدين')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('كثافة الطلب', $desireIntensity)
                ->description('متوسط الرغبات لكل منتج')
                ->descriptionIcon('heroicon-m-fire')
                ->color($desireIntensity >= 5 ? 'danger' : ($desireIntensity >= 3 ? 'warning' : 'info')),

            Stat::make('معدل التكرار', "{$repeatRate}%")
                ->description('عملاء سجلوا رغبات متعددة')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($repeatRate >= 30 ? 'success' : 'info'),

            Stat::make('معدل التحويل', "{$conversionRate}%")
                ->description('من الرغبة إلى الشراء الفعلي')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($conversionRate >= 20 ? 'success' : ($conversionRate >= 10 ? 'warning' : 'danger')),
        ];
    }
}
