<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification implements ShouldQueue
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
        $locale = app()->getLocale();

        $orderItemsDetails = $this->order->items->map(function ($item) use ($locale) {
            $productName = $locale === 'ar' ? $item->product->name_ar : $item->product->name_en;
            $variant = $item->variant ? " ({$item->variant->color} {$item->variant->size})" : '';

            if ($locale === 'ar') {
                return "• {$productName}{$variant} - الكمية: {$item->quantity} - السعر: ".number_format($item->price, 2).' جنيه';
            } else {
                return "• {$productName}{$variant} - Quantity: {$item->quantity} - Price: ".number_format($item->price, 2).' EGP';
            }
        })->implode("\n");

        if ($locale === 'ar') {
            $message = (new MailMessage)
                ->subject('تأكيد طلبك - رقم الطلب #'.$this->order->id)
                ->greeting('مرحباً '.$this->order->user->name.'!')
                ->line('شكراً لطلبك من '.config('app.name').'.')
                ->line('تم استلام طلبك بنجاح وسيتم معالجته في أقرب وقت.')
                ->line('')
                ->line('**تفاصيل الطلب:**')
                ->line('• رقم الطلب: #'.$this->order->id)
                ->line('• تاريخ الطلب: '.$this->order->created_at->format('Y-m-d H:i:s'))
                ->line('• طريقة الدفع: '.$this->order->payment_method->getLabel())
                ->line('')
                ->line('**المنتجات:**')
                ->line($orderItemsDetails)
                ->line('')
                ->line('**الملخص المالي:**')
                ->line('• المبلغ الفرعي: '.number_format((float) $this->order->subtotal, 2).' جنيه')
                ->line('• تكلفة الشحن: '.number_format((float) $this->order->shipping_cost, 2).' جنيه');

            if ($this->order->discount > 0) {
                $message->line('• الخصم: '.number_format((float) $this->order->discount, 2).' جنيه');
            }

            $message->line('• **إجمالي المبلغ: '.number_format((float) $this->order->total, 2).' جنيه**')
                ->line('')
                ->line('**عنوان الشحن:**')
                ->line($this->order->shippingAddress->area->gov->name_ar.' - '.$this->order->shippingAddress->area->name_ar)
                ->line($this->order->shippingAddress->address_line)
                ->line('رقم الهاتف: '.$this->order->shippingAddress->phone_number)
                ->action('عرض تفاصيل الطلب', route('orders.show', $this->order))
                ->line('')
                ->line('سنبقيك على اطلاع بحالة طلبك.')
                ->salutation('مع أطيب التحيات,'."\n".config('app.name'));
        } else {
            $message = (new MailMessage)
                ->subject('Order Confirmation - Order #'.$this->order->id)
                ->greeting('Hello '.$this->order->user->name.'!')
                ->line('Thank you for your order from '.config('app.name').'.')
                ->line('Your order has been received successfully and will be processed soon.')
                ->line('')
                ->line('**Order Details:**')
                ->line('• Order Number: #'.$this->order->id)
                ->line('• Order Date: '.$this->order->created_at->format('Y-m-d H:i:s'))
                ->line('• Payment Method: '.$this->order->payment_method->getLabel())
                ->line('')
                ->line('**Products:**')
                ->line($orderItemsDetails)
                ->line('')
                ->line('**Summary:**')
                ->line('• Subtotal: '.number_format((float) $this->order->subtotal, 2).' EGP')
                ->line('• Shipping: '.number_format((float) $this->order->shipping_cost, 2).' EGP');

            if ($this->order->discount > 0) {
                $message->line('• Discount: '.number_format((float) $this->order->discount, 2).' EGP');
            }

            $message->line('• **Total: '.number_format((float) $this->order->total, 2).' EGP**')
                ->line('')
                ->line('**Shipping Address:**')
                ->line($this->order->shippingAddress->area->gov->name_en.' - '.$this->order->shippingAddress->area->name_en)
                ->line($this->order->shippingAddress->address_line)
                ->line('Phone: '.$this->order->shippingAddress->phone_number)
                ->action('View Order Details', route('orders.show', $this->order))
                ->line('')
                ->line('We will keep you updated on your order status.')
                ->salutation('Best regards,'."\n".config('app.name'));
        }

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
            'order_id' => $this->order->id,
            'order_total' => $this->order->total,
        ];
    }
}
