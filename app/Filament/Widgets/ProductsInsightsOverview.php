<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProductsInsightsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $category = $this->filters['category'] ?? [];
        $status = $this->filters['status'] ?? [];

        // Base query for products
        $productsQuery = \App\Models\Product::query();        // Apply filters
        if (!empty($category)) {
            $productsQuery->whereIn('category_id', $category);
        }
        if (!empty($status)) {
            // Convert status to is_active boolean
            if (in_array('active', $status)) {
                $productsQuery->where('is_active', true);
            }
            if (in_array('inactive', $status)) {
                $productsQuery->where('is_active', false);
            }
        }

        // Average Product Price
        $avgPrice = (clone $productsQuery)->avg('price') ?? 0;        // Total Product Views (assuming you have a views column or tracking)
        $totalViews = 0; // Since views column doesn't exist in migration

        // Products Added in Date Range
        $newProducts = (clone $productsQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Average Rating (assuming you have a rating system)
        $avgRating = 0; // Since rating columns don't exist in migration

        // Calculate conversion rate (views to sales)
        $totalSales = \App\Models\OrderItem::query()
            ->whereHas('product', function($query) use ($productsQuery) {
                $query->whereIn('id', $productsQuery->pluck('id'));
            })
            ->whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->sum('quantity');

        $conversionRate = $totalViews > 0 ? round(($totalSales / $totalViews) * 100, 2) : 0;

        return [
            Stat::make('متوسط سعر المنتج', number_format(round($avgPrice, 2)) . ' ج.م')
                ->description("متوسط أسعار المنتجات")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('إجمالي المشاهدات', number_format($totalViews))
                ->description("مشاهدات المنتجات")
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),

            Stat::make('منتجات جديدة', number_format($newProducts))
                ->description("أضيفت خلال الفترة المحددة")
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('primary'),

            Stat::make('متوسط التقييم', number_format(round($avgRating, 1), 1) . '/5')
                ->description("تقييم المنتجات")
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
