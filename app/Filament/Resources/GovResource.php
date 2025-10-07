<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GovResource\Pages;
use App\Models\Gov;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GovResource extends Resource
{
    protected static ?string $model = Gov::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $recordTitleAttribute = 'name_ar';

    protected static ?string $label = 'المحافظة';

    protected static ?string $pluralLabel = 'المحافظات';

    protected static ?string $navigationGroup = 'إدارة المناطق';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name_en',
            'name_ar',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Gov $record */
        return [
            'الاسم بالإنجليزية' => $record->name_en,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المحافظة')
                    ->schema([
                        Forms\Components\TextInput::make('name_ar')
                            ->label('اسم المحافظة بالعربية')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('name_en')
                            ->label('اسم المحافظة بالإنجليزية')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
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
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('name_en')
                    ->label('الاسم بالإنجليزية')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('areas_count')
                    ->label('عدد المناطق')
                    ->counts('areas')
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
                Tables\Filters\Filter::make('has_areas')
                    ->label('لديها مناطق')
                    ->query(fn ($query) => $query->has('areas')),
                Tables\Filters\Filter::make('no_areas')
                    ->label('بدون مناطق')
                    ->query(fn ($query) => $query->doesntHave('areas')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Gov $record) {
                        // Check if governorate has areas before deletion
                        if ($record->areas()->count() > 0) {
                            throw new \Exception('لا يمكن حذف المحافظة لأنها تحتوي على مناطق');
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->areas()->count() > 0) {
                                    throw new \Exception('لا يمكن حذف محافظة تحتوي على مناطق');
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('name_ar')
            ->striped();
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
            'index' => Pages\ListGovs::route('/'),
            'create' => Pages\CreateGov::route('/create'),
            'view' => Pages\ViewGov::route('/{record}'),
            'edit' => Pages\EditGov::route('/{record}/edit'),
        ];
    }
}
