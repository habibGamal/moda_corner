<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected PromotionService $promotionService;

    protected CartService $cartService;

    public function __construct(PromotionService $promotionService, CartService $cartService)
    {
        $this->promotionService = $promotionService;
        $this->cartService = $cartService;
    }

    /**
     * Apply a promotion code to the user's cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyPromotion(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
        ]);

        $code = $request->input('code');
        $result = $this->promotionService->validatePromotionCode($code);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الكود غير صالح أو لا يمكن تطبيقه على سلة التسوق الخاصة بك.',
            ], 400);
        }

        [$discountAmount, $promotion] = $result;

        // Store the validated promotion in the session for later use during checkout
        session()->put('promotion', [
            'id' => $promotion->id,
            'code' => $promotion->code,
            'discount' => $discountAmount,
        ]);

        // Get updated cart summary
        $cartSummary = $this->cartService->getCartSummary();

        return response()->json([
            'success' => true,
            'message' => 'تم تطبيق الكود بنجاح!',
            'discount' => $discountAmount,
            'promotion' => [
                'id' => $promotion->id,
                'name' => app()->getLocale() === 'ar' ? $promotion->name_ar : $promotion->name_en,
                'code' => $promotion->code,
                'type' => $promotion->type,
            ],
            'cartSummary' => $cartSummary,
        ]);
    }

    /**
     * Remove the applied promotion from the cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removePromotion()
    {
        session()->forget('promotion');

        // Get updated cart summary
        $cartSummary = $this->cartService->getCartSummary();

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة كود الخصم بنجاح.',
            'cartSummary' => $cartSummary,
        ]);
    }

    /**
     * Get automatic promotions applicable to the cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAutomaticPromotions()
    {
        $cart = $this->cartService->getCart();
        $result = $this->promotionService->applyBestAutomaticPromotion($cart);

        if ($result === null) {
            return response()->json([
                'success' => true,
                'hasPromotion' => false,
            ]);
        }

        [$discountAmount, $promotion] = $result;

        return response()->json([
            'success' => true,
            'hasPromotion' => true,
            'discount' => $discountAmount,
            'promotion' => [
                'id' => $promotion->id,
                'name' => app()->getLocale() === 'ar' ? $promotion->name_ar : $promotion->name_en,
                'type' => $promotion->type,
            ],
        ]);
    }
}
