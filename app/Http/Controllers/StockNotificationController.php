<?php

namespace App\Http\Controllers;

use App\Services\StockNotificationService;
use Illuminate\Http\Request;

class StockNotificationController extends Controller
{
    protected StockNotificationService $stockNotificationService;

    public function __construct(StockNotificationService $stockNotificationService)
    {
        $this->stockNotificationService = $stockNotificationService;
    }

    /**
     * Subscribe to stock notifications for a product.
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'email' => 'required|email|max:255',
        ]);

        try {
            $this->stockNotificationService->subscribe(
                $validated['product_id'],
                $validated['email']
            );

            return back()->with('success', __('You will be notified when this product is back in stock.'));
        } catch (\Exception $e) {
            return back()->with('error', __('Failed to subscribe to stock notifications.'));
        }
    }

    /**
     * Unsubscribe from stock notifications for a product.
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'email' => 'required|email|max:255',
        ]);

        try {
            $this->stockNotificationService->unsubscribe(
                $validated['product_id'],
                $validated['email']
            );

            return back()->with('success', __('You have been unsubscribed from stock notifications.'));
        } catch (\Exception $e) {
            return back()->with('error', __('Failed to unsubscribe from stock notifications.'));
        }
    }

    /**
     * Check subscription status for a product.
     */
    public function checkSubscription(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'email' => 'required|email|max:255',
        ]);

        $isSubscribed = $this->stockNotificationService->isSubscribed(
            $validated['product_id'],
            $validated['email']
        );

        return response()->json(['subscribed' => $isSubscribed]);
    }
}
