<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\StockNotification;
use App\Notifications\BackInStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendStockNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $productId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $product = Product::find($this->productId);

        if (! $product) {
            return;
        }

        // Get all pending stock notifications for this product
        $notifications = StockNotification::where('product_id', $this->productId)
            ->pending()
            ->get();

        foreach ($notifications as $notification) {
            // Send notification via AnonymousNotifiable for email
            Notification::route('mail', $notification->email)
                ->notify(new BackInStockNotification($product));

            $notification->delete();
        }
    }
}
