<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for orders as they are created through frontend
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('جميع الطلبات')
                ->icon('heroicon-o-squares-2x2')
                ->badge(Order::count()),

            'processing' => Tab::make('قيد المعالجة')
                ->icon('heroicon-o-cog')
                ->badge(Order::processing()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->processing()),

            'delivery' => Tab::make('قيد التوصيل')
                ->icon('heroicon-o-truck')
                ->badge(Order::delivery()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->delivery()),

            'completed' => Tab::make('مكتملة')
                ->icon('heroicon-o-check-circle')
                ->badge(Order::completed()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed()),

            'cancelled' => Tab::make('ملغاة')
                ->icon('heroicon-o-x-circle')
                ->badge(Order::cancelled()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->cancelled()),

            'returns' => Tab::make('المرتجعات')
                ->icon('heroicon-o-arrow-uturn-left')
                ->badge(Order::returns()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->returns()),
        ];
    }
}
