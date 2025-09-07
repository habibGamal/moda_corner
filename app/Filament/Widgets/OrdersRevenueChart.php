<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class OrdersRevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'إيرادات الطلبات';

    protected static ?string $description = 'إجمالي الإيرادات خلال الفترة المحددة';    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $orderStatus = $this->filters['orderStatus'] ?? [];

        // Generate date range
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end);

        $labels = [];
        $revenueData = [];
        $ordersData = [];

        if ($days <= 30) {
            // Daily data for periods up to 30 days
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $labels[] = $date->format('M d');

                $dayRevenue = \App\Models\Order::whereDate('created_at', $date)
                    ->where('payment_status', \App\Enums\PaymentStatus::PAID)
                    ->when(!empty($orderStatus), fn($query) => $query->whereIn('order_status', $orderStatus))
                    ->sum('total');

                $dayOrders = \App\Models\Order::whereDate('created_at', $date)
                    ->when(!empty($orderStatus), fn($query) => $query->whereIn('order_status', $orderStatus))
                    ->count();

                $revenueData[] = round($dayRevenue, 2);
                $ordersData[] = $dayOrders;
            }
        } else {
            // Weekly data for longer periods
            $start = $start->startOfWeek();
            for ($date = $start->copy(); $date->lte($end); $date->addWeek()) {
                $weekEnd = $date->copy()->endOfWeek();
                if ($weekEnd->gt($end)) {
                    $weekEnd = $end;
                }

                $labels[] = $date->format('M d') . ' - ' . $weekEnd->format('M d');

                $weekRevenue = \App\Models\Order::whereBetween('created_at', [$date, $weekEnd])
                    ->where('payment_status', \App\Enums\PaymentStatus::PAID)
                    ->when(!empty($orderStatus), fn($query) => $query->whereIn('order_status', $orderStatus))
                    ->sum('total');

                $weekOrders = \App\Models\Order::whereBetween('created_at', [$date, $weekEnd])
                    ->when(!empty($orderStatus), fn($query) => $query->whereIn('order_status', $orderStatus))
                    ->count();

                $revenueData[] = round($weekRevenue, 2);
                $ordersData[] = $weekOrders;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (ج.م)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'عدد الطلبات',
                    'data' => $ordersData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'yAxisID' => 'y1',
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
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
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
                        'text' => 'التاريخ',
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'الإيرادات (ج.م)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'عدد الطلبات',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
