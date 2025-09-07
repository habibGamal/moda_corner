<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class RecommendationSectionService
{
    /**
     * Get query for recommended products
     *
     * Recommends products based on:
     * - Products from categories of user's past orders
     * - Products from brands of user's past orders
     * - Products related to items in the user's wishlist
     * - Products related to items in the user's cart
     * - Featured products if no user history available
     * - Recently added products as a fallback
     *
     * @return Builder
     */
    public function getRecommendedProductsQuery(): Builder
    {
        // Start with base query for active products
        $query = Product::forCards();

        // If user is authenticated, personalize recommendations
        if (Auth::check()) {
            $user = Auth::user();
            $excludeProductIds = []; // Products to exclude from recommendations

            // Try to get recommendations from user's order history
            $orderBasedRecommendations = $this->getOrderBasedRecommendations($user, $excludeProductIds);
            if ($orderBasedRecommendations) {
                return $orderBasedRecommendations;
            }

            // Try to get recommendations from user's cart
            $cartBasedRecommendations = $this->getCartBasedRecommendations($user, $excludeProductIds);
            if ($cartBasedRecommendations) {
                return $cartBasedRecommendations;
            }

            // Try to get recommendations from user's wishlist
            $wishlistBasedRecommendations = $this->getWishlistBasedRecommendations($user, $excludeProductIds);
            if ($wishlistBasedRecommendations) {
                return $wishlistBasedRecommendations;
            }
        }

        // Fall back to generic recommendations if no personalized ones
        return $this->getGenericRecommendations($query);
    }

    /**
     * Get recommendations based on user's order history
     *
     * @param \App\Models\User $user
     * @param array $excludeProductIds Products to exclude
     * @return Builder|null Query builder or null if no recommendations
     */
    private function getOrderBasedRecommendations($user, array &$excludeProductIds): ?Builder
    {
        $userOrderItems = $this->getUserOrderItems($user);

        if ($userOrderItems->isEmpty()) {
            return null;
        }

        $purchasedProductIds = $userOrderItems->pluck('product_id')->filter()->unique()->toArray();
        $excludeProductIds = $purchasedProductIds;

        $categoryIds = $userOrderItems->whereNotNull('product')->pluck('product.category_id')->filter()->unique()->toArray();
        $brandIds = $userOrderItems->whereNotNull('product')->pluck('product.brand_id')->filter()->unique()->toArray();

        return $this->buildRecommendationsQuery($categoryIds, $brandIds, $excludeProductIds);
    }

    /**
     * Get user's order items with product details
     *
     * @param \App\Models\User $user
     * @return Collection
     */
    private function getUserOrderItems($user): Collection
    {
        return OrderItem::whereHas('order', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('product')->get();
    }

    /**
     * Get recommendations based on user's cart contents
     *
     * @param \App\Models\User $user
     * @param array $excludeProductIds Products to exclude
     * @return Builder|null Query builder or null if no recommendations
     */
    private function getCartBasedRecommendations($user, array $excludeProductIds): ?Builder
    {
        if (!$user->cart) {
            return null;
        }

        $cartItems = $user->cart->items()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return null;
        }

        $cartProductIds = $cartItems->pluck('product_id')->filter()->unique()->toArray();
        $excludeProductIds = array_merge($excludeProductIds, $cartProductIds);
        $cartProducts = $cartItems->pluck('product')->filter();

        $categoryIds = $cartProducts->pluck('category_id')->filter()->unique()->toArray();
        $brandIds = $cartProducts->pluck('brand_id')->filter()->unique()->toArray();

        return $this->buildRecommendationsQuery($categoryIds, $brandIds, $excludeProductIds);
    }

    /**
     * Get recommendations based on user's wishlist
     *
     * @param \App\Models\User $user
     * @param array $excludeProductIds Products to exclude
     * @return Builder|null Query builder or null if no recommendations
     */
    private function getWishlistBasedRecommendations($user, array $excludeProductIds): ?Builder
    {
        $wishlistItems = WishlistItem::where('user_id', $user->id)->get();

        if ($wishlistItems->isEmpty()) {
            return null;
        }

        $wishlistProductIds = $wishlistItems->pluck('product_id')->toArray();
        $excludeProductIds = array_merge($excludeProductIds, $wishlistProductIds);
        $wishlistProducts = Product::find($wishlistProductIds);

        $categoryIds = $wishlistProducts->pluck('category_id')->filter()->unique()->toArray();
        $brandIds = $wishlistProducts->pluck('brand_id')->filter()->unique()->toArray();

        return $this->buildRecommendationsQuery($categoryIds, $brandIds, $excludeProductIds);
    }

    /**
     * Build a query for recommendations based on categories and brands
     *
     * @param array $categoryIds Category IDs to include
     * @param array $brandIds Brand IDs to include
     * @param array $excludeProductIds Product IDs to exclude
     * @return Builder|null Query builder or null if no valid recommendations
     */
    private function buildRecommendationsQuery(array $categoryIds, array $brandIds, array $excludeProductIds): ?Builder
    {
        if (empty($categoryIds) && empty($brandIds)) {
            return null;
        }

        $query = Product::forCards()
            ->where(function($query) use ($categoryIds, $brandIds) {
                if (!empty($categoryIds)) {
                    $query->whereIn('category_id', $categoryIds);
                }

                if (!empty($brandIds)) {
                    $query->orWhereIn('brand_id', $brandIds);
                }
            })
            ->whereNotIn('id', $excludeProductIds);

        if ($query->count() > 0) {
            return $query->inRandomOrder();
        }

        return null;
    }

    /**
     * Get generic (non-personalized) recommendations
     *
     * @param Builder $baseQuery Base query to build upon
     * @return Builder
     */
    private function getGenericRecommendations(Builder $baseQuery): Builder
    {
        // Fallback 1: Featured products
        $featuredQuery = clone $baseQuery;
        $featuredQuery->where('is_featured', true);

        if ($featuredQuery->count() > 0) {
            return $featuredQuery->inRandomOrder();
        }

        // Fallback 2: Recent products
        return $baseQuery->orderBy('created_at', 'desc')->take(12);
    }
}
