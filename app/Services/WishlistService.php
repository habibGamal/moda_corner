<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

class WishlistService
{
    /**
     * Get a user's wishlist items with products.
     */
    public function getUserList(User $user): Collection
    {
        return $user->wishlistItems()->with('product')->get();
    }

    /**
     * Add an item to the user's wishlist.
     */
    public function addItem(User $user, int $productId): ?WishlistItem
    {
        // Check if the product exists
        $product = Product::find($productId);
        if (! $product) {
            return null;
        }

        // Check if the item is already in the wishlist
        $existingItem = $user->wishlistItems()
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            return $existingItem;
        }

        // Add the item to the wishlist
        return WishlistItem::create([
            'user_id' => $user->id,
            'product_id' => $productId,
        ]);
    }

    /**
     * Remove an item from the user's wishlist.
     */
    public function removeItem(User $user, int $productId): bool
    {
        $deleted = $user->wishlistItems()
            ->where('product_id', $productId)
            ->delete();

        return $deleted > 0;
    }

    /**
     * Clear a user's wishlist.
     */
    public function clearList(User $user): bool
    {
        $user->wishlistItems()->delete();

        return true;
    }
}
