<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'عناصر الطلب';

    protected static ?string $label = 'عنصر الطلب';
    protected static ?string $pluralLabel = 'عناصر الطلب';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product.name_en')
                    ->label('اسم المنتج (الإنجليزية)')
                    ->disabled(),

                Forms\Components\TextInput::make('product.name_ar')
                    ->label('اسم المنتج (العربية)')
                    ->disabled(),

                Forms\Components\TextInput::make('variant_details')
                    ->label('المتغير')
                    ->visible(fn ($record) => $record->variant_id !== null)
                    ->formatStateUsing(function ($record) {
                        if (!$record->variant) return null;

                        $variantDetails = [];

                        if ($record->variant->color) {
                            $variantDetails[] = 'اللون: ' . $record->variant->color;
                        }

                        if ($record->variant->size) {
                            $variantDetails[] = 'الحجم: ' . $record->variant->size;
                        }

                        if ($record->variant->capacity) {
                            $variantDetails[] = 'السعة: ' . $record->variant->capacity;
                        }

                        return !empty($variantDetails) ? implode(' | ', $variantDetails) : null;
                    })
                    ->disabled(),

                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية')
                    ->disabled(),

                Forms\Components\TextInput::make('unit_price')
                    ->label('سعر الوحدة')
                    ->prefix('ج.م')
                    ->disabled(),

                Forms\Components\TextInput::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->prefix('ج.م')
                    ->disabled(),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('المعرّف')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('variant.images.0')
                    ->height(100)
                    ->label('صورة المنتج'),

                Tables\Columns\TextColumn::make('product.name_en')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('variant')
                    ->label('المتغير')
                    ->getStateUsing(function ($record) {
                        if (!$record->variant) return null;

                        $variantDetails = [];

                        if ($record->variant->color) {
                            $variantDetails[] = 'اللون: ' . $record->variant->color;
                        }

                        if ($record->variant->size) {
                            $variantDetails[] = 'الحجم: ' . $record->variant->size;
                        }

                        if ($record->variant->capacity) {
                            $variantDetails[] = 'السعة: ' . $record->variant->capacity;
                        }

                        return !empty($variantDetails) ? implode(' | ', $variantDetails) : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('سعر الوحدة')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('المجموع الفرعي')
                    ->money('EGP')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // No actions for adding items - they come from frontend
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions needed
            ]);
    }
}
