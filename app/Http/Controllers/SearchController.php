<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductListService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SearchController extends Controller
{
    /**
     * Search suggestions as user types
     */
    public function suggestions(Request $request)
    {
        $query = $request->input('q');

        if (empty($query) || strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        // Use TNTSearch for better search results
        $suggestions = Product::search($query)
            ->where('is_active', true)
            ->take(5)
            ->get()
            ->load(['brand:id,name_en,name_ar', 'category:id,name_en,name_ar']);

        // Format the suggestions for the frontend
        $formattedSuggestions = $suggestions->map(function ($product) {
            return [
                'id' => $product->id,
                'name_en' => $product->name_en,
                'name_ar' => $product->name_ar,
                'slug' => $product->slug,
                'price' => $product->price,
                'featured_image' => $product->featured_image,
                'brand' => $product->brand,
                'category' => $product->category,
            ];
        });

        return response()->json(['suggestions' => $formattedSuggestions]);
    }

    /**
     * Show search results page
     */
    public function results(Request $request, ProductListService $productListService)
    {
        $query = $request->input('q');
        $page = $request->input('section_search_results_products_page', 1);
        $brandIds = $request->input('brands', []);
        $categoryIds = $request->input('categories', []);
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $sortBy = $request->input('sort_by', 'newest');

        $searchResults = collect([]);
        $allBrands = [];
        $allCategories = [];
        $priceRange = [];

        if (! empty($query) || $brandIds || $categoryIds || $minPrice || $maxPrice) {
            // Use the ProductListService to get filtered products
            $filteredQuery = $productListService->getFilteredProducts([
                'query' => $query,
                'brands' => $brandIds,
                'categories' => $categoryIds,
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
                'sortBy' => $sortBy,
            ]);

            // Load relationships
            $filteredQuery->with([
                'brand' => function ($query) {
                    $query->select('id', 'name_en', 'name_ar', 'slug');
                },
                'category' => function ($query) {
                    $query->select('id', 'name_en', 'name_ar', 'slug');
                },
            ]);

            $searchResults = $filteredQuery->paginate(12, ['*'], 'section_search_results_products_page', $page)
                ->withQueryString();

            // Get all brands for filter
            $allBrands = $productListService->getAllBrands();

            // Get all categories for filter
            $allCategories = $productListService->getAllCategories();

            // Get price range for filter
            $priceRange = $productListService->getPriceRange();
        }

        return Inertia::render('Search/Results', [
            'section_search_results_products_page_data' => ! $searchResults->isEmpty() ? inertia()->merge($searchResults->items()) : [],
            'section_search_results_products_page_pagination' => ! $searchResults->isEmpty() ? \Illuminate\Support\Arr::except($searchResults->toArray(), ['data']) : null,
            'query' => $query,
            'filters' => [
                'brands' => $allBrands,
                'categories' => $allCategories,
                'priceRange' => $priceRange,
                'selectedBrands' => $brandIds,
                'selectedCategories' => $categoryIds,
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
                'sortBy' => $sortBy,
            ],
        ]);
    }

    /**
     * Recursively add all child categories to the array
     *
     * @param  int  $categoryId
     * @param  array  &$categoryArray
     * @return void
     */
    private function addChildCategories($categoryId, &$categoryArray)
    {
        $childCategories = Category::where('parent_id', $categoryId)->pluck('id')->toArray();

        if (count($childCategories) > 0) {
            foreach ($childCategories as $childId) {
                $categoryArray[] = $childId;
                // Recursively get children of this child
                $this->addChildCategories($childId, $categoryArray);
            }
        }
    }
}
