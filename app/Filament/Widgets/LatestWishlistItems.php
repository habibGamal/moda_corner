<?php

namespace App\Filament\Widgets;

use App\Models\WishlistItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestWishlistItems extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'أحدث العناصر المضافة لقوائم الأمنيات';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                WishlistItem::query()
                    ->with(['user', 'product', 'variant'])
                    ->when($this->filters['startDate'] ?? null, fn ($query, $date) => $query->where('wishlist_items.created_at', '>=', $date))
                    ->when($this->filters['endDate'] ?? null, fn ($query, $date) => $query->where('wishlist_items.created_at', '<=', $date))
                    ->latest()
            )
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('product.name_ar')
                    ->label('المنتج')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (WishlistItem $record): ?string {
                        return $record->product?->name_ar;
                    }),

                Tables\Columns\TextColumn::make('variant.name_ar')
                    ->label('التنويع')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('الأساسي')
                    ->limit(20),

                Tables\Columns\TextColumn::make('product.price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_purchased')
                    ->label('تم الشراء')
                    ->boolean()
                    ->getStateUsing(function (WishlistItem $record): bool {
                        return \App\Models\OrderItem::query()
                            ->join('orders', 'order_items.order_id', '=', 'orders.id')
                            ->where('order_items.product_id', $record->product_id)
                            ->where('orders.user_id', $record->user_id)
                            ->where(function ($query) use ($record) {
                                if ($record->product_variant_id) {
                                    $query->where('order_items.variant_id', $record->product_variant_id);
                                }
                            })
                            ->where('orders.created_at', '>=', $record->created_at)
                            ->exists();
                    })
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('عرض المنتج')
                    ->icon('heroicon-o-eye')
                    ->url(fn (WishlistItem $record): string => route('filament.admin.resources.products.view', $record->product))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('view_user')
                    ->label('عرض العميل')
                    ->icon('heroicon-o-user')
                    ->url(fn (WishlistItem $record): string => route('filament.admin.resources.users.edit', $record->user))
                    ->openUrlInNewTab(),
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
                                $subQuery->select(\Illuminate\Support\Facades\DB::raw(1))
                                    ->from('order_items')
                                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                    ->whereColumn('order_items.product_id', 'wishlist_items.product_id')
                                    ->whereColumn('orders.user_id', 'wishlist_items.user_id')
                                    ->where(function ($q) {
                                        $q->whereNull('wishlist_items.product_variant_id')
                                            ->orWhereColumn('order_items.variant_id', '=', 'wishlist_items.product_variant_id');
                                    })
                                    ->where('orders.created_at', '>=', \Illuminate\Support\Facades\DB::raw('wishlist_items.created_at'));
                            });
                        } elseif ($value === '0') {
                            return $query->whereNotExists(function ($subQuery) {
                                $subQuery->select(\Illuminate\Support\Facades\DB::raw(1))
                                    ->from('order_items')
                                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                                    ->whereColumn('order_items.product_id', 'wishlist_items.product_id')
                                    ->whereColumn('orders.user_id', 'wishlist_items.user_id')
                                    ->where(function ($q) {
                                        $q->whereNull('wishlist_items.product_variant_id')
                                            ->orWhereColumn('order_items.variant_id', '=', 'wishlist_items.product_variant_id');
                                    })
                                    ->where('orders.created_at', '>=', \Illuminate\Support\Facades\DB::raw('wishlist_items.created_at'));
                            });
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
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('wishlist_items.created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('wishlist_items.created_at', '<=', $date));
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
