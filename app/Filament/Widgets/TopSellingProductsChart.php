<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopSellingProductsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أفضل المنتجات مبيعاً';

    protected static ?string $description = 'المنتجات الأكثر مبيعاً خلال الفترة المحددة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $category = $this->filters['category'] ?? [];

        $topProducts = \App\Models\OrderItem::query()
            ->with('product')
            ->whereHas('order', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($category), function($query) use ($category) {
                $query->whereHas('product', function($productQuery) use ($category) {
                    $productQuery->whereIn('category_id', $category);
                });
            })
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(quantity * unit_price) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $labels = [];
        $salesData = [];
        $backgroundColors = [];

        $colors = [
            'rgb(34, 197, 94)',   // Green
            'rgb(59, 130, 246)',  // Blue
            'rgb(251, 191, 36)',  // Yellow
            'rgb(239, 68, 68)',   // Red
            'rgb(147, 51, 234)',  // Purple
            'rgb(236, 72, 153)',  // Pink
            'rgb(14, 165, 233)',  // Sky
            'rgb(34, 197, 94)',   // Emerald
            'rgb(245, 158, 11)',  // Amber
            'rgb(99, 102, 241)',  // Indigo
        ];

        foreach ($topProducts as $index => $item) {
            // Use English fallback to avoid UTF-8 encoding issues with json_encode
            $productName = $item->product->name_en ?? $item->product->name_ar ?? 'Unknown Product';
            
            // Truncate long names
            if (strlen($productName) > 25) {
                $productName = substr($productName, 0, 22) . '...';
            }
            
            $labels[] = $productName;
            $salesData[] = (int) $item->total_sold;
            $backgroundColors[] = $colors[$index % count($colors)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $salesData,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'indexAxis' => 'y', // Horizontal bars
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "Quantity Sold: " + context.parsed.x.toLocaleString();
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Quantity Sold',
                    ],
                    'beginAtZero' => true,
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Products',
                    ],
                ],
            ],
            'elements' => [
                'bar' => [
                    'borderRadius' => 4,
                ],
            ],
        ];
    }
}
