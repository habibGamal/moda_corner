<?php

namespace App\Notifications;

use App\Models\ReturnOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnOrderRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ReturnOrder $returnOrder;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReturnOrder $returnOrder)
    {
        $this->returnOrder = $returnOrder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $returnItemsDetails = $this->returnOrder->items->map(function ($item) {
            $variant = $item->order_item->variant ? " ({$item->order_item->variant->name_ar})" : '';

            return "• {$item->order_item->product->name_ar}{$variant} - الكمية: {$item->quantity} - السعر: ".
                   number_format((float) $item->total_price, 2).' جنيه';
        })->implode("\n");

        $totalItemsCount = $this->returnOrder->items->sum('quantity');
        $totalRefundAmount = $this->returnOrder->items->sum(function ($item) {
            return (float) $item->total_price;
        });

        $message = (new MailMessage)
            ->subject('طلب إرجاع جديد - رقم الطلب #'.$this->returnOrder->order_id)
            ->greeting('مرحباً إدارة '.config('app.name'))
            ->line('تم إستلام طلب إرجاع جديد من أحد العملاء.')
            ->line('')
            ->line('**تفاصيل طلب الإرجاع:**')
            ->line('• رقم الإرجاع: #'.$this->returnOrder->id)
            ->line('• رقم الطلب الأصلي: #'.$this->returnOrder->order_id)
            ->line('• اسم العميل: '.$this->returnOrder->order->user->name)
            ->line('• بريد العميل الإلكتروني: '.$this->returnOrder->order->user->email)
            ->line('• رقم هاتف العميل: '.($this->returnOrder->order->user->phone ?? 'غير محدد'))
            ->line('• تاريخ الطلب الأصلي: '.$this->returnOrder->order->created_at->format('Y-m-d H:i:s'))
            ->line('• تاريخ طلب الإرجاع: '.$this->returnOrder->created_at->format('Y-m-d H:i:s'))
            ->line('• حالة الإرجاع: '.$this->returnOrder->status->getLabel())
            ->line('• عدد العناصر المطلوب إرجاعها: '.$totalItemsCount)
            ->line('• قيمة الإرجاع المقدرة: '.number_format($totalRefundAmount, 2).' جنيه')
            ->line('')
            ->line('**سبب الإرجاع:**')
            ->line($this->returnOrder->reason)
            ->line('')
            ->line('**المنتجات المطلوب إرجاعها:**')
            ->line($returnItemsDetails);

        // Check if refund is needed
        $needsRefund = $this->returnOrder->order->payment_status->value === 'paid' &&
                      ! $this->returnOrder->order->payment_method->isCOD();

        if ($needsRefund) {
            $message->line('')
                ->line('⚠️ **ملاحظة هامة:** هذا الطلب يتطلب إرجاع المبلغ المدفوع حيث أن الدفع تم بالفعل.');
        }

        $message->action('مراجعة طلب الإرجاع', route('filament.admin.resources.return-orders.view', $this->returnOrder))
            ->line('')
            ->line('يرجى مراجعة طلب الإرجاع واتخاذ القرار المناسب (الموافقة أو الرفض) في أقرب وقت ممكن.')
            ->line('يمكنك التواصل مع العميل مباشرة إذا كنت بحاجة لمزيد من التفاصيل.')
            ->salutation('مع أطيب التحيات,'."\n".'نظام '.config('app.name'));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'return_order_id' => $this->returnOrder->id,
            'order_id' => $this->returnOrder->order_id,
            'customer_name' => $this->returnOrder->order->user->name,
            'customer_email' => $this->returnOrder->order->user->email,
            'return_reason' => $this->returnOrder->reason,
            'return_status' => $this->returnOrder->status->value,
            'total_items' => $this->returnOrder->items->sum('quantity'),
            'total_amount' => $this->returnOrder->items->sum(function ($item) {
                return (float) $item->total_price;
            }),
            'needs_refund' => $this->returnOrder->order->payment_status->value === 'paid' &&
                             ! $this->returnOrder->order->payment_method->isCOD(),
        ];
    }
}
