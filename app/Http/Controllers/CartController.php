<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartItemResolverService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    protected CartService $cartService;

    protected CartItemResolverService $cartItemResolverService;

    public function __construct(CartService $cartService, CartItemResolverService $cartItemResolverService)
    {
        $this->cartService = $cartService;
        $this->cartItemResolverService = $cartItemResolverService;
    }

    /**
     * Display the cart page
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $cart = $this->cartService->getCart();

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'cartSummary' => $this->cartService->getCartSummary(),
        ]);
    }

    /**
     * Add a product to the cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = $this->cartItemResolverService->resolveCartItem(
            $this->cartService->getOrCreateCart(),
            $validated['product_id'],
            $validated['product_variant_id'] ?? null
        );

        $this->cartService->addToCart(
            $cartItem,
            $validated['quantity']
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_summary' => $this->cartService->getCartSummary(),
            'cart_item' => $cartItem->load('product'),
        ]);
    }

    /**
     * Update cart item quantity
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Validate that the cart item belongs to the current user's cart
        if ($cartItem->cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to cart item',
            ], 403);
        }

        $this->cartService->updateCartItemQuantity(
            $cartItem,
            $validated['quantity']
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'cart_summary' => $this->cartService->getCartSummary(),
            'cart_item' => $cartItem->load('product'),
        ]);
    }

    /**
     * Remove a cart item
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(CartItem $cartItem)
    {
        // Validate that the cart item belongs to the current user's cart
        if ($cartItem->cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to cart item',
            ], 403);
        }

        $this->cartService->removeFromCart(item: $cartItem);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_summary' => $this->cartService->getCartSummary(),
        ]);
    }

    /**
     * Clear all items from the cart
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCart()
    {
        $this->cartService->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
            'cart_summary' => $this->cartService->getCartSummary(),
        ]);
    }

    /**
     * Get cart summary data for navigation or other components
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary()
    {
        return response()->json([
            'cart_summary' => $this->cartService->getCartSummary(),
        ]);
    }
}
