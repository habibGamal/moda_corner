<?php

namespace App\Services;

use App\Models\DirectPromotion;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DirectPromotionService
{
    /**
     * Apply price discount promotion - modifies product and variant sale prices.
     *
     * @param DirectPromotion $promotion
     * @return array ['applied_count' => int, 'reverted_count' => int]
     */
    public function applyPriceDiscount(DirectPromotion $promotion): array
    {
        if (!$promotion->isPriceDiscount()) {
            throw new \InvalidArgumentException('Invalid price discount promotion');
        }

        $appliedCount = 0;
        $revertedCount = 0;

        DB::transaction(function () use ($promotion, &$appliedCount, &$revertedCount) {
            // First, revert any existing promotion effects to avoid stacking
            $revertedCount = $this->revertPriceDiscounts();

            // Get products to apply discount to
            $products = $this->getProductsForPromotion($promotion);

            foreach ($products as $product) {
                // Apply discount to product if it has a price
                if ($product->price) {
                    $discountedPrice = $product->price * (1 - ($promotion->discount_percentage / 100));
                    $discountedPrice = round($discountedPrice, 2);

                    $product->update([
                        'sale_price' => $discountedPrice
                    ]);

                    $appliedCount++;
                }

                // Apply discount to all variants of this product
                foreach ($product->variants as $variant) {
                    if ($variant->is_active) {
                        // Use variant's price if available, otherwise use product's price
                        $basePrice = $variant->price ?? $product->price;
                        if ($basePrice) {
                            $discountedPrice = $basePrice * (1 - ($promotion->discount_percentage / 100));
                            $discountedPrice = round($discountedPrice, 2);

                            $variant->update([
                                'sale_price' => $discountedPrice
                            ]);

                            $appliedCount++;
                        }
                    }
                }
            }

            // Mark promotion as active
            $promotion->update(['is_active' => true]);
        });

        Log::info("Applied price discount promotion", [
            'promotion_id' => $promotion->id,
            'applied_count' => $appliedCount,
            'reverted_count' => $revertedCount
        ]);

        return [
            'applied_count' => $appliedCount,
            'reverted_count' => $revertedCount
        ];
    }

    /**
     * Revert all price discount effects - removes sale prices from products and variants.
     *
     * @return int Number of items reverted
     */
    public function revertPriceDiscounts(): int
    {
        $revertedCount = 0;

        // Revert product sale prices
        $revertedCount += Product::whereNotNull('sale_price')
            ->update(['sale_price' => null]);

        // Revert variant sale prices
        $revertedCount += ProductVariant::whereNotNull('sale_price')
            ->update(['sale_price' => null]);

        // Deactivate all price discount promotions
        DirectPromotion::priceDiscount()->update(['is_active' => false]);

        Log::info("Reverted price discounts", [
            'reverted_count' => $revertedCount
        ]);

        return $revertedCount;
    }

    /**
     * Get products that should be affected by the promotion.
     *
     * @param DirectPromotion $promotion
     * @return Collection
     */
    protected function getProductsForPromotion(DirectPromotion $promotion): Collection
    {
        $query = Product::query()->with('variants');

        switch ($promotion->apply_to) {
            case 'all_products':
                // Apply to all products
                break;

            case 'category':
                if ($promotion->category_id) {
                    $query->where('category_id', $promotion->category_id);
                }
                break;

            case 'brand':
                if ($promotion->brand_id) {
                    $query->where('brand_id', $promotion->brand_id);
                }
                break;
        }

        return $query->where('is_active', true)->get();
    }

    /**
     * Check if an order qualifies for free shipping.
     *
     * @param float $orderTotal
     * @return bool
     */
    public function qualifiesForFreeShipping(float $orderTotal): bool
    {
        $freeShippingPromotion = DirectPromotion::valid()
            ->freeShipping()
            ->where('minimum_order_amount', '<=', $orderTotal)
            ->orderBy('minimum_order_amount', 'desc') // Get the promotion with highest minimum (best for customer)
            ->first();

        return $freeShippingPromotion !== null;
    }

    /**
     * Get the active free shipping promotion that applies to the given order total.
     *
     * @param float $orderTotal
     * @return DirectPromotion|null
     */
    public function getApplicableFreeShippingPromotion(float $orderTotal): ?DirectPromotion
    {
        return DirectPromotion::valid()
            ->freeShipping()
            ->where('minimum_order_amount', '<=', $orderTotal)
            ->orderBy('minimum_order_amount', 'desc')
            ->first();
    }

    /**
     * Get all active promotions.
     *
     * @return Collection
     */
    public function getActivePromotions(): Collection
    {
        return DirectPromotion::valid()
            ->with(['category', 'brand'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new price discount promotion.
     *
     * @param array $data
     * @return DirectPromotion
     */
    public function createPriceDiscount(array $data): DirectPromotion
    {
        $data['type'] = 'price_discount';
        return DirectPromotion::create($data);
    }

    /**
     * Create a new free shipping promotion.
     *
     * @param array $data
     * @return DirectPromotion
     */
    public function createFreeShipping(array $data): DirectPromotion
    {
        $data['type'] = 'free_shipping';
        return DirectPromotion::create($data);
    }

    /**
     * Delete a promotion and revert its effects if it's a price discount.
     *
     * @param DirectPromotion $promotion
     * @return bool
     */
    public function deletePromotion(DirectPromotion $promotion): bool
    {
        if ($promotion->isPriceDiscount() && $promotion->is_active) {
            $this->revertPriceDiscounts();
        }

        return $promotion->delete();
    }

    /**
     * Get statistics about current promotions.
     *
     * @return array
     */
    public function getPromotionStats(): array
    {
        $activePromotions = DirectPromotion::active()->count();
        $priceDiscountPromotions = DirectPromotion::active()->priceDiscount()->count();
        $freeShippingPromotions = DirectPromotion::active()->freeShipping()->count();
        $productsWithSalePrice = Product::whereNotNull('sale_price')->count();
        $variantsWithSalePrice = ProductVariant::whereNotNull('sale_price')->count();

        return [
            'active_promotions' => $activePromotions,
            'price_discount_promotions' => $priceDiscountPromotions,
            'free_shipping_promotions' => $freeShippingPromotions,
            'products_with_discount' => $productsWithSalePrice,
            'variants_with_discount' => $variantsWithSalePrice,
        ];
    }
}
