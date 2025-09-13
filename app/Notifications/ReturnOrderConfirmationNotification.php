<?php

namespace App\Notifications;

use App\Models\ReturnOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnOrderConfirmationNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('تأكيد طلب الإرجاع - رقم #'.$this->returnOrder->id)
            ->greeting("مرحباً {$this->returnOrder->order->user->name}،")
            ->line('تم استلام طلب الإرجاع الخاص بك بنجاح.')
            ->line('')
            ->line('**تفاصيل طلب الإرجاع:**')
            ->line('• رقم الإرجاع: #'.$this->returnOrder->id)
            ->line('• رقم الطلب الأصلي: #'.$this->returnOrder->order_id)
            ->line('• تاريخ طلب الإرجاع: '.$this->returnOrder->created_at->format('Y-m-d H:i:s'))
            ->line('• حالة الطلب: '.$this->returnOrder->status->getLabel())
            ->line('• عدد العناصر: '.$totalItemsCount)
            ->line('• القيمة المقدرة للإرجاع: '.number_format($totalRefundAmount, 2).' جنيه')
            ->line('')
            ->line('**سبب الإرجاع:**')
            ->line($this->returnOrder->reason)
            ->line('')
            ->line('**المنتجات المطلوب إرجاعها:**')
            ->line($returnItemsDetails)
            ->line('')
            ->line('**الخطوات التالية:**')
            ->line('• سيتم مراجعة طلبك من قبل فريق خدمة العملاء خلال 24-48 ساعة.')
            ->line('• ستتلقى إشعاراً بالبريد الإلكتروني عند تحديث حالة طلب الإرجاع.')
            ->line('• في حالة الموافقة، سيتم التواصل معك لترتيب استلام المنتجات.')
            ->line('• سيتم معالجة الاسترداد بعد استلام المنتجات وفحصها.')
            ->action('عرض تفاصيل الإرجاع', route('returns.show', $this->returnOrder))
            ->line('')
            ->line('**ملاحظات مهمة:**')
            ->line('• يرجى الاحتفاظ بالمنتجات في حالتها الأصلية.')
            ->line('• تأكد من وجود جميع الملحقات والتغليف الأصلي.')
            ->line('• يمكنك متابعة حالة طلب الإرجاع من خلال حسابك.')
            ->line('')
            ->line('إذا كان لديك أي استفسارات، لا تتردد في التواصل مع خدمة العملاء.')
            ->line('شكراً لك على ثقتك في '.config('app.name'))
            ->salutation('مع أطيب التحيات,'."\n".'فريق '.config('app.name'));
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
            'return_status' => $this->returnOrder->status->value,
            'total_items' => $this->returnOrder->items->sum('quantity'),
            'estimated_refund' => $this->returnOrder->items->sum(function ($item) {
                return (float) $item->total_price;
            }),
        ];
    }
}
