<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'categories' => $this->getTopCategories(),
            'brands' => $this->getTopBrands(),
            'cartInfo' => $this->getCartInfo($request),
        ];
    }

    /**
     * Get cart information if user is authenticated
     */
    private function getCartInfo(Request $request): array
    {
        $cartInfo = [
            'itemsCount' => 0,
            'totalPrice' => 0,
        ];

        if ($request->user()) {
            $cartService = app(CartService::class);
            $cartSummary = $cartService->getCartSummary();
            $cartInfo['itemsCount'] = $cartSummary->totalItems;
            $cartInfo['totalPrice'] = $cartSummary->totalPrice;
        }

        return $cartInfo;
    }

    /**
     * Get the top active categories for the navigation.
     */
    private function getTopCategories()
    {
        return \App\Models\Category::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->take(8)
            ->get();
    }

    /**
     * Get the top active brands for the navigation.
     */
    private function getTopBrands()
    {
        return \App\Models\Brand::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->take(8)
            ->get();
    }
}
