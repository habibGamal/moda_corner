<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'variants' => function($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true);

        // Apply filters if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(24)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only(['category_id', 'brand_id'])
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'variants' => function($query) {
            $query->where('is_active', true);
        }])
            ->append([
                'isInWishlist',
                'featuredImage',
                'allImages',
                'totalQuantity',
                'isInStock',
            ]);

        // Get related products from the same category with pagination
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with([
                'brand' => function ($query) {
                    $query->select('id', 'name_en', 'name_ar', 'slug');
                },
                'variants' => function($query) {
                    $query->where('is_active', true);
                }
            ])
            ->where('is_active', true)
            ->paginate(4);

        return Inertia::render('Products/Show', [
            'product' => $product,
            'section_related_products_page_data' => inertia()->merge(
                $relatedProducts->items()
            ),
            'section_related_products_page_pagination' => \Illuminate\Support\Arr::except($relatedProducts->toArray(), ['data']),
        ]);
    }
}
