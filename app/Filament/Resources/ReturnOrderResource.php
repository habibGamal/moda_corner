<?php

namespace App\Filament\Resources;

use App\Enums\ReturnOrderStatus;
use App\Filament\Resources\ReturnOrderResource\Pages;
use App\Filament\Resources\ReturnOrderResource\RelationManagers;
use App\Models\ReturnOrder;
use App\Services\ReturnOrderService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReturnOrderResource extends Resource
{
    protected static ?string $model = ReturnOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'return_number';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Return Information')
                    ->schema([
                        Forms\Components\TextInput::make('return_number')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('status')
                            ->options(ReturnOrderStatus::class)
                            ->required()
                            ->disabled(),
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->maxLength(65535)
                            ->disabled(),
                        Forms\Components\Textarea::make('rejection_reason')
                            ->maxLength(65535)
                            ->visible(fn (ReturnOrder $record) => $record->status === ReturnOrderStatus::REJECTED),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\Select::make('order_id')
                            ->relationship('order', 'id')
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Financial Information')
                    ->schema([
                        Forms\Components\TextInput::make('total_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\TextInput::make('refund_amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('requested_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('rejected_at')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('return_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID')
                    ->searchable()
                    ->sortable()
                    ->url(fn (ReturnOrder $record): string => route('filament.admin.resources.orders.view', $record->order)),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_items_count')
                    ->label('Items Count')
                    ->counts('returnItems'),
                Tables\Columns\TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(ReturnOrderStatus::class),
                Tables\Filters\Filter::make('requested_at')
                    ->form([
                        Forms\Components\DatePicker::make('requested_from'),
                        Forms\Components\DatePicker::make('requested_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['requested_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('requested_at', '>=', $date),
                            )
                            ->when(
                                $data['requested_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('requested_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (ReturnOrder $record): bool => $record->status === ReturnOrderStatus::REQUESTED)
                    ->requiresConfirmation()
                    ->action(function (ReturnOrder $record, ReturnOrderService $service): void {
                        try {
                            $service->approveReturn($record->id);

                            Notification::make()
                                ->title('Return Approved')
                                ->body("Return {$record->return_number} has been approved successfully.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (ReturnOrder $record): bool => $record->status === ReturnOrderStatus::REQUESTED)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (ReturnOrder $record, array $data, ReturnOrderService $service): void {
                        try {
                            $service->rejectReturn($record->id, $data['rejection_reason']);

                            Notification::make()
                                ->title('Return Rejected')
                                ->body("Return {$record->return_number} has been rejected.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ReturnOrder $record): bool => $record->status === ReturnOrderStatus::APPROVED)
                    ->requiresConfirmation()
                    ->modalDescription('This will return the items to stock and process the refund if applicable.')
                    ->action(function (ReturnOrder $record, ReturnOrderService $service): void {
                        try {
                            $service->completeReturn($record->id);

                            Notification::make()
                                ->title('Return Completed')
                                ->body("Return {$record->return_number} has been completed successfully.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => false), // Disable bulk delete for returns
                ]),
            ])
            ->defaultSort('requested_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ReturnItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnOrders::route('/'),
            'view' => Pages\ViewReturnOrder::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Returns should only be created by customers through the frontend
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Returns should not be edited directly, only status changes through actions
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', ReturnOrderStatus::REQUESTED)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $pendingCount = static::getModel()::where('status', ReturnOrderStatus::REQUESTED)->count();

        return $pendingCount > 0 ? 'warning' : null;
    }
}
