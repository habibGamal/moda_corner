<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class ProductsPerformanceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أداء المنتجات';

    protected static ?string $description = 'إيرادات المنتجات وعدد المبيعات خلال الفترة المحددة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $category = $this->filters['category'] ?? [];

        // Generate date range
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end);

        $labels = [];
        $revenueData = [];
        $salesData = [];

        if ($days <= 30) {
            // Daily data for periods up to 30 days
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $labels[] = $date->format('M d');                $dayRevenue = \App\Models\OrderItem::query()
                    ->whereHas('order', function($query) use ($date) {
                        $query->whereDate('created_at', $date);
                    })
                    ->when(!empty($category), function($query) use ($category) {
                        $query->whereHas('product', function($productQuery) use ($category) {
                            $productQuery->whereIn('category_id', $category);
                        });
                    })
                    ->sum(\DB::raw('quantity * unit_price'));

                $daySales = \App\Models\OrderItem::query()
                    ->whereHas('order', function($query) use ($date) {
                        $query->whereDate('created_at', $date);
                    })
                    ->when(!empty($category), function($query) use ($category) {
                        $query->whereHas('product', function($productQuery) use ($category) {
                            $productQuery->whereIn('category_id', $category);
                        });
                    })
                    ->sum('quantity');

                $revenueData[] = round($dayRevenue, 2);
                $salesData[] = $daySales;
            }
        } else {
            // Weekly data for longer periods
            $start = $start->startOfWeek();
            for ($date = $start->copy(); $date->lte($end); $date->addWeek()) {
                $weekEnd = $date->copy()->endOfWeek();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end;
                }

                $labels[] = $date->format('M d') . ' - ' . $weekEnd->format('M d');                $weekRevenue = \App\Models\OrderItem::query()
                    ->whereHas('order', function($query) use ($date, $weekEnd) {
                        $query->whereBetween('created_at', [$date, $weekEnd]);
                    })
                    ->when(!empty($category), function($query) use ($category) {
                        $query->whereHas('product', function($productQuery) use ($category) {
                            $productQuery->whereIn('category_id', $category);
                        });
                    })
                    ->sum(\DB::raw('quantity * unit_price'));

                $weekSales = \App\Models\OrderItem::query()
                    ->whereHas('order', function($query) use ($date, $weekEnd) {
                        $query->whereBetween('created_at', [$date, $weekEnd]);
                    })
                    ->when(!empty($category), function($query) use ($category) {
                        $query->whereHas('product', function($productQuery) use ($category) {
                            $productQuery->whereIn('category_id', $category);
                        });
                    })
                    ->sum('quantity');

                $revenueData[] = round($weekRevenue, 2);
                $salesData[] = $weekSales;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (ج.م)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                    'fill' => true,
                ],
                [
                    'label' => 'عدد المبيعات',
                    'data' => $salesData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y1',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'التاريخ',
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'الإيرادات (ج.م)',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'عدد المبيعات',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
