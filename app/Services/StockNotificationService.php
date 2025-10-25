<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Support\Facades\Auth;

class StockNotificationService
{
    /**
     * Subscribe a user to stock notifications for a product.
     */
    public function subscribe(int $productId, string $email): StockNotification
    {
        $userId = Auth::id();

        // Check if product exists
        $product = Product::findOrFail($productId);

        // Create or update the stock notification
        return StockNotification::updateOrCreate(
            [
                'email' => $email,
                'product_id' => $productId,
            ],
            [
                'user_id' => $userId,
                'notified_at' => null, // Reset if re-subscribing
            ]
        );
    }

    /**
     * Unsubscribe a user from stock notifications for a product.
     */
    public function unsubscribe(int $productId, string $email): bool
    {
        return StockNotification::where('email', $email)
            ->where('product_id', $productId)
            ->delete() > 0;
    }

    /**
     * Check if an email is subscribed to notifications for a product.
     */
    public function isSubscribed(int $productId, string $email): bool
    {
        return StockNotification::where('email', $email)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Get all pending notifications for a product.
     */
    public function getPendingNotifications(int $productId)
    {
        return StockNotification::where('product_id', $productId)
            ->pending()
            ->get();
    }
}
