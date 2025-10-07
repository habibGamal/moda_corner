<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestCustomers extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أحدث العملاء';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد'),

                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد'),

                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->default('غير محدد'),

                TextColumn::make('orders_count')
                    ->label('عدد الطلبات')
                    ->sortable()
                    ->alignCenter()
                    ->default(0),                TextColumn::make('orders_sum_total')
                    ->label('إجمالي الإنفاق')
                    ->sortable()
                    ->alignEnd()
                    ->money('EGP')
                    ->default(0),                TextColumn::make('last_order_date')
                    ->label('آخر طلب')
                    ->getStateUsing(function ($record) {
                        $lastOrder = $record->orders()->latest()->first();

                        return $lastOrder ? $lastOrder->created_at->format('d/m/Y H:i') : 'لا يوجد';
                    })
                    ->sortable()
                    ->default('لا يوجد'),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->extremePaginationLinks()
            ->poll('30s');
    }

    protected function getTableQuery(): Builder
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $customerType = $this->filters['customerType'] ?? [];

        $query = \App\Models\User::query()
            ->withCount(['orders' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])->withSum(['orders' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total')
            ->with(['orders' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->latest()
                    ->limit(1);
            }]);

        // Apply date filter for customer registration
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }        // Apply customer type filter if needed
        if (! empty($customerType)) {
            // Add customer type filtering logic here based on your implementation
            // Example: $query->whereIn('customer_type', $customerType);
        }

        return $query;
    }
}
