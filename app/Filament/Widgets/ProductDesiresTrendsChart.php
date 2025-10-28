<?php

namespace App\Filament\Widgets;

use App\Models\ProductDesire;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ProductDesiresTrendsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'اتجاهات رغبات المنتجات';

    protected static ?string $description = 'نمو تسجيل الرغبات في المنتجات بمرور الوقت';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $categoryId = $this->filters['categoryId'] ?? null;

        // Get daily trends
        $trendsQuery = ProductDesire::query()
            ->where('product_desires.created_at', '>=', $startDate)
            ->where('product_desires.created_at', '<=', $endDate)
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->join('products', 'product_desires.product_id', '=', 'products.id')
                    ->where('products.category_id', $categoryId);
            })
            ->select(
                DB::raw('DATE(product_desires.created_at) as date'),
                DB::raw('COUNT(*) as desires_count'),
                DB::raw('COUNT(DISTINCT product_desires.email) as unique_users')
            )
            ->groupBy(DB::raw('DATE(product_desires.created_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing dates with zeros
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $labels = [];
        $desiresData = [];
        $usersData = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('j/n');

            $dayData = $trendsQuery->firstWhere('date', $dateStr);
            $desiresData[] = $dayData?->desires_count ?? 0;
            $usersData[] = $dayData?->unique_users ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'الرغبات المسجلة',
                    'data' => $desiresData,
                    'borderColor' => 'rgb(236, 72, 153)',
                    'backgroundColor' => 'rgba(236, 72, 153, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'العملاء المهتمين',
                    'data' => $usersData,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
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
                    'title' => [
                        'display' => true,
                        'text' => 'التاريخ',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'العدد',
                    ],
                    'beginAtZero' => true,
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}
