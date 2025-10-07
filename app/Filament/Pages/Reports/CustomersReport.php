<?php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class CustomersReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $routePath = 'customers-report';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير العملاء';

    protected static ?string $title = 'تقرير العملاء والسلوك الشرائي';

    protected static ?int $navigationSort = 2;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فترة التقرير')
                    ->description('اختر الفترة الزمنية لتحليل العملاء')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاريخ البداية')
                            ->default(now()->subMonth())
                            ->maxDate(now()),
                        DatePicker::make('endDate')
                            ->label('تاريخ النهاية')
                            ->default(now())
                            ->maxDate(now()),
                        \Filament\Forms\Components\Select::make('customerType')
                            ->label('نوع العميل')
                            ->multiple()
                            ->options([
                                'new' => 'عملاء جدد',
                                'returning' => 'عملاء عائدون',
                                'vip' => 'عملاء مميزون',
                            ])
                            ->placeholder('جميع العملاء'),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CustomersSalesOverview::class,
            \App\Filament\Widgets\CustomersInsightsOverview::class,
            \App\Filament\Widgets\CustomersGrowthChart::class,
            \App\Filament\Widgets\CustomersBehaviorChart::class,
            \App\Filament\Widgets\TopCustomersChart::class,
            // \App\Filament\Widgets\CustomersGeographyChart::class,
            \App\Filament\Widgets\LatestCustomers::class,
        ];
    }
}
