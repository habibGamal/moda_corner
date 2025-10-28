<?php

namespace App\Filament\Widgets;

use App\Models\ProductDesire;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ProductDesiresConversionChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'معدل التحويل من الرغبة للشراء';

    protected static ?string $description = 'مقارنة الرغبات المحولة لشراء فعلي مقابل غير المحولة';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $categoryId = $this->filters['categoryId'] ?? null;

        $baseQuery = ProductDesire::query()
            ->when($startDate, fn ($query) => $query->where('product_desires.created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('product_desires.created_at', '<=', $endDate))
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->join('products', 'product_desires.product_id', '=', 'products.id')
                    ->where('products.category_id', $categoryId);
            });

        // Total desires
        $totalDesires = $baseQuery->clone()->count();

        // Converted desires (purchased)
        $convertedDesires = $baseQuery->clone()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                    ->whereColumn('order_items.product_id', 'product_desires.product_id')
                    ->where(function ($q) {
                        $q->whereColumn('orders.user_id', 'product_desires.user_id')
                            ->orWhereColumn('users.email', 'product_desires.email');
                    })
                    ->where('orders.created_at', '>=', DB::raw('product_desires.created_at'));
            })
            ->count();

        // Non-converted desires
        $nonConvertedDesires = $totalDesires - $convertedDesires;

        // Calculate percentages
        $convertedPercentage = $totalDesires > 0 ? round(($convertedDesires / $totalDesires) * 100, 1) : 0;
        $nonConvertedPercentage = $totalDesires > 0 ? round(($nonConvertedDesires / $totalDesires) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'حالة التحويل',
                    'data' => [$convertedDesires, $nonConvertedDesires],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',   // Green for converted
                        'rgba(245, 158, 11, 0.8)',   // Amber for non-converted
                    ],
                    'borderColor' => [
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
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
                            return label + ": " + value + " رغبة";
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
