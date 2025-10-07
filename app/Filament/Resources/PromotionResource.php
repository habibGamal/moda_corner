<?php

namespace App\Filament\Resources;

use App\Enums\PromotionType;
use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'التسويق';

    protected static ?int $navigationSort = 10;

    protected static ?string $label = 'العرض الترويجي';

    protected static ?string $pluralLabel = 'العروض الترويجية';

    protected static ?string $recordTitleAttribute = 'name_'.'ar';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name_en', 'name_ar', 'code', 'description_en', 'description_ar'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Promotion')
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
                                Forms\Components\TextInput::make('code')
                                    ->label('كود الخصم')
                                    ->helperText('اتركه فارغًا للعروض التلقائية')
                                    ->maxLength(255)
                                    ->nullable()
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('type')
                                    ->label('نوع العرض')
                                    ->options(PromotionType::toSelectArray())
                                    ->required()
                                    ->reactive(),
                                Forms\Components\TextInput::make('value')
                                    ->label('القيمة')
                                    ->numeric()
                                    ->prefix(fn (Forms\Get $get) => $get('type') === PromotionType::PERCENTAGE->value ? '%' : 'ج.م')
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                                        PromotionType::PERCENTAGE->value,
                                        PromotionType::FIXED->value,
                                    ])
                                    )
                                    ->required(fn (Forms\Get $get) => in_array($get('type'), [
                                        PromotionType::PERCENTAGE->value,
                                        PromotionType::FIXED->value,
                                    ])
                                    ),
                                Forms\Components\TextInput::make('min_order_value')
                                    ->label('الحد الأدنى لقيمة الطلب')
                                    ->helperText('اتركه فارغًا إذا لم يكن هناك حد أدنى')
                                    ->numeric()
                                    ->prefix('ج.م')
                                    ->nullable(),
                                Forms\Components\TextInput::make('usage_limit')
                                    ->label('الحد الأقصى للاستخدام')
                                    ->helperText('اتركه فارغًا للاستخدام غير المحدود')
                                    ->numeric()
                                    ->minValue(1)
                                    ->nullable(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true),
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
                        Forms\Components\Tabs\Tab::make('تاريخ الصلاحية')
                            ->schema([
                                Forms\Components\DateTimePicker::make('starts_at')
                                    ->label('تاريخ البدء')
                                    ->nullable()
                                    ->helperText('اتركه فارغًا للبدء فورًا'),
                                Forms\Components\DateTimePicker::make('expires_at')
                                    ->label('تاريخ الانتهاء')
                                    ->nullable()
                                    ->helperText('اتركه فارغًا لعدم انتهاء الصلاحية')
                                    ->after('starts_at'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('الكود')
                    ->searchable()
                    ->sortable()
                    ->placeholder('تلقائي'),
                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->formatStateUsing(fn ($state, $record) => $record->type === PromotionType::PERCENTAGE->value
                            ? "{$state}%"
                            : ($record->type === PromotionType::FIXED->value ? "\${$state}" : '-')
                    ),
                Tables\Columns\TextColumn::make('usage_count')
                    ->label('عدد الاستخدامات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('غير محدد'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('نشط')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options(PromotionType::toSelectArray()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),
                Tables\Filters\TernaryFilter::make('has_code')
                    ->label('له كود')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('code'),
                        false: fn ($query) => $query->whereNull('code'),
                    ),
                Tables\Filters\Filter::make('expired')
                    ->label('منتهي الصلاحية')
                    ->query(fn ($query) => $query->where('expires_at', '<', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggleActive')
                        ->label('تبديل الحالة')
                        ->icon('heroicon-o-power')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => ! $record->is_active]);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ConditionsRelationManager::class,
            RelationManagers\RewardsRelationManager::class,
            RelationManagers\UsagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
