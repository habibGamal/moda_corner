<?php

namespace App\Observers;

use App\Jobs\SendStockNotificationsJob;
use App\Models\ProductVariant;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "updated" event.
     * Check if quantity changed from 0 to > 0, then dispatch stock notification job.
     */
    public function updated(ProductVariant $productVariant): void
    {
        // Check if quantity was changed
        if ($productVariant->isDirty('quantity')) {
            $oldQuantity = $productVariant->getOriginal('quantity');
            $newQuantity = $productVariant->quantity;

            // If quantity changed from 0 (out of stock) to > 0 (back in stock)
            if ($oldQuantity == 0 && $newQuantity > 0) {
                // Dispatch job to send stock notifications for this product
                SendStockNotificationsJob::dispatch($productVariant->product_id);
            }
        }
    }
}
