<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackInStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Product $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
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
        $productName = $locale === 'ar' ? $this->product->name_ar : $this->product->name_en;

        if ($locale === 'ar') {
            return (new MailMessage)
                ->subject('المنتج متوفر الآن - '.$productName)
                ->greeting('مرحباً!')
                ->line('خبر سار! المنتج الذي طلبت إشعارك عنه أصبح متوفراً الآن.')
                ->line('')
                ->line('**'.$productName.'**')
                ->line('السعر: '.number_format((float) ($this->product->sale_price ?? $this->product->price), 2).' جنيه')
                ->action('عرض المنتج', route('products.show', $this->product->id))
                ->line('')
                ->line('سارع بالشراء قبل نفاد الكمية!')
                ->salutation('مع أطيب التحيات,'."\n".config('app.name'));
        } else {
            return (new MailMessage)
                ->subject('Back in Stock - '.$productName)
                ->greeting('Hello!')
                ->line('Great news! The product you requested to be notified about is now back in stock.')
                ->line('')
                ->line('**'.$productName.'**')
                ->line('Price: '.number_format((float) ($this->product->sale_price ?? $this->product->price), 2).' EGP')
                ->action('View Product', route('products.show', $this->product->id))
                ->line('')
                ->line('Hurry, limited stock available!')
                ->salutation('Best regards,'."\n".config('app.name'));
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name_en' => $this->product->name_en,
            'product_name_ar' => $this->product->name_ar,
        ];
    }
}
