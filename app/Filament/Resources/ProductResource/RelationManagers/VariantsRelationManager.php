<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    protected static ?string $pluralLabel = 'متغيرات المنتج';

    protected static ?string $label = 'متغير';

    protected static ?string $title = 'متغيرات المنتج';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->label('رمز المنتج (SKU)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('price')
                    ->label('السعر')
                    ->numeric()
                    ->prefix('ج.م'),

                Forms\Components\TextInput::make('sale_price')
                    ->label('سعر العرض')
                    ->numeric()
                    ->nullable()
                    ->lte('price')
                    ->prefix('ج.م'),

                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Forms\Components\TextInput::make('color')
                    ->label('اللون')
                    ->maxLength(255),

                Forms\Components\TextInput::make('size')
                    ->label('الحجم')
                    ->maxLength(255),

                Forms\Components\TextInput::make('capacity')
                    ->label('السعة')
                    ->maxLength(255),

                Forms\Components\KeyValue::make('additional_attributes')
                    ->label('خصائص إضافية')
                    ->keyLabel('الخاصية')
                    ->valueLabel('القيمة')
                    ->reorderable(),

                Forms\Components\FileUpload::make('images')
                    ->label('الصور')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->disk('public')
                    ->directory('product-variants')
                    ->visibility('public')
                    ->optimize('webp')
                    ->imageEditor()
                    ->maxFiles(5),

                Forms\Components\Toggle::make('is_default')
                    ->label('افتراضي')
                    ->default(false),

                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('رمز المنتج (SKU)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('الصورة')
                    ->circular(),

                Tables\Columns\TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('سعر العرض')
                    ->money('EGP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->quantity > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('color')
                    ->label('اللون')
                    ->searchable(),

                Tables\Columns\TextColumn::make('size')
                    ->label('الحجم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('السعة')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('افتراضي')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),

                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('افتراضي')
                    ->placeholder('الكل')
                    ->trueLabel('افتراضي فقط')
                    ->falseLabel('غير افتراضي فقط'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
