<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;    protected static ?string $navigationLabel = 'الإعدادات';

    protected static ?string $pluralModelLabel = 'الإعدادات';

    protected static ?string $modelLabel = 'إعداد';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([                Forms\Components\Section::make('المعلومات الأساسية')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('المفتاح')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('معرف فريد لهذا الإعداد'),

                        Forms\Components\Select::make('group')
                            ->label('المجموعة')
                            ->options([
                                'general' => 'عام',
                                'appearance' => 'المظهر',
                                'seo' => 'تحسين محركات البحث',
                                'social' => 'وسائل التواصل الاجتماعي',
                                'analytics' => 'التحليلات',
                                'contact' => 'الاتصال',
                                'email' => 'البريد الإلكتروني',
                                'payment' => 'الدفع',
                                'legal' => 'قانوني',
                            ])
                            ->required()
                            ->default('general'),

                        Forms\Components\Select::make('type')
                            ->label('النوع')
                            ->options([
                                'text' => 'نص',
                                'textarea' => 'نص متعدد الأسطر',
                                'url' => 'رابط',
                                'image' => 'صورة',
                                'boolean' => 'منطقي (صحيح/خطأ)',
                                'json' => 'JSON',
                                'integer' => 'رقم صحيح',
                                'float' => 'رقم عشري',
                            ])
                            ->required()
                            ->default('text')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('value', null)),

                        Forms\Components\TextInput::make('display_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0)
                            ->helperText('ترتيب العرض ضمن المجموعة'),

                        Forms\Components\Toggle::make('is_required')
                            ->label('مطلوب')
                            ->default(false)
                            ->helperText('ما إذا كان هذا الإعداد مطلوباً'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('التسميات والأوصاف')
                    ->schema([
                        Forms\Components\TextInput::make('label_en')
                            ->label('التسمية (الإنجليزية)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('label_ar')
                            ->label('التسمية (العربية)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description_en')
                            ->label('الوصف (الإنجليزية)')
                            ->rows(2),

                        Forms\Components\Textarea::make('description_ar')
                            ->label('الوصف (العربية)')
                            ->rows(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('القيمة')
                    ->schema(function (Forms\Get $get) {
                        return match ($get('type')) {
                            'text', 'url', 'integer', 'float' => [
                                Forms\Components\TextInput::make('value')
                                    ->label('قيمة النص')
                                    ->required($get('is_required')),
                            ],
                            'textarea' => [
                                Forms\Components\Textarea::make('value')
                                    ->label('قيمة النص المتعدد')
                                    ->rows(4)
                                    ->required($get('is_required')),
                            ],
                            'image' => [
                                Forms\Components\FileUpload::make('value')
                                    ->label('الصورة')
                                    ->image()
                                    ->directory('settings')
                                    ->imageEditor()
                                    ->required($get('is_required')),
                            ],
                            'boolean' => [
                                Forms\Components\Toggle::make('value')
                                    ->label('القيمة المنطقية'),
                            ],
                            'json' => [
                                Forms\Components\KeyValue::make('value')
                                    ->label('قيمة JSON')
                                    ->required($get('is_required')),
                            ],
                            default => [
                                Forms\Components\TextInput::make('value')
                                    ->label('القيمة')
                                    ->required($get('is_required')),
                            ],
                        };
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('المفتاح')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group')
                    ->label('المجموعة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'general' => 'gray',
                        'appearance' => 'info',
                        'seo' => 'success',
                        'social' => 'warning',
                        'analytics' => 'danger',
                        'contact' => 'primary',
                        'legal' => 'slate',
                        default => 'gray',
                    })
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'general' => 'عام',
                        'appearance' => 'المظهر',
                        'seo' => 'تحسين محركات البحث',
                        'social' => 'وسائل التواصل',
                        'analytics' => 'التحليلات',
                        'contact' => 'الاتصال',
                        'email' => 'البريد الإلكتروني',
                        'payment' => 'الدفع',
                        'legal' => 'قانوني',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('label_ar')
                    ->label('التسمية')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'text' => 'نص',
                        'textarea' => 'نص متعدد',
                        'url' => 'رابط',
                        'image' => 'صورة',
                        'boolean' => 'منطقي',
                        'json' => 'JSON',
                        'integer' => 'رقم صحيح',
                        'float' => 'رقم عشري',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة')
                    ->limit(50)
                    ->formatStateUsing(function ($state, Setting $record) {
                        return match ($record->type) {
                            'boolean' => $state ? 'نعم' : 'لا',
                            'image' => $state ? 'تم رفع الصورة' : 'لا توجد صورة',
                            'json' => 'بيانات JSON',
                            default => $state,
                        };
                    }),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('مطلوب')
                    ->boolean(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('الترتيب')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('المجموعة')
                    ->options([
                        'general' => 'عام',
                        'appearance' => 'المظهر',
                        'seo' => 'تحسين محركات البحث',
                        'social' => 'وسائل التواصل الاجتماعي',
                        'analytics' => 'التحليلات',
                        'contact' => 'الاتصال',
                        'email' => 'البريد الإلكتروني',
                        'payment' => 'الدفع',
                        'legal' => 'قانوني',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'text' => 'نص',
                        'textarea' => 'نص متعدد الأسطر',
                        'url' => 'رابط',
                        'image' => 'صورة',
                        'boolean' => 'منطقي',
                        'json' => 'JSON',
                        'integer' => 'رقم صحيح',
                        'float' => 'رقم عشري',
                    ]),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('مطلوب'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group')
            ->defaultSort('display_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->ordered();
    }
}
