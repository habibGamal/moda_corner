<?php

namespace App\Filament\Resources;

use App\Actions\Orders\ApproveReturnAction;
use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CompleteReturnAction;
use App\Actions\Orders\MarkOrderAsDeliveredAction;
use App\Actions\Orders\MarkOrderAsShippedAction;
use App\Actions\Orders\ProcessRefundAction;
use App\Actions\Orders\RejectReturnAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $label = 'الطلب';

    protected static ?string $pluralLabel = 'الطلبات';

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        /** @var Order $record */
        return 'طلب #'.$record->id;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'user.name', 'user.email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        /** @var Order $record */
        return [
            'Status' => $record->order_status->value,
            'Total' => number_format($record->total, 2),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                FormSection::make('معلومات الطلب')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('رقم الطلب')
                            ->disabled(),
                        Forms\Components\Select::make('order_status')
                            ->label('حالة الطلب')
                            ->options(OrderStatus::class)
                            ->disabled(),
                        Forms\Components\Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options(PaymentStatus::class)
                            ->disabled(),
                        Forms\Components\TextInput::make('payment_method')
                            ->label('طريقة الدفع')
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s'))
                            ->disabled(),
                        Forms\Components\Select::make('return_status')
                            ->label('حالة الإرجاع')
                            ->options(ReturnStatus::class)
                            ->disabled(),
                        Forms\Components\TextInput::make('delivered_at')
                            ->label('تاريخ التوصيل')
                            ->formatStateUsing(fn ($state) => $state?->format('Y-m-d H:i:s'))
                            ->disabled(),
                    ])->columns(2),

                FormSection::make('معلومات العميل')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('اسم العميل')
                            ->disabled(),
                        Forms\Components\TextInput::make('user.email')
                            ->label('البريد الإلكتروني')
                            ->disabled(),
                        Forms\Components\TextInput::make('shippingAddress.content')
                            ->label('عنوان الشحن')
                            ->disabled(),
                        Forms\Components\TextInput::make('shippingAddress.phone')
                            ->label('رقم الهاتف')
                            ->disabled(),
                        Forms\Components\TextInput::make('shippingAddress.area.name_en')
                            ->label('المنطقة')
                            ->disabled(),
                    ])->columns(2),

                FormSection::make('ملخص الطلب')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('المجموع الفرعي')
                            ->prefix('ج.م')
                            ->disabled(),
                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('الشحن')
                            ->prefix('ج.م')
                            ->disabled(),
                        Forms\Components\TextInput::make('discount')
                            ->label('الخصم')
                            ->prefix('ج.م')
                            ->disabled(),
                        Forms\Components\TextInput::make('total')
                            ->label('الإجمالي')
                            ->prefix('ج.م')
                            ->disabled(),
                        Forms\Components\TextInput::make('coupon_code')
                            ->label('كوبون')
                            ->disabled(),
                        Forms\Components\TextInput::make('promotion.name_ar')
                            ->label('العرض الترويجي')
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('return_reason')
                            ->label('سبب الإرجاع')
                            ->disabled()
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->return_status),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('معلومات الطلب')
                    ->schema([
                        TextEntry::make('id')
                            ->label('رقم الطلب'),
                        TextEntry::make('order_status')
                            ->label('حالة الطلب')
                            ->badge(),
                        TextEntry::make('payment_status')
                            ->label('حالة الدفع')
                            ->badge(),
                        TextEntry::make('payment_method')
                            ->label('طريقة الدفع'),
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                        TextEntry::make('return_status')
                            ->label('حالة الإرجاع')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('delivered_at')
                            ->label('تاريخ التوصيل')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('return_requested_at')
                            ->label('تاريخ طلب الإرجاع')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->return_status),
                        TextEntry::make('cancelled_at')
                            ->label('تاريخ الإلغاء')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->order_status === OrderStatus::CANCELLED),
                        TextEntry::make('refunded_at')
                            ->label('تاريخ الاسترداد')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn ($record) => $record->payment_status === PaymentStatus::REFUNDED),
                        IconEntry::make('needs_refund')
                            ->label('يحتاج استرداد')
                            ->boolean()
                            ->trueIcon('heroicon-o-exclamation-triangle')
                            ->falseIcon('heroicon-o-check-circle')
                            ->trueColor('warning')
                            ->falseColor('success')
                            ->visible(fn ($record) => $record->order_status === OrderStatus::CANCELLED),
                    ])->columns(2),

                Section::make('معلومات العميل')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('اسم العميل'),
                        TextEntry::make('user.email')
                            ->label('البريد الإلكتروني'),
                        TextEntry::make('shippingAddress.content')
                            ->label('عنوان الشحن'),
                        TextEntry::make('shippingAddress.phone')
                            ->label('رقم الهاتف')
                            ->placeholder('-'),
                        TextEntry::make('shippingAddress.area.name_en')
                            ->label('المنطقة'),
                    ])->columns(2),

                Section::make('ملخص الطلب')
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label('المجموع الفرعي')
                            ->money('EGP'),
                        TextEntry::make('shipping_cost')
                            ->label('الشحن')
                            ->money('EGP'),
                        TextEntry::make('discount')
                            ->label('الخصم')
                            ->money('EGP'),
                        TextEntry::make('total')
                            ->label('الإجمالي')
                            ->money('EGP')
                            ->weight('bold'),
                        TextEntry::make('coupon_code')
                            ->label('كوبون')
                            ->placeholder('-'),
                        TextEntry::make('promotion.name_ar')
                            ->label('العرض الترويجي')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('ملاحظات')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('return_reason')
                            ->label('سبب الإرجاع')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->return_status),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الطلب')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('العميل')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('needs_refund')
                    ->label('يحتاج استرداد')
                    ->boolean()
                    ->tooltip(fn (Order $record) => $record->needs_refund ? 'هذا الطلب يحتاج إلى استرداد' : 'لا يحتاج استرداد'),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('return_status')
                    ->label('حالة الإرجاع')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')
                    ->label('حالة الطلب')
                    ->options(OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options(PaymentStatus::class),
                Tables\Filters\SelectFilter::make('return_status')
                    ->label('حالة الإرجاع')
                    ->options(ReturnStatus::class),
                Tables\Filters\Filter::make('needs_refund')
                    ->label('يحتاج استرداد')
                    ->query(fn (Builder $query): Builder => $query->needsRefund()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_shipped')
                    ->label('تحديد كمشحون')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->order_status === OrderStatus::PROCESSING)
                    ->action(function (Order $record) {
                        try {
                            app(MarkOrderAsShippedAction::class)->execute($record);
                            Notification::make()
                                ->title('تم تحديد الطلب كمشحون')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في تحديث حالة الطلب')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('mark_delivered')
                    ->label('تم التوصيل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => in_array($record->order_status, [OrderStatus::PROCESSING, OrderStatus::SHIPPED]))
                    ->action(function (Order $record) {
                        try {
                            app(MarkOrderAsDeliveredAction::class)->execute($record);
                            Notification::make()
                                ->title('تم تحديد الطلب كمسلم')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في تحديث حالة الطلب')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('cancel_order')
                    ->label('إلغاء الطلب')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => ! in_array($record->order_status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED]))
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء الطلب')
                    ->modalDescription('هل أنت متأكد من إلغاء هذا الطلب؟ سيتم إرجاع البضائع للمخزون.')
                    ->action(function (Order $record) {
                        try {
                            app(CancelOrderAction::class)->execute($record);
                            Notification::make()
                                ->title('تم إلغاء الطلب')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في إلغاء الطلب')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('approve_return')
                    ->label('الموافقة على الإرجاع')
                    ->icon('heroicon-o-check')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->return_status === ReturnStatus::RETURN_REQUESTED)
                    ->action(function (Order $record) {
                        try {
                            app(ApproveReturnAction::class)->execute($record);
                            Notification::make()
                                ->title('تم الموافقة على طلب الإرجاع')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في الموافقة على الإرجاع')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('reject_return')
                    ->label('رفض الإرجاع')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Order $record) => $record->return_status === ReturnStatus::RETURN_REQUESTED)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->action(function (Order $record, array $data) {
                        try {
                            app(RejectReturnAction::class)->execute($record, $data['rejection_reason'] ?? null);
                            Notification::make()
                                ->title('تم رفض طلب الإرجاع')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في رفض الإرجاع')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('complete_return')
                    ->label('إكمال الإرجاع')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->return_status === ReturnStatus::RETURN_APPROVED)
                    ->requiresConfirmation()
                    ->modalHeading('إكمال عملية الإرجاع')
                    ->modalDescription('سيتم إرجاع البضائع للمخزون ومعالجة الاسترداد إذا لزم الأمر. هل أنت متأكد؟')
                    ->action(function (Order $record) {
                        try {
                            app(CompleteReturnAction::class)->execute($record);
                            Notification::make()
                                ->title('تم إكمال عملية الإرجاع بنجاح')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في إكمال الإرجاع')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('process_refund')
                    ->label('معالجة الاسترداد')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->visible(fn (Order $record) => $record->needs_refund)
                    ->requiresConfirmation()
                    ->modalHeading('معالجة الاسترداد')
                    ->modalDescription(fn (Order $record) => 'سيتم تحديث حالة الدفع إلى "تم الاسترداد" للطلب رقم #'.$record->id.'. المبلغ: '.number_format($record->total, 2).' جنيه. هل أنت متأكد؟')
                    ->action(function (Order $record) {
                        try {
                            app(ProcessRefundAction::class)->execute($record);
                            Notification::make()
                                ->title('تم معالجة الاسترداد بنجاح')
                                ->body('تم تحديث حالة الدفع إلى "تم الاسترداد"')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('فشل في معالجة الاسترداد')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'shippingAddress', 'shippingAddress.area']);
    }
}
