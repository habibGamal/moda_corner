<?php

namespace App\Filament\Resources\PromotionResource\RelationManagers;

use App\Enums\PromotionConditionType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ConditionsRelationManager extends RelationManager
{
    protected static string $relationship = 'conditions';

    protected static ?string $pluralLabel = 'شروط العرض';

    protected static ?string $label = 'شرط';

    protected static ?string $title = 'شروط العرض';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('نوع الشرط')
                    ->options(PromotionConditionType::toSelectArray())
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر المنتج')
                    ->options(fn () => Product::query()->pluck('name_ar', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionConditionType::PRODUCT->value),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر الفئة')
                    ->options(fn () => Category::query()->pluck('name_ar', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionConditionType::CATEGORY->value),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر العلامة التجارية')
                    ->options(fn () => Brand::query()->pluck('name_ar', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionConditionType::BRAND->value),

                Forms\Components\Select::make('entity_id')
                    ->label('اختر العميل')
                    ->options(fn () => User::query()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (Forms\Get $get) => $get('type') === PromotionConditionType::CUSTOMER->value),

                Forms\Components\TextInput::make('quantity')
                    ->label('الكمية المطلوبة')
                    ->helperText('اتركه فارغًا إذا لم يكن هناك كمية محددة مطلوبة')
                    ->numeric()
                    ->minValue(1)
                    ->nullable()
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                        PromotionConditionType::PRODUCT->value,
                        PromotionConditionType::CATEGORY->value,
                        PromotionConditionType::BRAND->value,
                    ])),
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
                            PromotionConditionType::PRODUCT => Product::find($record->entity_id)?->name_ar ?? 'غير موجود',
                            PromotionConditionType::CATEGORY => Category::find($record->entity_id)?->name_ar ?? 'غير موجود',
                            PromotionConditionType::BRAND => Brand::find($record->entity_id)?->name_ar ?? 'غير موجود',
                            PromotionConditionType::CUSTOMER => User::find($record->entity_id)?->name ?? 'غير موجود',
                            default => 'غير موجود',
                        };
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('الكمية المطلوبة')
                    ->placeholder('غير محدد'),
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
