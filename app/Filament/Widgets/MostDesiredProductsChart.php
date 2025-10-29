<?php

namespace App\Filament\Widgets;

use App\Models\ProductDesire;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class MostDesiredProductsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'المنتجات الأكثر طلباً';

    protected static ?string $description = 'أفضل 15 منتج من حيث تسجيل الرغبات وكثافة الطلب';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $categoryId = $this->filters['categoryId'] ?? null;

        $mostDesiredProducts = ProductDesire::query()
            ->join('products', 'product_desires.product_id', '=', 'products.id')
            ->when($startDate, fn ($query) => $query->where('product_desires.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('product_desires.created_at', '<=', $endDate))
            ->when($categoryId, fn ($query) => $query->where('products.category_id', $categoryId))
            ->select(
                'products.name_ar as product_name',
                DB::raw('COUNT(*) as desire_count'),
                DB::raw('COUNT(DISTINCT product_desires.email) as unique_users')
            )
            ->groupBy('product_desires.product_id', 'products.name_ar')
            ->orderBy('desire_count', 'desc')
            ->limit(15)
            ->get();

        $labels = $mostDesiredProducts->pluck('product_name')->map(function ($name) {
            return mb_strlen($name) > 35 ? mb_substr($name, 0, 35).'...' : $name;
        })->toArray();

        $desireData = $mostDesiredProducts->pluck('desire_count')->toArray();

        // Calculate intensity (desires per unique user for each product)
        $intensityData = $mostDesiredProducts->map(function ($product) {
            return round($product->desire_count / max($product->unique_users, 1), 2);
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'عدد مرات التسجيل',
                    'data' => $desireData,
                    'backgroundColor' => 'rgba(236, 72, 153, 0.8)',
                    'borderColor' => 'rgb(236, 72, 153)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'كثافة الطلب',
                    'data' => $intensityData,
                    'backgroundColor' => 'rgba(139, 92, 246, 0.8)',
                    'borderColor' => 'rgb(139, 92, 246)',
                    'borderWidth' => 1,
                    'yAxisID' => 'y1',
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
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'عدد التسجيلات',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'كثافة الطلب',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '400px';
    }
}
