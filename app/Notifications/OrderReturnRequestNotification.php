<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReturnRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $orderItemsDetails = $this->order->items->map(function ($item) {
            $variant = $item->variant ? " ({$item->variant->name_ar})" : '';

            return "• {$item->product->name_ar}{$variant} - الكمية: {$item->quantity} - السعر: ".number_format((float) $item->price, 2).' جنيه';
        })->implode("\n");

        $message = (new MailMessage)
            ->subject('طلب إرجاع جديد - رقم الطلب #'.$this->order->id)
            ->greeting('مرحباً إدارة '.config('app.name'))
            ->line('تم إستلام طلب إرجاع من أحد العملاء.')
            ->line('')
            ->line('**تفاصيل طلب الإرجاع:**')
            ->line('• رقم الطلب: #'.$this->order->id)
            ->line('• اسم العميل: '.$this->order->user->name)
            ->line('• بريد العميل الإلكتروني: '.$this->order->user->email)
            ->line('• رقم هاتف العميل: '.($this->order->user->phone ?? 'غير محدد'))
            ->line('• تاريخ الطلب الأصلي: '.$this->order->created_at->format('Y-m-d H:i:s'))
            ->line('• تاريخ طلب الإرجاع: '.now()->format('Y-m-d H:i:s'))
            ->line('• حالة الإرجاع الحالية: '.$this->order->return_status?->getLabel())
            ->line('')
            ->line('**سبب الإرجاع:**')
            ->line($this->order->return_reason ?? 'لم يتم تحديد السبب')
            ->line('')
            ->line('**تفاصيل الطلب الأصلي:**')
            ->line('• طريقة الدفع: '.$this->order->payment_method?->getLabel())
            ->line('• حالة الدفع: '.$this->order->payment_status?->getLabel())
            ->line('• المبلغ الفرعي: '.number_format((float) $this->order->subtotal, 2).' جنيه')
            ->line('• تكلفة الشحن: '.number_format((float) $this->order->shipping_cost, 2).' جنيه');

        if ($this->order->discount > 0) {
            $message->line('• الخصم: '.number_format((float) $this->order->discount, 2).' جنيه');
            if ($this->order->coupon_code) {
                $message->line('• كود الخصم المستخدم: '.$this->order->coupon_code);
            }
        }

        $message->line('• **إجمالي المبلغ: '.number_format((float) $this->order->total, 2).' جنيه**')
            ->line('')
            ->line('**عنوان الشحن الأصلي:**')
            ->line('• المحافظة: '.$this->order->shippingAddress->area->gov->name_ar)
            ->line('• المنطقة: '.$this->order->shippingAddress->area->name_ar)
            ->line('• العنوان التفصيلي: '.$this->order->shippingAddress->address_line)
            ->line('• رقم الهاتف: '.$this->order->shippingAddress->phone_number)
            ->line('')
            ->line('**المنتجات المطلوب إرجاعها:**')
            ->line($orderItemsDetails);

        // Check if refund is needed
        $needsRefund = $this->order->payment_status->value === 'paid' &&
                      ! $this->order->payment_method->isCOD();

        if ($needsRefund) {
            $message->line('')
                ->line('⚠️ **ملاحظة هامة:** هذا الطلب يتطلب إرجاع المبلغ المدفوع حيث أن الدفع تم بالفعل.');
        }

        $message->action('مراجعة طلب الإرجاع', route('filament.admin.resources.orders.view', $this->order))
            ->line('')
            ->line('يرجى مراجعة طلب الإرجاع واتخاذ القرار المناسب (الموافقة أو الرفض) في أقرب وقت ممكن.')
            ->line('يمكنك التواصل مع العميل مباشرة إذا كنت بحاجة لمزيد من التفاصيل.')
            ->salutation('مع أطيب التحيات,'."\n".'نظام '.config('app.name'));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_total' => $this->order->total,
            'customer_name' => $this->order->user->name,
            'customer_email' => $this->order->user->email,
            'return_reason' => $this->order->return_reason,
            'return_status' => $this->order->return_status->value,
            'needs_refund' => $this->order->payment_status->value === 'paid' &&
                             ! $this->order->payment_method->isCOD(),
        ];
    }
}
