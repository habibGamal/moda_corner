<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsagesRelationManager extends RelationManager
{
    protected static string $relationship = 'usages';

    protected static ?string $pluralLabel = 'استخدامات العرض';

    protected static ?string $label = 'استخدام';

    protected static ?string $title = 'استخدامات العرض';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('المستخدم')
                    ->options(fn () => User::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(),

                Forms\Components\Select::make('order_id')
                    ->label('الطلب')
                    ->options(fn () => Order::query()->pluck('id', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(),

                Forms\Components\TextInput::make('discount_amount')
                    ->label('قيمة الخصم')
                    ->numeric()
                    ->prefix('ج.م')
                    ->required()
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('order_id')
                    ->label('رقم الطلب')
                    ->url(fn ($record) => route('filament.admin.resources.orders.edit', $record->order_id))
                    ->searchable(),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('قيمة الخصم')
                    ->money('EGP'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الاستخدام')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No create action for usage history
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for usage history
            ]);
    }
}
