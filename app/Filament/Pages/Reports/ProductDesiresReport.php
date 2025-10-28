<?php

namespace App\Filament\Pages\Reports;

use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class ProductDesiresReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $routePath = 'product-desires-report';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير المنتجات المرغوبة';

    protected static ?string $title = 'تقرير المنتجات المرغوبة والطلب';

    protected static ?int $navigationSort = 3;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فترة التقرير')
                    ->description('اختر الفترة الزمنية لتحليل رغبات العملاء في المنتجات')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاريخ البداية')
                            ->default(now()->subMonth())
                            ->maxDate(now()),
                        DatePicker::make('endDate')
                            ->label('تاريخ النهاية')
                            ->default(now())
                            ->maxDate(now()),
                        Select::make('categoryId')
                            ->label('التصنيف')
                            ->options(Category::query()->orderBy('name_ar')->pluck('name_ar', 'id'))
                            ->searchable()
                            ->preload()
                            ->placeholder('جميع التصنيفات')
                            ->native(false),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ProductDesiresOverview::class,
            \App\Filament\Widgets\ProductDesiresTrendsChart::class,
            \App\Filament\Widgets\MostDesiredProductsChart::class,
            \App\Filament\Widgets\ProductDesiresConversionChart::class,
            \App\Filament\Widgets\LatestProductDesires::class,
        ];
    }
}
