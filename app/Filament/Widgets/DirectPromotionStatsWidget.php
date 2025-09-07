<?php

namespace App\Filament\Widgets;

use App\Services\DirectPromotionService;
use Filament\Widgets\StatsOverviewWidget;

class DirectPromotionStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $service = app(DirectPromotionService::class);
        $stats = $service->getPromotionStats();

        return [
            StatsOverviewWidget\Stat::make('العروض النشطة', $stats['active_promotions'])
                ->description('إجمالي العروض المباشرة النشطة')
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('success'),

            StatsOverviewWidget\Stat::make('عروض خصم الأسعار', $stats['price_discount_promotions'])
                ->description('عروض خصم الأسعار النشطة')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),

            StatsOverviewWidget\Stat::make('عروض الشحن المجاني', $stats['free_shipping_promotions'])
                ->description('عروض الشحن المجاني النشطة')
                ->descriptionIcon('heroicon-m-truck')
                ->color('info'),

            StatsOverviewWidget\Stat::make('المنتجات المخفضة', $stats['variants_with_discount'])
                ->description('عدد المنتجات التي لديها سعر مخفض')
                ->descriptionIcon('heroicon-m-tag')
                ->color('success'),
        ];
    }
}
