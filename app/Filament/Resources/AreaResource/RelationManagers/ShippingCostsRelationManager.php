<?php

namespace App\Filament\Resources\AreaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingCostsRelationManager extends RelationManager
{
    protected static string $relationship = 'shippingCosts';

    protected static ?string $title = 'تكلفة الشحن';

    protected static ?string $label = 'تكلفة الشحن';

    protected static ?string $pluralLabel = 'تكاليف الشحن';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('تكلفة الشحن')
                    ->schema([
                        Forms\Components\TextInput::make('value')
                            ->label('التكلفة')
                            ->required()
                            ->numeric()
                            ->suffix('ج.م')
                            ->minValue(0)
                            ->step(0.01)
                            ->rules(['required', 'numeric', 'min:0']),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('value')
            ->columns([
                Tables\Columns\TextColumn::make('value')
                    ->label('التكلفة')
                    ->money('EGP')
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
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة تكلفة شحن')
                    ->visible(fn () => ! $this->getOwnerRecord()->shippingCosts()->exists())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['area_id'] = $this->getOwnerRecord()->getKey();

                        return $data;
                    }),
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
            ->emptyStateHeading('لا توجد تكلفة شحن')
            ->emptyStateDescription('لم يتم تحديد تكلفة شحن لهذه المنطقة بعد.')
            ->emptyStateIcon('heroicon-o-truck');
    }
}
