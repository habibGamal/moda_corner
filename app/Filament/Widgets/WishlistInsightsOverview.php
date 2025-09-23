<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class WishlistInsightsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $baseQuery = WishlistItem::query()
            ->when($startDate, fn ($query) => $query->where('wishlist_items.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('wishlist_items.created_at', '<=', $endDate));

        // Most Active Day
        $mostActiveDay = $baseQuery->clone()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('count', 'desc')
            ->first();

        $mostActiveDayText = $mostActiveDay
            ? now()->parse($mostActiveDay->date)->locale('ar')->translatedFormat('j F')." ({$mostActiveDay->count} عنصر)"
            : 'لا يوجد بيانات';

        // Average Time to Purchase (for converted items)
        $avgTimeToPurchase = $baseQuery->clone()
            ->join('order_items', function ($join) {
                $join->on('wishlist_items.product_id', '=', 'order_items.product_id');
                $join->where(function ($query) {
                    $query->whereNull('wishlist_items.product_variant_id')
                        ->orWhereColumn('wishlist_items.product_variant_id', '=', 'order_items.variant_id');
                });
            })
            ->join('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->on('wishlist_items.user_id', '=', 'orders.user_id');
            })
            ->where('orders.created_at', '>=', DB::raw('wishlist_items.created_at'))
            ->selectRaw('AVG(TIMESTAMPDIFF(DAY, wishlist_items.created_at, orders.created_at)) as avg_days')
            ->value('avg_days');

        $avgTimeToPurchaseText = $avgTimeToPurchase
            ? round($avgTimeToPurchase).' يوم'
            : 'غير محدد';

        // Top Category in Wishlist
        $topCategory = $baseQuery->clone()
            ->join('products', 'wishlist_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name_ar', DB::raw('COUNT(*) as count'))
            ->groupBy('categories.id', 'categories.name_ar')
            ->orderBy('count', 'desc')
            ->first();

        $topCategoryText = $topCategory?->name_ar ?? 'لا يوجد';
        $topCategoryCount = $topCategory?->count ?? 0;

        // Seasonal Trend (current vs previous month)
        $currentMonth = $baseQuery->clone()
            ->where('wishlist_items.created_at', '>=', now()->startOfMonth())
            ->count();

        $previousMonth = WishlistItem::query()
            ->where('wishlist_items.created_at', '>=', now()->subMonth()->startOfMonth())
            ->where('wishlist_items.created_at', '<', now()->startOfMonth())
            ->count();

        $seasonalTrend = $previousMonth > 0
            ? round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1)
            : 0;

        // Users with Multiple Items
        $usersWithMultipleItems = $baseQuery->clone()
            ->select('user_id', DB::raw('COUNT(*) as item_count'))
            ->groupBy('user_id')
            ->having('item_count', '>', 1)
            ->count();

        // Abandonment Rate (items added but never purchased after 30 days)
        $oldItems = WishlistItem::query()
            ->where('wishlist_items.created_at', '<=', now()->subDays(30))
            ->count();

        $abandonedItems = WishlistItem::query()
            ->where('wishlist_items.created_at', '<=', now()->subDays(30))
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereColumn('order_items.product_id', 'wishlist_items.product_id')
                    ->whereColumn('orders.user_id', 'wishlist_items.user_id')
                    ->where(function ($q) {
                        $q->whereNull('wishlist_items.product_variant_id')
                            ->orWhereColumn('order_items.variant_id', '=', 'wishlist_items.product_variant_id');
                    })
                    ->where('orders.created_at', '>=', DB::raw('wishlist_items.created_at'));
            })
            ->count();

        $abandonmentRate = $oldItems > 0 ? round(($abandonedItems / $oldItems) * 100, 1) : 0;

        return [
            Stat::make('اليوم الأكثر نشاطاً', $mostActiveDayText)
                ->description('أعلى يوم في إضافة العناصر')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('متوسط وقت الشراء', $avgTimeToPurchaseText)
                ->description('من الإضافة إلى الشراء الفعلي')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('الفئة الأكثر اهتماماً', $topCategoryText)
                ->description("تم إضافة {$topCategoryCount} منتج منها")
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),

            Stat::make('الاتجاه الموسمي', ($seasonalTrend >= 0 ? '+' : '').$seasonalTrend.'%')
                ->description('مقارنة بالشهر السابق')
                ->descriptionIcon($seasonalTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($seasonalTrend >= 0 ? 'success' : 'danger'),

            Stat::make('عملاء متعددو الاهتمامات', number_format($usersWithMultipleItems))
                ->description('لديهم أكثر من منتج واحد')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('معدل الهجر', $abandonmentRate.'%')
                ->description('عناصر لم تُشترى بعد 30 يوم')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($abandonmentRate <= 50 ? 'success' : ($abandonmentRate <= 70 ? 'warning' : 'danger')),
        ];
    }
}
