<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Actions\Orders\ApproveReturnAction;
use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CompleteReturnAction;
use App\Actions\Orders\MarkOrderAsDeliveredAction;
use App\Actions\Orders\MarkOrderAsShippedAction;
use App\Actions\Orders\ProcessRefundAction;
use App\Actions\Orders\RejectReturnAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm_instapay_payment')
                ->label('تأكيد الدفع')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(
                    fn($record) =>
                    $record->payment_method === PaymentMethod::INSTAPAY &&
                    $record->payment_status === PaymentStatus::IN_REVIEW &&
                    $record->payment_proof !== null
                )
                ->requiresConfirmation()
                ->modalHeading('تأكيد الدفع عبر إنستاباي')
                ->modalDescription('هل أنت متأكد من تأكيد هذا الدفع؟ سيتم تحديث حالة الدفع إلى "مدفوع".')
                ->action(function () {
                    try {
                        $this->record->payment_status = PaymentStatus::PAID;

                        $existingDetails = json_decode($this->record->payment_details, true) ?? [];
                        $this->record->payment_details = json_encode(array_merge($existingDetails, [
                            'verified_at' => now()->toISOString(),
                            'verified_by' => auth()->id(),
                        ]));

                        $this->record->save();

                        Notification::make()
                            ->title('تم تأكيد الدفع بنجاح')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل في تأكيد الدفع')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('reject_instapay_payment')
                ->label('رفض الدفع')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(
                    fn($record) =>
                    $record->payment_method === PaymentMethod::INSTAPAY &&
                    $record->payment_status === PaymentStatus::IN_REVIEW &&
                    $record->payment_proof !== null
                )
                ->requiresConfirmation()
                ->modalHeading('رفض الدفع عبر إنستاباي')
                ->modalDescription('سيتم رفض إثبات الدفع والسماح للعميل بإعادة الرفع. هل أنت متأكد؟')
                ->action(function () {
                    try {
                        $this->record->payment_status = PaymentStatus::PENDING;
                        $existingDetails = json_decode($this->record->payment_details, true) ?? [];
                        $this->record->payment_details = json_encode(array_merge($existingDetails, [
                            'rejected_at' => now()->toISOString(),
                            'rejected_by' => auth()->id(),
                            'can_reupload' => true,
                        ]));

                        $this->record->save();

                        Notification::make()
                            ->title('تم رفض إثبات الدفع')
                            ->body('يمكن للعميل الآن إعادة رفع إثبات الدفع')
                            ->warning()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('فشل في رفض الدفع')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('mark_shipped')
                ->label('تحديد كمشحون')
                ->icon('heroicon-o-paper-airplane')
                ->requiresConfirmation()
                ->color('info')
                ->visible(fn($record) => $record->order_status === OrderStatus::PROCESSING)
                ->action(function () {
                    try {
                        app(MarkOrderAsShippedAction::class)->execute($this->record);

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
            Action::make('mark_delivered')
                ->label('تم التوصيل')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn($record) => in_array($record->order_status, [OrderStatus::PROCESSING, OrderStatus::SHIPPED]))
                ->action(function () {
                    try {
                        app(MarkOrderAsDeliveredAction::class)->execute($this->record);

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
            Action::make('cancel_order')
                ->label('إلغاء الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn($record) => !in_array($record->order_status, [OrderStatus::CANCELLED, OrderStatus::DELIVERED]))
                ->requiresConfirmation()
                ->modalHeading('إلغاء الطلب')
                ->modalDescription('هل أنت متأكد من إلغاء هذا الطلب؟ سيتم إرجاع البضائع للمخزون.')
                ->action(function () {
                    try {
                        app(CancelOrderAction::class)->execute($this->record);

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
            Action::make('approve_return')
                ->label('الموافقة على الإرجاع')
                ->icon('heroicon-o-check')
                ->color('info')
                ->visible(fn($record) => $record->return_status === ReturnStatus::RETURN_REQUESTED)
                ->action(function () {
                    try {
                        app(ApproveReturnAction::class)->execute($this->record);

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
            Action::make('reject_return')
                ->label('رفض الإرجاع')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn($record) => $record->return_status === ReturnStatus::RETURN_REQUESTED)
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('سبب الرفض')
                        ->maxLength(500)
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(RejectReturnAction::class)->execute($this->record, $data['rejection_reason'] ?? null);

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
            Action::make('complete_return')
                ->label('إكمال الإرجاع')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success')
                ->visible(fn($record) => $record->return_status === ReturnStatus::RETURN_APPROVED)
                ->requiresConfirmation()
                ->modalHeading('إكمال عملية الإرجاع')
                ->modalDescription('سيتم إرجاع البضائع للمخزون ومعالجة الاسترداد إذا لزم الأمر. هل أنت متأكد؟')
                ->action(function () {
                    try {
                        app(CompleteReturnAction::class)->execute($this->record);

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
            Action::make('process_refund')
                ->label('معالجة الاسترداد')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->visible(fn($record) => $record->needsRefund())
                ->requiresConfirmation()
                ->modalHeading('معالجة الاسترداد')
                ->modalDescription(fn($record) => 'سيتم تحديث حالة الدفع إلى "تم الاسترداد" للطلب رقم #' . $record->id . '. المبلغ: ' . number_format($record->total, 2) . ' جنيه. هل أنت متأكد؟')
                ->action(function () {
                    try {
                        app(ProcessRefundAction::class)->execute($this->record);

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
        ];
    }
}
