<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PaymentMethodsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'طرق الدفع المستخدمة';

    protected static ?string $description = 'توزيع الطلبات حسب طريقة الدفع';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $orderStatus = $this->filters['orderStatus'] ?? [];

        $baseQuery = \App\Models\Order::query()
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->when(! empty($orderStatus), fn ($query) => $query->whereIn('order_status', $orderStatus));
        $paymentMethodCounts = [
            'cash_on_delivery' => $baseQuery->clone()->where('payment_method', \App\Enums\PaymentMethod::CASH_ON_DELIVERY)->count(),
            'kashier' => $baseQuery->clone()->where('payment_method', \App\Enums\PaymentMethod::CARD)->count(),
        ];

        $labels = [
            'الدفع عند الاستلام',
            'الدفع الإلكتروني',
        ];

        $data = array_values($paymentMethodCounts);

        // Calculate revenue for each payment method
        $revenueData = [
            $baseQuery->clone()->where('payment_method', \App\Enums\PaymentMethod::CASH_ON_DELIVERY)->sum('total'),
            $baseQuery->clone()->where('payment_method', \App\Enums\PaymentMethod::CARD)->sum('total'),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'عدد الطلبات',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // Cash on Delivery - Green
                        'rgba(59, 130, 246, 0.8)', // Electronic Payment - Blue
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
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
