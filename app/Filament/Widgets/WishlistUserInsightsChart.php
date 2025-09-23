<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class WishlistUserInsightsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'تحليل سلوك العملاء';

    protected static ?string $description = 'توزيع العملاء حسب عدد المنتجات في قوائمهم';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $userBehavior = WishlistItem::query()
            ->when($startDate, fn ($query) => $query->where('wishlist_items.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('wishlist_items.created_at', '<=', $endDate))
            ->select('user_id', DB::raw('COUNT(*) as item_count'))
            ->groupBy('user_id')
            ->get()
            ->groupBy(function ($item) {
                $count = $item->item_count;
                if ($count == 1) {
                    return '1 منتج';
                }
                if ($count <= 3) {
                    return '2-3 منتجات';
                }
                if ($count <= 5) {
                    return '4-5 منتجات';
                }
                if ($count <= 10) {
                    return '6-10 منتجات';
                }

                return 'أكثر من 10 منتجات';
            })
            ->map(fn ($group) => $group->count());

        $labels = ['1 منتج', '2-3 منتجات', '4-5 منتجات', '6-10 منتجات', 'أكثر من 10 منتجات'];
        $data = [];

        foreach ($labels as $label) {
            $data[] = $userBehavior->get($label, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'عدد العملاء',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Green
                        'rgba(59, 130, 246, 0.8)',  // Blue
                        'rgba(245, 158, 11, 0.8)',  // Yellow
                        'rgba(239, 68, 68, 0.8)',   // Red
                        'rgba(139, 92, 246, 0.8)',  // Purple
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                    ],
                    'borderWidth' => 2,
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
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.parsed.y + " عميل"; }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'عدد المنتجات',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'عدد العملاء',
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
