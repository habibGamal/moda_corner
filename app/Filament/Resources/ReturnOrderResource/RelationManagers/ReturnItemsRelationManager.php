<?php

namespace App\Filament\Resources\ReturnOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReturnItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'returnItems';

    protected static ?string $title = 'Return Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_item_id')
                    ->relationship('orderItem', 'id')
                    ->required()
                    ->disabled(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->disabled(),
                Forms\Components\Textarea::make('reason')
                    ->maxLength(65535)
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('orderItem.product.name_en')
                    ->label('Product Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orderItem.variant.name_en')
                    ->label('Variant')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Disable creating return items from admin
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // Disable bulk actions
            ]);
    }

    public function isReadOnly(): bool
    {
        return true; // Return items should not be editable from admin
    }
}
