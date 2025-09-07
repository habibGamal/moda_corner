<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class LatestProducts extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أحدث المنتجات';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular()
                    ->size(50)
                    ->default('/placeholder.jpg'),

                TextColumn::make('name_ar')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable()
                    ->default('غير محدد')
                    ->limit(30),
                TextColumn::make('category.name_ar')
                    ->label('الفئة')
                    ->badge()
                    ->color('info')
                    ->default('غير محدد'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('is_active')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn(bool $state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'نشط' : 'غير نشط'),
                TextColumn::make('order_items_sum_quantity')
                    ->label('المبيعات')
                    ->sortable()
                    ->alignCenter()
                    ->default(0)
                    ->formatStateUsing(fn($state) => number_format($state ?? 0)),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
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
        $category = $this->filters['category'] ?? [];
        $status = $this->filters['status'] ?? [];

        $query = \App\Models\Product::query()
            ->with(['category'])
            ->withSum([
                'orderItems' => function ($query) use ($startDate, $endDate) {
                    $query->whereHas('order', function ($orderQuery) use ($startDate, $endDate) {
                        $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
                    });
                }
            ], 'quantity');

        // Apply filters
        if (!empty($category)) {
            $query->whereIn('category_id', $category);
        }

        if (!empty($status)) {
            if (in_array('active', $status)) {
                $query->where('is_active', true);
            }
            if (in_array('inactive', $status)) {
                $query->where('is_active', false);
            }
        }

        // Apply date filter for product creation
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query;
    }
}
