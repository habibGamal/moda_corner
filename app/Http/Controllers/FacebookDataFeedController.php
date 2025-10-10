<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Response;

class FacebookDataFeedController extends Controller
{
    /**
     * Generate Facebook Product Catalog CSV feed.
     *
     * Follows Facebook's data feed specifications:
     * https://www.facebook.com/business/help/120325381656392
     */
    public function generateFeed()
    {
        // Fetch all active products with relationships
        $products = Product::with(['brand', 'category', 'variants' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get();

        // Define CSV headers according to Facebook specifications
        $headers = [
            // Required fields
            'id',
            'title',
            'description',
            'availability',
            'condition',
            'price',
            'link',
            'image_link',
            'brand',

            // Additional required for Shops
            'quantity_to_sell_on_facebook',

            // Highly recommended optional fields
            'sale_price',
            'sale_price_effective_date',
            'item_group_id',
            'additional_image_link',
            'product_type',
            'google_product_category',
            'fb_product_category',
            'color',
            'size',
            'gender',
            'age_group',
            'material',
            'pattern',
            'gtin',
            'mpn',
            'shipping_weight',
        ];

        // Start output buffering
        $output = fopen('php://temp', 'r+');

        // Write headers
        fputcsv($output, $headers);

        // Process each product and its variants
        foreach ($products as $product) {
            // Get the base price (considering sale price)
            $basePrice = $product->sale_price ?? $product->price;
            $baseSalePrice = $product->sale_price;

            // Get product URL
            $productUrl = route('products.show', $product->slug);

            foreach ($product->variants as $variant) {
                // Use variant price if available, otherwise use product price
                $variantPrice = $variant->price ?? $basePrice;
                $variantSalePrice = $variant->sale_price ?? $baseSalePrice;

                // Determine final price
                $finalPrice = $variantSalePrice ?? $variantPrice;

                // Get all images for this variant
                $images = $variant->images ?? [];
                $mainImage = ! empty($images) ? asset('storage/'.$images[0]) : null;
                $additionalImages = array_slice($images, 1);
                $additionalImageLinks = array_map(function ($img) {
                    return asset('storage/'.$img);
                }, $additionalImages);

                // Determine availability
                $availability = $variant->quantity > 0 ? 'in stock' : 'out of stock';

                // Get category hierarchy for product_type
                $productType = $this->getCategoryHierarchy($product->category);

                // Build the row data
                $row = [
                    // Required fields
                    'id' => $variant->sku ?? "variant_{$variant->id}",
                    'title' => $this->sanitizeText($product->name_en, 200),
                    'description' => $this->sanitizeText($product->description_en, 5000),
                    'availability' => $availability,
                    'condition' => 'new',
                    'price' => number_format($finalPrice, 2, '.', '').' EGP',
                    'link' => $productUrl,
                    'image_link' => $mainImage ?? '',
                    'brand' => $this->sanitizeText($product->brand?->name_en ?? '', 100),

                    // Additional required for Shops
                    'quantity_to_sell_on_facebook' => max(0, $variant->quantity),

                    // Optional fields
                    'sale_price' => $variantSalePrice ? (number_format($variantSalePrice, 2, '.', '').' EGP') : '',
                    'sale_price_effective_date' => '', // You can add date logic if needed
                    'item_group_id' => "product_{$product->id}",
                    'additional_image_link' => ! empty($additionalImageLinks) ? implode(',', $additionalImageLinks) : '',
                    'product_type' => $productType,
                    'google_product_category' => $this->getGoogleCategory($product->category),
                    'fb_product_category' => $this->getFacebookCategory($product->category),
                    'color' => $this->sanitizeText($variant->color ?? '', 200),
                    'size' => $this->sanitizeText($variant->size ?? '', 200),
                    'gender' => $this->extractGender($product),
                    'age_group' => 'adult',
                    'material' => $this->extractMaterial($variant),
                    'pattern' => $this->extractPattern($variant),
                    'gtin' => '', // Add GTIN if available in your database
                    'mpn' => $variant->sku ?? '',
                    'shipping_weight' => '', // Add shipping weight if available
                ];

                fputcsv($output, $row);
            }
        }

        // Get the CSV content
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        // Return CSV response
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="datafeed.csv"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Sanitize text for CSV output.
     */
    private function sanitizeText(?string $text, int $maxLength = 5000): string
    {
        if (! $text) {
            return '';
        }

        // Remove HTML tags
        $text = strip_tags($text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim to max length
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength);
        }

        return trim($text);
    }

    /**
     * Get category hierarchy for product_type field.
     */
    private function getCategoryHierarchy($category): string
    {
        if (! $category) {
            return '';
        }

        $hierarchy = [];
        $current = $category;

        while ($current) {
            array_unshift($hierarchy, $current->name_en);
            $current = $current->parent;
        }

        return implode(' > ', $hierarchy);
    }

    /**
     * Map category to Google product category.
     * You should customize this based on your actual categories.
     */
    private function getGoogleCategory($category): string
    {
        if (! $category) {
            return '';
        }

        // Map your categories to Google categories
        // This is a simplified example - customize based on your categories
        $categoryMappings = [
            'clothing' => 'Apparel & Accessories',
            'shoes' => 'Apparel & Accessories > Shoes',
            'accessories' => 'Apparel & Accessories > Accessories',
            'electronics' => 'Electronics',
            'home' => 'Home & Garden',
        ];

        $categorySlug = strtolower($category->slug);

        foreach ($categoryMappings as $key => $value) {
            if (str_contains($categorySlug, $key)) {
                return $value;
            }
        }

        return 'Apparel & Accessories';
    }

    /**
     * Map category to Facebook product category.
     * You should customize this based on your actual categories.
     */
    private function getFacebookCategory($category): string
    {
        if (! $category) {
            return '';
        }

        // Map your categories to Facebook categories
        // This is a simplified example - customize based on your categories
        $categoryMappings = [
            'clothing' => 'Clothing & Accessories > Clothing',
            'women' => 'Clothing & Accessories > Clothing > Women\'s Clothing',
            'men' => 'Clothing & Accessories > Clothing > Men\'s Clothing',
            'shoes' => 'Clothing & Accessories > Shoes',
            'accessories' => 'Clothing & Accessories > Accessories',
        ];

        $categorySlug = strtolower($category->slug);

        foreach ($categoryMappings as $key => $value) {
            if (str_contains($categorySlug, $key)) {
                return $value;
            }
        }

        return 'Clothing & Accessories';
    }

    /**
     * Extract gender from product data.
     */
    private function extractGender($product): string
    {
        // Check category name for gender indicators
        $categoryName = strtolower($product->category?->name_en ?? '');
        $productName = strtolower($product->name_en);
        // Check product additional_attributes for gender/sex
        if (! empty($product->additional_attributes) && is_array($product->additional_attributes)) {
            $genderValue = strtolower(trim((string) ($product->additional_attributes['gender'] ?? $product->additional_attributes['sex'] ?? '')));
            if ($genderValue !== '') {
                if (preg_match('/\b(wom|women|female|f|girl)\b/i', $genderValue)) {
                    return 'female';
                }
                if (preg_match('/\b(men|man|male|m|boy)\b/i', $genderValue)) {
                    return 'male';
                }
                if (preg_match('/\b(unisex|both)\b/i', $genderValue)) {
                    return 'unisex';
                }
            }
        }

        // Check variants additional_attributes for gender/sex
        if (! empty($product->variants) && is_iterable($product->variants)) {
            foreach ($product->variants as $variant) {
                if (! empty($variant->additional_attributes) && is_array($variant->additional_attributes)) {
                    $genderValue = strtolower(trim((string) ($variant->additional_attributes['gender'] ?? $variant->additional_attributes['sex'] ?? '')));
                    if ($genderValue !== '') {
                        if (preg_match('/\b(wom|women|female|f|girl)\b/i', $genderValue)) {
                            return 'female';
                        }
                        if (preg_match('/\b(men|man|male|m|boy)\b/i', $genderValue)) {
                            return 'male';
                        }
                        if (preg_match('/\b(unisex|both)\b/i', $genderValue)) {
                            return 'unisex';
                        }
                    }
                }
            }
        }
        if (str_contains($categoryName, 'women') || str_contains($productName, 'women')) {
            return 'female';
        }

        if (str_contains($categoryName, 'men') || str_contains($productName, 'men')) {
            return 'male';
        }

        return 'unisex';
    }

    /**
     * Extract material from variant attributes.
     */
    private function extractMaterial($variant): string
    {
        if (! $variant->additional_attributes || ! is_array($variant->additional_attributes)) {
            return '';
        }

        // Check for material in additional attributes
        return $variant->additional_attributes['material'] ?? '';
    }

    /**
     * Extract pattern from variant attributes.
     */
    private function extractPattern($variant): string
    {
        if (! $variant->additional_attributes || ! is_array($variant->additional_attributes)) {
            return '';
        }

        // Check for pattern in additional attributes
        return $variant->additional_attributes['pattern'] ?? '';
    }
}
