<?php

namespace App\Filament\Pages\Reports;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class ProductsReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static string $routePath = 'products-report';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير المنتجات';

    protected static ?string $title = 'تقرير المنتجات والمبيعات';

    protected static ?int $navigationSort = 3;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فترة التقرير')
                    ->description('اختر الفترة الزمنية لتحليل المنتجات')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاريخ البداية')
                            ->default(now()->subMonth())
                            ->maxDate(now()),
                        DatePicker::make('endDate')
                            ->label('تاريخ النهاية')
                            ->default(now())
                            ->maxDate(now()),                        \Filament\Forms\Components\Select::make('category')
                            ->label('الفئة')
                            ->multiple()
                            ->options(function() {
                                return \App\Models\Category::pluck('name_ar', 'id')->toArray();
                            })
                            ->placeholder('جميع الفئات'),
                        \Filament\Forms\Components\Select::make('status')
                            ->label('حالة المنتج')
                            ->multiple()
                            ->options([
                                'active' => 'نشط',
                                'inactive' => 'غير نشط',
                            ])
                            ->placeholder('جميع الحالات'),
                    ])
                    ->columns(4),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ProductsSalesOverview::class,
            \App\Filament\Widgets\ProductsInsightsOverview::class,
            \App\Filament\Widgets\ProductsPerformanceChart::class,
            \App\Filament\Widgets\ProductsCategoriesChart::class,
            // \App\Filament\Widgets\TopSellingProductsChart::class,
            // \App\Filament\Widgets\ProductsInventoryChart::class,
            \App\Filament\Widgets\LatestProducts::class,
        ];
    }
}
