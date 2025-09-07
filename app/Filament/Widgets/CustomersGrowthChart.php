<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class CustomersGrowthChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'نمو العملاء';

    protected static ?string $description = 'عدد العملاء الجدد والنشطين خلال الفترة المحددة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $customerType = $this->filters['customerType'] ?? [];

        // Generate date range
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = $start->diffInDays($end);

        $labels = [];
        $newCustomersData = [];
        $activeCustomersData = [];

        if ($days <= 30) {
            // Daily data for periods up to 30 days
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $labels[] = $date->format('M d');

                $dayNewCustomers = \App\Models\User::whereDate('created_at', $date)->count();

                $dayActiveCustomers = \App\Models\User::query()
                    ->whereHas('orders', function($query) use ($date) {
                        $query->whereDate('created_at', $date);
                    })
                    ->count();

                $newCustomersData[] = $dayNewCustomers;
                $activeCustomersData[] = $dayActiveCustomers;
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

                $weekNewCustomers = \App\Models\User::whereBetween('created_at', [$date, $weekEnd])->count();

                $weekActiveCustomers = \App\Models\User::query()
                    ->whereHas('orders', function($query) use ($date, $weekEnd) {
                        $query->whereBetween('created_at', [$date, $weekEnd]);
                    })
                    ->count();

                $newCustomersData[] = $weekNewCustomers;
                $activeCustomersData[] = $weekActiveCustomers;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'عملاء جدد',
                    'data' => $newCustomersData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'عملاء نشطين',
                    'data' => $activeCustomersData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
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
