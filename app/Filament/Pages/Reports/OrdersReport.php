<?php

namespace App\Filament\Pages\Reports;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class OrdersReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string $routePath = 'orders-report';

    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationLabel = 'تقرير الطلبات';

    protected static ?string $title = 'تقرير الطلبات والمبيعات';

    protected static ?int $navigationSort = 1;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('فترة التقرير')
                    ->description('اختر الفترة الزمنية لتحليل الطلبات')
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('تاريخ البداية')
                            ->default(now()->subMonth())
                            ->maxDate(now()),
                        DatePicker::make('endDate')
                            ->label('تاريخ النهاية')
                            ->default(now())
                            ->maxDate(now()),
                        \Filament\Forms\Components\Select::make('orderStatus')
                            ->label('حالة الطلب')
                            ->multiple()
                            ->options([
                                'processing' => 'قيد المعالجة',
                                'shipped' => 'تم الشحن',
                                'delivered' => 'تم التوصيل',
                                'cancelled' => 'ملغاة',
                            ])
                            ->placeholder('جميع الحالات'),
                    ])
                    ->columns(3),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\OrdersSalesOverview::class,
            \App\Filament\Widgets\OrdersInsightsOverview::class,
            \App\Filament\Widgets\OrdersRevenueChart::class,
            \App\Filament\Widgets\TopProductsChart::class,
            \App\Filament\Widgets\OrdersStatusChart::class,
            \App\Filament\Widgets\PaymentMethodsChart::class,
            \App\Filament\Widgets\LatestOrders::class,
        ];
    }
}
