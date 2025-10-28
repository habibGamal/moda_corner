<?php

namespace App\Filament\Widgets;

use App\Models\ProductDesire;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class LatestProductDesires extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أحدث الرغبات المسجلة';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductDesire::query()
                    ->with(['user', 'product'])
                    ->when($this->filters['startDate'] ?? null, fn ($query, $date) => $query->where('product_desires.created_at', '>=', $date))
                    ->when($this->filters['endDate'] ?? null, fn ($query, $date) => $query->where('product_desires.created_at', '<=', $date))
                    ->when($this->filters['categoryId'] ?? null, function ($query, $categoryId) {
                        $query->whereHas('product', fn ($q) => $q->where('category_id', $categoryId));
                    })
                    ->latest()
            )
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('product.name_ar')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (ProductDesire $record): ?string {
                        return $record->product?->name_ar;
                    }),

                Tables\Columns\TextColumn::make('product.category.name_ar')
                    ->label('التصنيف')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable()
                    ->placeholder('زائر')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->limit(30)
                    ->copyable(),

                Tables\Columns\TextColumn::make('product.price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('desire_count')
                    ->label('عدد الرغبات')
                    ->getStateUsing(function (ProductDesire $record): int {
                        return ProductDesire::query()
                            ->where('email', $record->email)
                            ->where('product_id', $record->product_id)
                            ->count();
                    })
                    ->badge()
                    ->color(fn (int $state): string => $state > 2 ? 'danger' : ($state > 1 ? 'warning' : 'info'))
                    ->sortable(query: function ($query, string $direction): void {
                        $query->withCount(['product as desire_count' => function ($q) {
                            $q->select(DB::raw('COUNT(*)'));
                        }])
                            ->orderBy('desire_count', $direction);
                    }),

                Tables\Columns\IconColumn::make('is_purchased')
                    ->label('تم الشراء')
                    ->boolean()
                    ->getStateUsing(function (ProductDesire $record): bool {
                        return \App\Models\OrderItem::query()
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                            ->where('order_items.product_id', $record->product_id)
                            ->where(function ($query) use ($record) {
                                if ($record->user_id) {
                                    $query->where('orders.user_id', $record->user_id);
                                }
                                $query->orWhere('users.email', $record->email);
                            })
                            ->where('orders.created_at', '>=', $record->created_at)
                            ->exists();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('عرض المنتج')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductDesire $record): string => route('filament.admin.resources.products.view', $record->product))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_user')
                    ->label('عرض العميل')
                    ->icon('heroicon-o-user')
                    ->url(fn (ProductDesire $record): ?string => $record->user_id
                        ? route('filament.admin.resources.users.edit', $record->user)
                        : null)
                    ->visible(fn (ProductDesire $record): bool => $record->user_id !== null)
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_similar_desires')
                    ->label('رغبات مشابهة')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (ProductDesire $record) {
                        // This will filter the table to show desires for the same product
                        $this->tableFilters['product_id'] = $record->product_id;
                    })
                    ->modalHeading(fn (ProductDesire $record): string => "رغبات المنتج: {$record->product->name_ar}")
                    ->modalContent(function (ProductDesire $record) {
                        $desires = ProductDesire::where('product_id', $record->product_id)
                            ->with('user')
                            ->latest()
                            ->limit(10)
                            ->get();

                        return view('filament.widgets.similar-desires-modal', ['desires' => $desires]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_purchased')
                    ->label('حالة الشراء')
                    ->options([
                        '1' => 'تم الشراء',
                        '0' => 'لم يتم الشراء',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === '1') {
                            return $query->whereExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                    ->from('order_items')
                                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                    ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                                    ->whereColumn('order_items.product_id', 'product_desires.product_id')
                                    ->where(function ($q) {
                                        $q->whereColumn('orders.user_id', 'product_desires.user_id')
                                            ->orWhereColumn('users.email', 'product_desires.email');
                                    })
                                    ->where('orders.created_at', '>=', DB::raw('product_desires.created_at'));
                            });
                        } elseif ($value === '0') {
                            return $query->whereNotExists(function ($subQuery) {
                                $subQuery->select(DB::raw(1))
                                    ->from('order_items')
                                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                    ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                                    ->whereColumn('order_items.product_id', 'product_desires.product_id')
                                    ->where(function ($q) {
                                        $q->whereColumn('orders.user_id', 'product_desires.user_id')
                                            ->orWhereColumn('users.email', 'product_desires.email');
                                    })
                                    ->where('orders.created_at', '>=', DB::raw('product_desires.created_at'));
                            });
                        }

                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('user_type')
                    ->label('نوع العميل')
                    ->options([
                        'registered' => 'مسجل',
                        'guest' => 'زائر',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        if ($value === 'registered') {
                            return $query->whereNotNull('user_id');
                        } elseif ($value === 'guest') {
                            return $query->whereNull('user_id');
                        }

                        return $query;
                    }),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('product_desires.created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('product_desires.created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
