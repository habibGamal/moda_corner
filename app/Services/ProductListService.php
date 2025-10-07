<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductListService
{
    /**
     * Get products based on provided filter criteria
     */
    public function getFilteredProducts(array $filters = [])
    {
        // If there's a search query, use TNTSearch and then apply filters
        if (! empty($filters['query'])) {
            $searchQuery = $filters['query'];

            // Get search results as IDs first
            $searchResults = Product::search($searchQuery)
                ->where('is_active', true)
                ->get()
                ->pluck('id')
                ->toArray();

            // If no search results, return empty query
            if (empty($searchResults)) {
                return Product::query()->whereRaw('1 = 0'); // Returns empty result set
            }

            // Create Eloquent query based on search results
            $query = Product::query()
                ->where('is_active', true)
                ->whereIn('id', $searchResults);
        } else {
            // No search query, use regular Eloquent query
            $query = Product::query()->where('is_active', true);
        }

        // Apply brand filter if provided
        if (! empty($filters['brands'])) {
            $query->whereIn('brand_id', $filters['brands']);
        }

        // Apply category filter if provided
        if (! empty($filters['categories'])) {
            // Get all descendant categories for the selected categories
            $allCategoryIds = $filters['categories'];
            foreach ($filters['categories'] as $categoryId) {
                $this->addChildCategories($categoryId, $allCategoryIds);
            }
            $query->whereIn('category_id', $allCategoryIds);
        }

        // Apply price range filter if provided
        if (isset($filters['minPrice']) && $filters['minPrice'] !== null) {
            $query->where('price', '>=', $filters['minPrice']);
        }

        if (isset($filters['maxPrice']) && $filters['maxPrice'] !== null) {
            $query->where('price', '<=', $filters['maxPrice']);
        }

        // Apply sorting
        $sortBy = $filters['sortBy'] ?? 'newest';

        switch ($sortBy) {
            case 'price_low_high':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high_low':
                $query->orderBy('price', 'desc');
                break;
            case 'name_a_z':
                $query->orderBy('name_en', 'asc');
                break;
            case 'name_z_a':
                $query->orderBy('name_en', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    /**
     * Get products by category ID including all child categories
     */
    public function getProductsByCategory(int $categoryId): Builder
    {
        // Get all descendant categories
        $allCategoryIds = [$categoryId];
        $this->addChildCategories($categoryId, $allCategoryIds);

        return Product::query()->where('is_active', true)
            ->whereIn('category_id', $allCategoryIds);
    }

    /**
     * Get products by brand ID
     */
    public function getProductsByBrand(int $brandId): Builder
    {
        return Product::query()->where('is_active', true)
            ->where('brand_id', $brandId);
    }

    /**
     * Get all active brands
     */
    public function getAllBrands(): Collection
    {
        return Brand::where('is_active', true)
            ->select('id', 'name_en', 'name_ar', 'slug', 'image')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get all active root categories with their children
     */
    public function getAllCategories(): Collection
    {
        return Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children')
            ->select('id', 'name_en', 'name_ar', 'slug', 'image', 'parent_id')
            ->orderBy('name_en')
            ->get();
    }

    /**
     * Get price range for all active products
     */
    public function getPriceRange(): array
    {
        $priceStats = Product::where('is_active', true)
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return [
            'min' => $priceStats->min_price ?? 0,
            'max' => $priceStats->max_price ?? 1000,
        ];
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
