<?php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class WishlistReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static string $routePath = 'wishlist-report';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير قائمة الأمنيات';

    protected static ?string $title = 'تقرير قائمة الأمنيات والاهتمامات';

    protected static ?int $navigationSort = 2;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فترة التقرير')
                    ->description('اختر الفترة الزمنية لتحليل قوائم الأمنيات')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاريخ البداية')
                            ->default(now()->subMonth())
                            ->maxDate(now()),
                        DatePicker::make('endDate')
                            ->label('تاريخ النهاية')
                            ->default(now())
                            ->maxDate(now()),
                        \Filament\Forms\Components\Select::make('userSegment')
                            ->label('شريحة العملاء')
                            ->multiple()
                            ->options([
                                'new' => 'عملاء جدد',
                                'returning' => 'عملاء عائدون',
                                'vip' => 'عملاء مميزون',
                                'inactive' => 'عملاء غير نشطين',
                            ])
                            ->placeholder('جميع الشرائح'),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\WishlistOverview::class,
            \App\Filament\Widgets\WishlistInsightsOverview::class,
            \App\Filament\Widgets\WishlistTrendsChart::class,
            \App\Filament\Widgets\PopularWishlistProductsChart::class,
            \App\Filament\Widgets\WishlistUserInsightsChart::class,
            \App\Filament\Widgets\WishlistConversionChart::class,
            \App\Filament\Widgets\LatestWishlistItems::class,
        ];
    }
}
