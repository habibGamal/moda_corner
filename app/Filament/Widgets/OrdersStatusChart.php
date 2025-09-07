<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class OrdersStatusChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'توزيع حالات الطلبات';

    protected static ?string $description = 'نسبة الطلبات حسب الحالة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $orderStatus = $this->filters['orderStatus'] ?? [];

        $baseQuery = \App\Models\Order::query()
            ->when($startDate, fn($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->where('created_at', '<=', $endDate))
            ->when(!empty($orderStatus), fn($query) => $query->whereIn('order_status', $orderStatus));        $statusCounts = [
            'processing' => $baseQuery->clone()->where('order_status', \App\Enums\OrderStatus::PROCESSING)->count(),
            'shipped' => $baseQuery->clone()->where('order_status', \App\Enums\OrderStatus::SHIPPED)->count(),
            'delivered' => $baseQuery->clone()->where('order_status', \App\Enums\OrderStatus::DELIVERED)->count(),
            'cancelled' => $baseQuery->clone()->where('order_status', \App\Enums\OrderStatus::CANCELLED)->count(),
        ];

        $labels = [
            'قيد المعالجة',
            'تم الشحن',
            'تم التوصيل',
            'ملغاة',
        ];

        $data = array_values($statusCounts);

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)', // Processing - Yellow
                        'rgba(59, 130, 246, 0.8)', // Shipped - Blue
                        'rgba(34, 197, 94, 0.8)',  // Delivered - Green
                        'rgba(239, 68, 68, 0.8)',  // Cancelled - Red
                    ],
                    'borderColor' => [
                        'rgb(245, 158, 11)',
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
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
                    'position' => 'bottom',
                ],
                'title' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
