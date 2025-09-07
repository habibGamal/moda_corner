<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AreaResource\Pages;
use App\Filament\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use App\Models\Gov;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AreaResource extends Resource
{
    protected static ?string $model = Area::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $label = 'المنطقة';
    protected static ?string $pluralLabel = 'المناطق';

    protected static ?string $navigationGroup = 'إدارة المناطق';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name_en',
            'name_ar',
            'gov.name_en',
            'gov.name_ar',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Area $record */
        return [
            'المحافظة' => $record->gov?->name_ar ?? $record->gov?->name_en,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المنطقة')
                    ->schema([
                        Forms\Components\Select::make('gov_id')
                            ->label('المحافظة')
                            ->relationship('gov', 'name_ar')
                            ->getOptionLabelFromRecordUsing(fn (Gov $record): string => "{$record->name_ar} ({$record->name_en})")
                            ->searchable(['name_ar', 'name_en'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name_ar')
                                    ->label('الاسم بالعربية')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name_en')
                                    ->label('الاسم بالإنجليزية')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم المنطقة بالعربية')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name_en')
                            ->label('اسم المنطقة بالإنجليزية')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الاسم بالعربية')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gov.name_ar')
                    ->label('المحافظة')
                    ->sortable()
                    ->searchable()
                    ->description(fn (Area $record): string => $record->gov?->name_en ?? ''),
                Tables\Columns\TextColumn::make('addresses_count')
                    ->label('عدد العناوين')
                    ->counts('addresses')
                    ->sortable(),
                Tables\Columns\TextColumn::make('shippingCost.value')
                    ->label('تكاليف الشحن')
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
                Tables\Filters\SelectFilter::make('gov_id')
                    ->label('المحافظة')
                    ->relationship('gov', 'name_ar')
                    ->getOptionLabelFromRecordUsing(fn (Gov $record): string => "{$record->name_ar} ({$record->name_en})")
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name_ar');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ShippingCostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'view' => Pages\ViewArea::route('/{record}'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
