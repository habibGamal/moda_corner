<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class WishlistTrendsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'اتجاهات قائمة الأمنيات';

    protected static ?string $description = 'نمو إضافة العناصر إلى قوائم الأمنيات بمرور الوقت';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();

        // Get daily trends
        $trends = WishlistItem::query()
            ->where('wishlist_items.created_at', '>=', $startDate)
            ->where('wishlist_items.created_at', '<=', $endDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as items_added'),
                DB::raw('COUNT(DISTINCT user_id) as unique_users')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing dates with zeros
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            (new \DateTime($endDate))->modify('+1 day')
        );

        $labels = [];
        $itemsData = [];
        $usersData = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('j/n');

            $dayData = $trends->firstWhere('date', $dateStr);
            $itemsData[] = $dayData?->items_added ?? 0;
            $usersData[] = $dayData?->unique_users ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'العناصر المضافة',
                    'data' => $itemsData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => 'العملاء النشطين',
                    'data' => $usersData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
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
