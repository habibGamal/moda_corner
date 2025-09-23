<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class PopularWishlistProductsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'المنتجات الأكثر إضافة لقوائم الأمنيات';

    protected static ?string $description = 'أفضل 10 منتجات من حيث الإضافة لقوائم الأمنيات';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $popularProducts = WishlistItem::query()
            ->join('products', 'wishlist_items.product_id', '=', 'products.id')
            ->when($startDate, fn ($query) => $query->where('wishlist_items.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('wishlist_items.created_at', '<=', $endDate))
            ->select(
                'products.name_ar as product_name',
                DB::raw('COUNT(*) as wishlist_count'),
                DB::raw('COUNT(DISTINCT wishlist_items.user_id) as unique_users')
            )
            ->groupBy('wishlist_items.product_id', 'products.name_ar')
            ->orderBy('wishlist_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $popularProducts->pluck('product_name')->map(function ($name) {
            return mb_strlen($name) > 30 ? mb_substr($name, 0, 30).'...' : $name;
        })->toArray();

        $wishlistData = $popularProducts->pluck('wishlist_count')->toArray();
        $usersData = $popularProducts->pluck('unique_users')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'عدد مرات الإضافة',
                    'data' => $wishlistData,
                    'backgroundColor' => [
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(236, 72, 153)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 146, 60)',
                        'rgb(168, 85, 247)',
                        'rgb(14, 165, 233)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
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
                    'position' => 'right',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.label + ": " + context.parsed + " مرة"; }',
                    ],
                ],
            ],
            'cutout' => '50%',
            'maintainAspectRatio' => false,
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }
}
