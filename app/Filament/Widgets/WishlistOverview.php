<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class WishlistOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $userSegment = $this->filters['userSegment'] ?? [];

        $baseQuery = WishlistItem::query()
            ->when($startDate, fn ($query) => $query->where('wishlist_items.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('wishlist_items.created_at', '<=', $endDate));

        // Total Wishlist Items
        $totalWishlistItems = $baseQuery->clone()->count();

        // Previous period for comparison
        $previousStartDate = $startDate ? now()->parse($startDate)->subMonth() : now()->subMonths(2);
        $previousEndDate = $startDate ? now()->parse($startDate) : now()->subMonth();

        $previousTotalItems = WishlistItem::query()
            ->where('wishlist_items.created_at', '>=', $previousStartDate)
            ->where('wishlist_items.created_at', '<=', $previousEndDate)
            ->count();

        $itemsGrowth = $previousTotalItems > 0
            ? round((($totalWishlistItems - $previousTotalItems) / $previousTotalItems) * 100, 1)
            : 0;

        // Unique Users with Wishlist Items
        $uniqueUsers = $baseQuery->clone()->distinct('user_id')->count('user_id');

        $previousUniqueUsers = WishlistItem::query()
            ->where('wishlist_items.created_at', '>=', $previousStartDate)
            ->where('wishlist_items.created_at', '<=', $previousEndDate)
            ->distinct('user_id')
            ->count('user_id');

        $usersGrowth = $previousUniqueUsers > 0
            ? round((($uniqueUsers - $previousUniqueUsers) / $previousUniqueUsers) * 100, 1)
            : 0;

        // Average Items per User
        $averageItemsPerUser = $uniqueUsers > 0 ? round($totalWishlistItems / $uniqueUsers, 1) : 0;

        // Most Popular Product
        $mostPopularProduct = $baseQuery->clone()
            ->select('product_id', DB::raw('COUNT(*) as count'))
            ->groupBy('product_id')
            ->orderBy('count', 'desc')
            ->with('product')
            ->first();

        $mostPopularProductName = $mostPopularProduct?->product?->name_ar ?? 'لا يوجد';
        $mostPopularProductCount = $mostPopularProduct?->count ?? 0;

        // Conversion Rate (wishlist items that turned into orders)
        $convertedItems = $baseQuery->clone()
            ->whereExists(function ($query) {
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

        $conversionRate = $totalWishlistItems > 0 ? round(($convertedItems / $totalWishlistItems) * 100, 1) : 0;

        // Active Wishlist Items (not yet purchased)
        $activeWishlistItems = $baseQuery->clone()
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

        return [
            Stat::make('إجمالي العناصر في القوائم', number_format($totalWishlistItems))
                ->description($itemsGrowth >= 0 ? "+{$itemsGrowth}% من الفترة السابقة" : "{$itemsGrowth}% من الفترة السابقة")
                ->descriptionIcon($itemsGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($itemsGrowth >= 0 ? 'success' : 'danger')
                ->chart(array_fill(0, 7, rand(10, $totalWishlistItems))),

            Stat::make('عدد العملاء المهتمين', number_format($uniqueUsers))
                ->description($usersGrowth >= 0 ? "+{$usersGrowth}% من الفترة السابقة" : "{$usersGrowth}% من الفترة السابقة")
                ->descriptionIcon($usersGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($usersGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('متوسط العناصر لكل عميل', $averageItemsPerUser)
                ->description('عدد المنتجات المضافة لكل عميل')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('المنتج الأكثر إضافة', $mostPopularProductName)
                ->description("تم إضافته {$mostPopularProductCount} مرة")
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('معدل التحويل', "{$conversionRate}%")
                ->description('من قائمة الأمنيات إلى شراء فعلي')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($conversionRate >= 15 ? 'success' : ($conversionRate >= 10 ? 'warning' : 'danger')),

            Stat::make('العناصر النشطة', number_format($activeWishlistItems))
                ->description('لم يتم شراؤها بعد')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }
}
