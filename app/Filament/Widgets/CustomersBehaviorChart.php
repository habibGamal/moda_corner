<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CustomersBehaviorChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'سلوك العملاء';

    protected static ?string $description = 'توزيع سلوك العملاء حسب مستوى النشاط';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $customerType = $this->filters['customerType'] ?? [];

        // Get customers within date range
        $customers = \App\Models\User::query()
            ->whereHas('orders', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            });

        // Apply customer type filter if specified
        if (! empty($customerType)) {
            // Assuming customer type filtering logic
            // This would need to be adjusted based on your actual customer type implementation
        }        $customers = $customers->with(['orders' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('user_id, COUNT(*) as order_count, SUM(total) as total_spent')
                ->groupBy('user_id');
        }])->get();

        $veryActive = 0; // 10+ orders
        $active = 0; // 5-9 orders
        $moderate = 0; // 2-4 orders
        $lowActivity = 0; // 1 order

        foreach ($customers as $customer) {
            $orderCount = $customer->orders->count();

            if ($orderCount >= 10) {
                $veryActive++;
            } elseif ($orderCount >= 5) {
                $active++;
            } elseif ($orderCount >= 2) {
                $moderate++;
            } else {
                $lowActivity++;
            }
        }

        return [
            'datasets' => [
                [
                    'data' => [
                        $veryActive,
                        $active,
                        $moderate,
                        $lowActivity,
                    ],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',  // Green for very active
                        'rgb(59, 130, 246)', // Blue for active
                        'rgb(251, 191, 36)', // Yellow for moderate
                        'rgb(239, 68, 68)',  // Red for low activity
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                'نشط جداً (10+ طلبات)',
                'نشط (5-9 طلبات)',
                'متوسط (2-4 طلبات)',
                'نشاط منخفض (طلب واحد)',
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
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
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
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ": " + value + " (" + percentage + "%)";
                        }',
                    ],
                ],
            ],
            'elements' => [
                'arc' => [
                    'borderWidth' => 0,
                ],
            ],
        ];
    }
}
