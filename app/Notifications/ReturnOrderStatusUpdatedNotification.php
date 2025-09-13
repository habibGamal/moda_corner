<?php

namespace App\Notifications;

use App\Enums\ReturnOrderStatus;
use App\Models\ReturnOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnOrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ReturnOrder $returnOrder;

    protected ReturnOrderStatus $previousStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReturnOrder $returnOrder, ReturnOrderStatus $previousStatus)
    {
        $this->returnOrder = $returnOrder;
        $this->previousStatus = $previousStatus;
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
        $subject = $this->getSubjectByStatus();
        $greeting = "مرحباً {$this->returnOrder->order->user->name}،";
        $intro = $this->getIntroByStatus();

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($intro)
            ->line('')
            ->line('**تفاصيل طلب الإرجاع:**')
            ->line('• رقم الإرجاع: #'.$this->returnOrder->id)
            ->line('• رقم الطلب الأصلي: #'.$this->returnOrder->order_id)
            ->line('• تاريخ طلب الإرجاع: '.$this->returnOrder->created_at->format('Y-m-d H:i:s'))
            ->line('• الحالة الحالية: '.$this->returnOrder->status->getLabel())
            ->line('• عدد العناصر: '.$this->returnOrder->items->sum('quantity'));

        // Add admin notes if present
        if ($this->returnOrder->admin_notes) {
            $message->line('')
                ->line('**ملاحظات إدارية:**')
                ->line($this->returnOrder->admin_notes);
        }

        // Add refund information for completed returns
        if ($this->returnOrder->status === ReturnOrderStatus::COMPLETED && $this->returnOrder->refund_amount) {
            $message->line('')
                ->line('**تفاصيل الاسترداد:**')
                ->line('• المبلغ المسترد: '.number_format((float) $this->returnOrder->refund_amount, 2).' جنيه');

            if ($this->returnOrder->refunded_at) {
                $message->line('• تاريخ الاسترداد: '.$this->returnOrder->refunded_at->format('Y-m-d H:i:s'));
            }
        }

        $message = $this->addStatusSpecificContent($message);

        $message->action('عرض تفاصيل الإرجاع', route('returns.show', $this->returnOrder))
            ->line('')
            ->line('شكراً لك على ثقتك في '.config('app.name'))
            ->salutation('مع أطيب التحيات,'."\n".'فريق '.config('app.name'));

        return $message;
    }

    /**
     * Get subject line based on status
     */
    private function getSubjectByStatus(): string
    {
        return match ($this->returnOrder->status) {
            ReturnOrderStatus::APPROVED => 'تمت الموافقة على طلب الإرجاع - رقم #'.$this->returnOrder->id,
            ReturnOrderStatus::REJECTED => 'تم رفض طلب الإرجاع - رقم #'.$this->returnOrder->id,
            ReturnOrderStatus::COMPLETED => 'تم إكمال عملية الإرجاع - رقم #'.$this->returnOrder->id,
            default => 'تحديث حالة طلب الإرجاع - رقم #'.$this->returnOrder->id,
        };
    }

    /**
     * Get intro message based on status
     */
    private function getIntroByStatus(): string
    {
        return match ($this->returnOrder->status) {
            ReturnOrderStatus::APPROVED => 'نود إعلامك بأنه تمت الموافقة على طلب الإرجاع الخاص بك.',
            ReturnOrderStatus::REJECTED => 'نأسف لإعلامك بأنه تم رفض طلب الإرجاع الخاص بك.',
            ReturnOrderStatus::COMPLETED => 'نود إعلامك بأنه تم إكمال عملية الإرجاع الخاصة بك بنجاح.',
            default => 'تم تحديث حالة طلب الإرجاع الخاص بك.',
        };
    }

    /**
     * Add status-specific content to the message
     */
    private function addStatusSpecificContent(MailMessage $message): MailMessage
    {
        return match ($this->returnOrder->status) {
            ReturnOrderStatus::APPROVED => $message
                ->line('')
                ->line('**الخطوات التالية:**')
                ->line('• سيتم التواصل معك قريباً لترتيب استلام المنتجات.')
                ->line('• يرجى التأكد من أن المنتجات في حالتها الأصلية.')
                ->line('• سيتم معالجة الاسترداد بعد استلام المنتجات وفحصها.'),

            ReturnOrderStatus::REJECTED => $message
                ->line('')
                ->line('إذا كان لديك أي استفسارات حول قرار الرفض، يمكنك التواصل مع خدمة العملاء.')
                ->line('شكراً لتفهمك.'),

            ReturnOrderStatus::COMPLETED => $message
                ->line('')
                ->line('تم إكمال جميع إجراءات الإرجاع.')
                ->line('شكراً لك على تعاملك معنا ونتطلع لخدمتك مرة أخرى.'),

            default => $message
        };
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
            'current_status' => $this->returnOrder->status->value,
            'previous_status' => $this->previousStatus->value,
            'status_label' => $this->returnOrder->status->getLabel(),
            'admin_notes' => $this->returnOrder->admin_notes,
            'refund_amount' => $this->returnOrder->refund_amount,
            'refunded_at' => $this->returnOrder->refunded_at?->toISOString(),
        ];
    }
}
