<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(StoreProductReviewRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        // Check if user already reviewed this product
        $existingReview = ProductReview::where('product_id', $validated['product_id'])
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return back()->with('error', __('You have already reviewed this product.'));
        }

        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('order_status', 'delivered')
            ->whereHas('items', function ($query) use ($validated) {
                $query->where('product_id', $validated['product_id']);
            })
            ->exists();

        // Get the order_id if user has purchased
        $orderId = null;
        if ($hasPurchased) {
            $order = Order::where('user_id', $user->id)
                ->where('order_status', 'delivered')
                ->whereHas('items', function ($query) use ($validated) {
                    $query->where('product_id', $validated['product_id']);
                })
                ->latest()
                ->first();

            $orderId = $order?->id;
        }

        // Create review
        ProductReview::create([
            'product_id' => $validated['product_id'],
            'user_id' => $user->id,
            'order_id' => $orderId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $hasPurchased,
            'is_approved' => true, // Auto-approve, or set to false for moderation
            'approved_at' => now(),
        ]);

        return back()->with('success', __('Thank you for your review!'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(StoreProductReviewRequest $request, ProductReview $review): RedirectResponse
    {
        // Check if the authenticated user owns this review
        if ($review->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validated();

        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back()->with('success', __('Your review has been updated.'));
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(ProductReview $review): RedirectResponse
    {
        // Check if the authenticated user owns this review or is an admin
        if ($review->user_id !== auth()->id() && ! auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        $review->delete();

        return back()->with('success', __('Review deleted successfully.'));
    }

    /**
     * Get reviews for a specific product.
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = $product->reviews()
            ->approved()
            ->with('user:id,name,avatar')
            ->latest()
            ->paginate(10);

        $stats = [
            'average_rating' => $product->reviews()->approved()->avg('rating'),
            'total_reviews' => $product->reviews()->approved()->count(),
            'rating_breakdown' => [
                5 => $product->reviews()->approved()->where('rating', 5)->count(),
                4 => $product->reviews()->approved()->where('rating', 4)->count(),
                3 => $product->reviews()->approved()->where('rating', 3)->count(),
                2 => $product->reviews()->approved()->where('rating', 2)->count(),
                1 => $product->reviews()->approved()->where('rating', 1)->count(),
            ],
        ];

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }

    /**
     * Check if the user can review a product.
     */
    public function canReview(Product $product): JsonResponse
    {
        $user = auth()->user();

        // Check if user has already reviewed
        $hasReviewed = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();

        // Check if user has purchased this product
        $hasPurchased = Order::where('user_id', $user->id)
            ->where('order_status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->exists();

        return response()->json([
            'can_review' => ! $hasReviewed,
            'has_reviewed' => $hasReviewed,
            'is_verified_purchase' => $hasPurchased,
        ]);
    }
}
