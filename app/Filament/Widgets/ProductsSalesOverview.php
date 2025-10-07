<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductsSalesOverview extends BaseWidget
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
        if (! empty($category)) {
            $productsQuery->whereIn('category_id', $category);
        }
        if (! empty($status)) {
            // Convert status to is_active boolean
            if (in_array('active', $status)) {
                $productsQuery->where('is_active', true);
            }
            if (in_array('inactive', $status)) {
                $productsQuery->where('is_active', false);
            }
        }

        // Total Products
        $totalProducts = (clone $productsQuery)->count();        // Active Products
        $activeProducts = (clone $productsQuery)->where('is_active', true)->count();

        // Products with Sales (within date range)
        $productsWithSales = (clone $productsQuery)
            ->whereHas('orderItems', function ($query) use ($startDate, $endDate) {
                $query->whereHas('order', function ($orderQuery) use ($startDate, $endDate) {
                    $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
                });
            })
            ->count();

        // Out of Stock Products (assuming we need to check variants for stock)
        $outOfStockProducts = (clone $productsQuery)
            ->where('is_active', false)
            ->count();

        // Calculate percentages and trends
        $activePercentage = $totalProducts > 0 ? round(($activeProducts / $totalProducts) * 100, 1) : 0;
        $salesPercentage = $totalProducts > 0 ? round(($productsWithSales / $totalProducts) * 100, 1) : 0;
        $stockPercentage = $totalProducts > 0 ? round(($outOfStockProducts / $totalProducts) * 100, 1) : 0;

        return [
            Stat::make('إجمالي المنتجات', number_format($totalProducts))
                ->description('المنتجات المسجلة في النظام')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('المنتجات النشطة', number_format($activeProducts))
                ->description("{$activePercentage}% من إجمالي المنتجات")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('المنتجات المباعة', number_format($productsWithSales))
                ->description("{$salesPercentage}% من إجمالي المنتجات")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('نفد المخزون', number_format($outOfStockProducts))
                ->description("{$stockPercentage}% من إجمالي المنتجات")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
