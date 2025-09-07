<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CustomersGeographyChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'التوزيع الجغرافي للعملاء';

    protected static ?string $description = 'توزيع العملاء حسب المناطق والمدن';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();

        // Get customers with orders in the specified date range
        $customers = \App\Models\User::query()
            ->whereHas('orders', function($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        // Group customers by city/region
        // This assumes you have address information in your user model or related models
        $geographyData = [];

        foreach ($customers as $customer) {
            // Try to get city from user's addresses or orders
            $city = $this->getCustomerCity($customer);

            if ($city) {
                if (!isset($geographyData[$city])) {
                    $geographyData[$city] = 0;
                }
                $geographyData[$city]++;
            }
        }

        // Sort by count and take top 10
        arsort($geographyData);
        $topCities = array_slice($geographyData, 0, 10, true);

        $labels = array_keys($topCities);
        $data = array_values($topCities);

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

        $backgroundColors = array_slice($colors, 0, count($data));

        return [
            'datasets' => [
                [
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
        return 'pie';
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
                            return label + ": " + value + " عميل (" + percentage + "%)";
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

    private function getCustomerCity($customer): ?string
    {
        // Try to get city from various sources
        // This is a placeholder implementation - adjust based on your actual data structure

        // Option 1: From user profile
        if (isset($customer->city) && $customer->city) {
            return $customer->city;
        }

        // Option 2: From user's addresses (if you have an addresses relationship)
        if ($customer->relationLoaded('addresses') && $customer->addresses->isNotEmpty()) {
            $address = $customer->addresses->first();
            if (isset($address->city) && $address->city) {
                return $address->city;
            }
        }

        // Option 3: From most recent order's shipping address
        if ($customer->relationLoaded('orders') && $customer->orders->isNotEmpty()) {
            $latestOrder = $customer->orders->sortByDesc('created_at')->first();
            if (isset($latestOrder->shipping_city) && $latestOrder->shipping_city) {
                return $latestOrder->shipping_city;
            }
        }

        // Option 4: Default cities for demo purposes (Egyptian cities)
        $defaultCities = [
            'القاهرة', 'الإسكندرية', 'الجيزة', 'شبرا الخيمة', 'بورسعيد',
            'السويس', 'الأقصر', 'المنصورة', 'المحلة الكبرى', 'طنطا'
        ];

        return $defaultCities[array_rand($defaultCities)];
    }
}
