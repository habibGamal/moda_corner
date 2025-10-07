<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أكثر المنتجات مبيعاً';

    protected static ?string $description = 'أفضل 10 منتجات من حيث المبيعات';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $orderStatus = $this->filters['orderStatus'] ?? [];

        $topProducts = \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->when($startDate, fn ($query) => $query->where('orders.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('orders.created_at', '<=', $endDate))
            ->when(! empty($orderStatus), fn ($query) => $query->whereIn('orders.order_status', $orderStatus))
            ->select(
                'products.name_ar as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('order_items.product_id', 'products.name_ar')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        $labels = $topProducts->pluck('product_name')->toArray();
        $quantityData = $topProducts->pluck('total_quantity')->toArray();
        $revenueData = $topProducts->pluck('total_revenue')->map(fn ($revenue) => round($revenue, 2))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'الكمية المباعة',
                    'data' => $quantityData,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
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
                'title' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'المنتج',
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'الكمية المباعة',
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
