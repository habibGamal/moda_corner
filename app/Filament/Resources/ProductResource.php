<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'المنتجات';

    protected static ?int $navigationSort = 0;

    protected static ?string $label = 'المنتج';

    protected static ?string $pluralLabel = 'المنتجات';

    protected static ?string $recordTitleAttribute = 'name_'.'ar';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name_en', 'name_ar', 'slug', 'description_en', 'description_ar'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('المعلومات الأساسية')
                            ->schema([
                                Forms\Components\TextInput::make('name_en')
                                    ->label('الاسم باللغة الإنجليزية')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم باللغة العربية')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->label('الرابط')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                Forms\Components\Select::make('category_id')
                                    ->label('الفئة')
                                    ->relationship('category', 'name_'.app()->getLocale())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('brand_id')
                                    ->label('العلامة التجارية')
                                    ->relationship('brand', 'name_'.app()->getLocale())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('مميز')
                                    ->default(false),
                                Forms\Components\TextInput::make('special_label')
                                    ->label('تصنيف خاص')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->placeholder('مثال: محدود، EVA، جديد'),
                            ]),
                        Forms\Components\Tabs\Tab::make('الوصف')
                            ->schema([
                                Forms\Components\Textarea::make('description_en')
                                    ->label('الوصف باللغة الإنجليزية')
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('description_ar')
                                    ->label('الوصف باللغة العربية')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('التسعير')
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('السعر')
                                    ->required()
                                    ->numeric()
                                    ->prefix('ج.م'),
                                Forms\Components\TextInput::make('sale_price')
                                    ->label('سعر العرض')
                                    ->numeric()
                                    ->nullable()
                                    ->lte('price')
                                    ->prefix('ج.م'),
                                Forms\Components\TextInput::make('cost_price')
                                    ->label('سعر التكلفة')
                                    ->numeric()
                                    ->nullable()
                                    ->prefix('ج.م'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_'.app()->getLocale())
                    ->label('الاسم')
                    ->sortable()
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('category.name_'.app()->getLocale())
                    ->label('الفئة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name_'.app()->getLocale())
                    ->label('العلامة التجارية')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('special_label')
                    ->label('تصنيف خاص')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('المخزون')
                    ->badge()
                    ->color(fn (Product $record): string => $record->is_in_stock ? 'success' : 'danger'),
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('الفئة')
                    ->relationship('category', 'name_'.app()->getLocale())
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('العلامة التجارية')
                    ->relationship('brand', 'name_'.app()->getLocale())
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('مميز')
                    ->placeholder('الكل')
                    ->trueLabel('مميز فقط')
                    ->falseLabel('غير مميز فقط'),
                Tables\Filters\TernaryFilter::make('is_in_stock')
                    ->label('المخزون')
                    ->placeholder('الكل')
                    ->trueLabel('متوفر فقط')
                    ->falseLabel('غير متوفر فقط')
                    ->attribute('is_in_stock'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء التفعيل')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('feature')
                        ->label('تمييز')
                        ->icon('heroicon-o-star')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('unfeature')
                        ->label('إلغاء التمييز')
                        ->icon('heroicon-o-no-symbol')
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }
}
