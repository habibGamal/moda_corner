<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProductsInventoryChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'حالة المخزون';

    protected static ?string $description = 'توزيع المنتجات حسب حالة المخزون';

    protected function getData(): array
    {
        $category = $this->filters['category'] ?? [];
        $status = $this->filters['status'] ?? [];

        $query = \App\Models\Product::query();        // Apply filters
        if (!empty($category)) {
            $query->whereIn('category_id', $category);
        }
        if (!empty($status)) {
            if (in_array('active', $status)) {
                $query->where('is_active', true);
            }
            if (in_array('inactive', $status)) {
                $query->where('is_active', false);
            }
        }

        // Get inventory status based on available fields
        $activeProducts = (clone $query)->where('is_active', true)->count();
        $inactiveProducts = (clone $query)->where('is_active', false)->count();
        $featuredProducts = (clone $query)->where('is_featured', true)->count();
        $totalProducts = (clone $query)->count();
        $regularProducts = $totalProducts - $featuredProducts;

        return [
            'datasets' => [
                [
                    'data' => [
                        $activeProducts,
                        $inactiveProducts,
                        $featuredProducts,
                        $regularProducts,
                    ],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',  // Green for active
                        'rgb(239, 68, 68)',  // Red for inactive
                        'rgb(251, 191, 36)', // Yellow for featured
                        'rgb(59, 130, 246)', // Blue for regular
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                'نشط',
                'غير نشط',
                'مميز',
                'عادي',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const label = context.label || "";
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ": " + value + " منتج (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'elements' => [
                'arc' => [
                    'borderWidth' => 0,
                ],
            ],
        ];
    }
}
