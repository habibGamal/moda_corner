<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages;
use App\Models\HeroSlide;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'واجهة المستخدم';

    protected static ?int $navigationSort = 80;

    protected static ?string $label = 'شريحة العرض';

    protected static ?string $pluralLabel = 'شرائح العرض';

    protected static ?string $recordTitleAttribute = 'title_ar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title_en')
                            ->label('العنوان باللغة الإنجليزية')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title_ar')
                            ->label('العنوان باللغة العربية')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description_en')
                            ->label('الوصف باللغة الإنجليزية')
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\Textarea::make('description_ar')
                            ->label('الوصف باللغة العربية')
                            ->rows(3)
                            ->maxLength(500),

                        Forms\Components\FileUpload::make('image')
                            ->label('الصورة')
                            ->image()
                            ->required()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                            ])
                            ->optimize('webp')
                            ->directory('hero-slides'),

                        Forms\Components\TextInput::make('cta_link')
                            ->label('رابط الدعوة للعمل')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('display_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('الصورة')
                    ->width(150)
                    ->height(100),

                Tables\Columns\TextColumn::make('title_en')
                    ->label('العنوان باللغة الإنجليزية')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان باللغة العربية')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cta_link')
                    ->label('رابط الدعوة للعمل')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('ترتيب العرض')
                    ->numeric()
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
            ->defaultSort('display_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit' => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
