<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancellationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    protected string $recipient;

    /**
     * Create a new notification instance.
     *
     * @param  string  $recipient  Either 'customer' or 'admin'
     */
    public function __construct(Order $order, string $recipient = 'customer')
    {
        $this->order = $order;
        $this->recipient = $recipient;
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
        if ($this->recipient === 'admin') {
            return $this->buildAdminNotification($notifiable);
        }

        return $this->buildCustomerNotification($notifiable);
    }

    /**
     * Build customer notification email
     *
     * @param  mixed  $notifiable
     */
    protected function buildCustomerNotification($notifiable): MailMessage
    {
        $needsRefund = $this->order->payment_status->value === 'paid' &&
                      ! $this->order->payment_method->isCOD();

        $message = (new MailMessage)
            ->subject(__('Order Cancelled - Order #:order_id', ['order_id' => $this->order->id]))
            ->greeting(__('Hello :name', ['name' => $notifiable->name]))
            ->line(__('Your order #:order_id has been successfully cancelled.', ['order_id' => $this->order->id]))
            ->line(__('Order Details:'))
            ->line(__('• Total Amount: :amount', ['amount' => number_format($this->order->total, 2)]))
            ->line(__('• Payment Method: :method', ['method' => $this->order->payment_method->getLabel()]));

        if ($needsRefund) {
            $message->line(__('• Since your payment was processed, our team will process your refund within 3-5 business days.'));
        }

        $message->line(__('The items from your order have been returned to our inventory and are available for purchase again.'))
            ->action(__('View Your Orders'), route('orders.index'))
            ->line(__('If you have any questions, please don\'t hesitate to contact our customer support.'))
            ->salutation(__('Best regards,')."\n".config('app.name'));

        return $message;
    }

    /**
     * Build admin notification email
     *
     * @param  mixed  $notifiable
     */
    protected function buildAdminNotification($notifiable): MailMessage
    {
        $needsRefund = $this->order->payment_status->value === 'paid' &&
                      ! $this->order->payment_method->isCOD();

        $message = (new MailMessage)
            ->subject(__('[Admin] Order Cancelled - Order #:order_id', ['order_id' => $this->order->id]))
            ->greeting(__('Hello Admin'))
            ->line(__('Order #:order_id has been cancelled by the customer.', ['order_id' => $this->order->id]))
            ->line(__('Customer Details:'))
            ->line(__('• Customer: :name (:email)', [
                'name' => $this->order->user->name,
                'email' => $this->order->user->email,
            ]))
            ->line(__('• Total Amount: :amount', ['amount' => number_format($this->order->total, 2)]))
            ->line(__('• Payment Method: :method', ['method' => $this->order->payment_method->getLabel()]))
            ->line(__('• Payment Status: :status', ['status' => $this->order->payment_status->getLabel()]));

        if ($needsRefund) {
            $message->line(__('⚠️ **Action Required**: This order requires a refund as the payment was processed.'))
                ->action(__('Process Refund'), route('filament.admin.resources.orders.view', $this->order));
        } else {
            $message->action(__('View Order Details'), route('filament.admin.resources.orders.view', $this->order));
        }

        $message->line(__('The inventory has been automatically restored.'))
            ->salutation(__('Best regards,')."\n".config('app.name').' System');

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
            'payment_method' => $this->order->payment_method->value,
            'payment_status' => $this->order->payment_status->value,
            'recipient' => $this->recipient,
            'needs_refund' => $this->order->payment_status->value === 'paid' &&
                             ! $this->order->payment_method->isCOD(),
        ];
    }
}
