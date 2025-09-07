<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopCustomersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أفضل العملاء';

    protected static ?string $description = 'العملاء الأكثر إنفاقاً خلال الفترة المحددة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();

        $topCustomers = \App\Models\User::query()
            ->whereHas('orders', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })            ->withSum(['orders' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total')
            ->orderByDesc('orders_sum_total')
            ->limit(10)
            ->get();

        $labels = [];
        $data = [];
        $backgroundColors = [];

        $colors = [
            'rgb(34, 197, 94)',   // Green
            'rgb(59, 130, 246)',  // Blue
            'rgb(251, 191, 36)',  // Yellow
            'rgb(239, 68, 68)',   // Red
            'rgb(147, 51, 234)',  // Purple
            'rgb(236, 72, 153)',  // Pink
            'rgb(14, 165, 233)',  // Sky
            'rgb(34, 197, 94)',   // Emerald
            'rgb(245, 158, 11)',  // Amber
            'rgb(99, 102, 241)',  // Indigo
        ];

        foreach ($topCustomers as $index => $customer) {
            $customerName = $customer->name ?? 'عميل غير محدد';
            // Truncate long names
            if (strlen($customerName) > 20) {
                $customerName = substr($customerName, 0, 17) . '...';
            }

            $labels[] = $customerName;
            $data[] = round($customer->orders_sum_total ?? 0, 2);
            $backgroundColors[] = $colors[$index % count($colors)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'إجمالي الإنفاق',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
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
            'indexAxis' => 'y', // Horizontal bars
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return "إجمالي الإنفاق: " + context.parsed.x.toLocaleString() + " ج.م";
                        }',
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'المبلغ (ج.م)',
                    ],
                    'beginAtZero' => true,
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'العملاء',
                    ],
                ],
            ],
            'elements' => [
                'bar' => [
                    'borderRadius' => 4,
                ],
            ],
        ];
    }
}
