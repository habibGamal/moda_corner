<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ProductsCategoriesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'توزيع المنتجات حسب الفئات';

    protected static ?string $description = 'نسبة المنتجات في كل فئة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $status = $this->filters['status'] ?? [];        $categoriesData = \App\Models\Product::query()
            ->when(!empty($status), function($query) use ($status) {
                if (in_array('active', $status)) {
                    $query->where('is_active', true);
                }
                if (in_array('inactive', $status)) {
                    $query->where('is_active', false);
                }
            })
            ->with('category')
            ->get()
            ->groupBy('category.name_ar')
            ->map(function($products, $categoryName) {
                return (object)[
                    'category' => $categoryName ?: 'غير محدد',
                    'count' => $products->count()
                ];
            })
            ->values()
            ->sortByDesc('count');

        $labels = [];
        $data = [];
        $backgroundColors = [];

        $categoryTranslations = [
            'electronics' => 'إلكترونيات',
            'clothing' => 'ملابس',
            'home' => 'منزل وحديقة',
            'books' => 'كتب',
            'sports' => 'رياضة',
            'beauty' => 'جمال وعناية',
            'automotive' => 'سيارات',
            'food' => 'أطعمة',
        ];

        $colors = [
            'rgb(34, 197, 94)',   // Green
            'rgb(59, 130, 246)',  // Blue
            'rgb(251, 191, 36)',  // Yellow
            'rgb(239, 68, 68)',   // Red
            'rgb(147, 51, 234)',  // Purple
            'rgb(236, 72, 153)',  // Pink
            'rgb(14, 165, 233)',  // Sky
            'rgb(245, 158, 11)',  // Amber
        ];        foreach ($categoriesData as $index => $category) {
            $categoryName = $category->category;
            $labels[] = $categoryName;
            $data[] = $category->count;
            $backgroundColors[] = $colors[$index % count($colors)];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
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
