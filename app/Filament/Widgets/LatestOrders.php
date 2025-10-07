<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestOrders extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أحدث الطلبات';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->prefix('#'),

                TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                TextColumn::make('total')
                    ->label('إجمالي المبلغ')
                    ->money('EGP')
                    ->sortable()
                    ->alignEnd(),

                BadgeColumn::make('order_status')
                    ->label('حالة الطلب')
                    ->formatStateUsing(fn ($state): string => $state ? $state->getLabel() : 'غير محدد')
                    ->color(fn ($state): string => $state ? $state->getColor() : 'gray'),

                BadgeColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->formatStateUsing(fn ($state): string => $state ? $state->getLabel() : 'غير محدد')
                    ->color(fn ($state): string => $state ? $state->getColor() : 'gray'),

                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn ($state): string => $state ? $state->getLabel() : 'غير محدد')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn (Order $record): string => $record->created_at->format('d/m/Y H:i')),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Note: Add view action when Orders resource is created
                // Tables\Actions\ViewAction::make()
                //     ->label('عرض')
                //     ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record)),
            ])
            ->bulkActions([
                //
            ])
            ->poll('30s');
    }

    protected function getTableQuery(): Builder
    {
        $startDate = $this->filters['startDate'] ?? now()->subMonth();
        $endDate = $this->filters['endDate'] ?? now();
        $orderStatus = $this->filters['orderStatus'] ?? [];

        return Order::query()
            ->with(['user'])
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate))
            ->when(! empty($orderStatus), fn ($query) => $query->whereIn('order_status', $orderStatus))
            ->latest()
            ->limit(50);
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25, 50];
    }
}
