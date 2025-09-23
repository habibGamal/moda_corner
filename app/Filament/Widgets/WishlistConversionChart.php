<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class WishlistConversionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'معدل التحويل من قائمة الأمنيات';

    protected static ?string $description = 'مقارنة العناصر المحولة لشراء فعلي مقابل غير المحولة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $baseQuery = WishlistItem::query()
            ->when($startDate, fn ($query) => $query->where('wishlist_items.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('wishlist_items.created_at', '<=', $endDate));

        // Total wishlist items
        $totalItems = $baseQuery->clone()->count();

        // Converted items (purchased)
        $convertedItems = $baseQuery->clone()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereColumn('order_items.product_id', 'wishlist_items.product_id')
                    ->whereColumn('orders.user_id', 'wishlist_items.user_id')
                    ->where(function ($q) {
                        $q->whereNull('wishlist_items.product_variant_id')
                            ->orWhereColumn('order_items.variant_id', '=', 'wishlist_items.product_variant_id');
                    })
                    ->where('orders.created_at', '>=', DB::raw('wishlist_items.created_at'));
            })
            ->count();

        // Non-converted items
        $nonConvertedItems = $totalItems - $convertedItems;

        // Calculate percentages
        $convertedPercentage = $totalItems > 0 ? round(($convertedItems / $totalItems) * 100, 1) : 0;
        $nonConvertedPercentage = $totalItems > 0 ? round(($nonConvertedItems / $totalItems) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'حالة التحويل',
                    'data' => [$convertedItems, $nonConvertedItems],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',   // Green for converted
                        'rgba(239, 68, 68, 0.8)',   // Red for non-converted
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                "تم الشراء ({$convertedPercentage}%)",
                "لم يتم الشراء ({$nonConvertedPercentage}%)",
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
            'plugins' => [
                'legend' => [
                    'display' => true,
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
                            const value = context.parsed || 0;
                            return label + ": " + value + " عنصر";
                        }',
                    ],
                ],
            ],
            'cutout' => '60%',
            'maintainAspectRatio' => true,
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }
}
