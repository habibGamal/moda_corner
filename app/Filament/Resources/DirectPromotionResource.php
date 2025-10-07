<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DirectPromotionResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DirectPromotion;
use App\Services\DirectPromotionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DirectPromotionResource extends Resource
{
    protected static ?string $model = DirectPromotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'العروض والتسويق';

    protected static ?int $navigationSort = 1;

    protected static ?string $label = 'العرض المباشر';

    protected static ?string $pluralLabel = 'العروض المباشرة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات العرض الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم العرض (العربية)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_en')
                            ->label('اسم العرض (الإنجليزية)')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description_ar')
                            ->label('وصف العرض (العربية)')
                            ->rows(3),

                        Forms\Components\Textarea::make('description_en')
                            ->label('وصف العرض (الإنجليزية)')
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('نوع العرض')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('نوع العرض')
                            ->options([
                                'price_discount' => 'خصم على الأسعار',
                                'free_shipping' => 'شحن مجاني',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Clear fields when type changes
                                if ($state === 'price_discount') {
                                    $set('minimum_order_amount', null);
                                } else {
                                    $set('discount_percentage', null);
                                    $set('apply_to', null);
                                    $set('category_id', null);
                                    $set('brand_id', null);
                                }
                            }),
                    ]),

                // Price Discount Fields
                Forms\Components\Section::make('إعدادات خصم الأسعار')
                    ->schema([
                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('نسبة الخصم (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0.01)
                            ->maxValue(99.99)
                            ->step(0.01)
                            ->required(fn (Forms\Get $get) => $get('type') === 'price_discount'),

                        Forms\Components\Select::make('apply_to')
                            ->label('تطبيق الخصم على')
                            ->options([
                                'all_products' => 'جميع المنتجات',
                                'category' => 'فئة محددة',
                                'brand' => 'علامة تجارية محددة',
                            ])
                            ->required(fn (Forms\Get $get) => $get('type') === 'price_discount')
                            ->live(),

                        Forms\Components\Select::make('category_id')
                            ->label('الفئة')
                            ->options(Category::where('is_active', true)->pluck('name_ar', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('apply_to') === 'category')
                            ->required(fn (Forms\Get $get) => $get('apply_to') === 'category'),

                        Forms\Components\Select::make('brand_id')
                            ->label('العلامة التجارية')
                            ->options(Brand::where('is_active', true)->pluck('name_ar', 'id'))
                            ->searchable()
                            ->visible(fn (Forms\Get $get) => $get('apply_to') === 'brand')
                            ->required(fn (Forms\Get $get) => $get('apply_to') === 'brand'),
                    ])
                    ->columns(2),

                // Free Shipping Fields
                Forms\Components\Section::make('إعدادات الشحن المجاني')
                    ->schema([
                        Forms\Components\TextInput::make('minimum_order_amount')
                            ->label('الحد الأدنى لقيمة الطلب')
                            ->numeric()
                            ->prefix('ج.م')
                            ->minValue(0)
                            ->step(0.01)
                            ->required(fn (Forms\Get $get) => $get('type') === 'free_shipping'),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'free_shipping'),

                Forms\Components\Section::make('إعدادات التوقيت والحالة')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(false)
                            ->visible(fn (Forms\Get $get) => $get('type') !== 'price_discount')
                            ->helperText('تحذير: تفعيل عرض خصم الأسعار سيلغي جميع العروض الأخرى'),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('يبدأ في')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('ينتهي في')
                            ->nullable()
                            ->after('starts_at'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('اسم العرض')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'price_discount' => 'خصم الأسعار',
                        'free_shipping' => 'شحن مجاني',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'price_discount' => 'success',
                        'free_shipping' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('نسبة الخصم')
                    ->formatStateUsing(fn (?float $state): string => $state ? $state.'%' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('apply_to')
                    ->label('التطبيق على')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'all_products' => 'جميع المنتجات',
                        'category' => 'فئة محددة',
                        'brand' => 'علامة تجارية',
                        default => '-',
                    }),

                Tables\Columns\TextColumn::make('category.name_ar')
                    ->label('الفئة')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('brand.name_ar')
                    ->label('العلامة التجارية')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('minimum_order_amount')
                    ->label('الحد الأدنى للطلب')
                    ->formatStateUsing(fn (?float $state): string => $state ? '$'.number_format($state, 2) : '-')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('يبدأ في')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('فوري')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('ينتهي في')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('بلا نهاية')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'price_discount' => 'خصم الأسعار',
                        'free_shipping' => 'شحن مجاني',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
            ])
            ->actions([
                Tables\Actions\Action::make('apply_discount')
                    ->label('تطبيق الخصم')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (DirectPromotion $record) => $record->isPriceDiscount() && ! $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('تطبيق خصم الأسعار')
                    ->modalDescription('هذا سيقوم بتطبيق الخصم على أسعار المنتجات وإلغاء أي خصومات أخرى نشطة. هل أنت متأكد؟')
                    ->action(function (DirectPromotion $record) {
                        try {
                            $service = app(DirectPromotionService::class);
                            $result = $service->applyPriceDiscount($record);

                            Notification::make()
                                ->title('تم تطبيق الخصم بنجاح')
                                ->body("تم تطبيق الخصم على {$result['applied_count']} منتج")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في تطبيق الخصم')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('revert_discount')
                    ->label('إلغاء الخصم')
                    ->icon('heroicon-o-stop')
                    ->color('warning')
                    ->visible(fn (DirectPromotion $record) => $record->isPriceDiscount() && $record->is_active)
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء خصم الأسعار')
                    ->modalDescription('هذا سيقوم بإلغاء جميع خصومات الأسعار المطبقة حالياً. هل أنت متأكد؟')
                    ->action(function () {
                        try {
                            $service = app(DirectPromotionService::class);
                            $revertedCount = $service->revertPriceDiscounts();

                            Notification::make()
                                ->title('تم إلغاء الخصومات بنجاح')
                                ->body("تم إلغاء الخصم من {$revertedCount} منتج")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في إلغاء الخصومات')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (DirectPromotion $record) {
                        try {
                            $service = app(DirectPromotionService::class);
                            $service->deletePromotion($record);

                            Notification::make()
                                ->title('تم حذف العرض بنجاح')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في حذف العرض')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirectPromotions::route('/'),
            'create' => Pages\CreateDirectPromotion::route('/create'),
            'edit' => Pages\EditDirectPromotion::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
