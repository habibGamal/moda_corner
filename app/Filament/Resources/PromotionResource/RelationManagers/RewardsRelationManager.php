<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Enums\PromotionRewardType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    protected static ?string $pluralLabel = 'مكافآت العرض';

    protected static ?string $label = 'مكافأة';

    protected static ?string $title = 'مكافآت العرض';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع المكافأة')
                    ->options(PromotionRewardType::toSelectArray())
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر المنتج')
                    ->options(fn () => Product::query()->pluck('name_ar', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionRewardType::PRODUCT->value),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر الفئة')
                    ->options(fn () => Category::query()->pluck('name_ar', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionRewardType::CATEGORY->value),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر العلامة التجارية')
                    ->options(fn () => Brand::query()->pluck('name_ar', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionRewardType::BRAND->value),

                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية المجانية / المخفضة')
                    ->helperText('عدد القطع التي سيتم تطبيق الخصم عليها')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->default(1),

                Forms\Components\TextInput::make('discount_percentage')
                    ->label('نسبة الخصم')
                    ->helperText('اتركه فارغًا للحصول على المنتج مجانًا (100٪)')
                    ->numeric()
                    ->suffix('%')
                    ->minValue(1)
                    ->maxValue(100)
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge(),

                Tables\Columns\TextColumn::make('entity_name')
                    ->label('الاسم')
                    ->getStateUsing(function ($record) {
                        return match ($record->type) {
                            PromotionRewardType::PRODUCT => Product::find($record->entity_id)?->name_ar ?? 'غير موجود',
                            PromotionRewardType::CATEGORY => Category::find($record->entity_id)?->name_ar ?? 'غير موجود',
                            PromotionRewardType::BRAND => Brand::find($record->entity_id)?->name_ar ?? 'غير موجود',
                            default => 'غير موجود',
                        };
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية'),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('نسبة الخصم')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}%" : "100% (مجاني)")
                    ->placeholder('100% (مجاني)'),
            ])
            ->filters([
                //
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
