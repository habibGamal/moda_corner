<?php

namespace Database\Factories;

use App\Models\DirectPromotion;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DirectPromotion>
 */
class DirectPromotionFactory extends Factory
{
    protected $model = DirectPromotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_ar' => $this->faker->sentence(3),
            'name_en' => $this->faker->sentence(3),
            'description_ar' => $this->faker->paragraph(),
            'description_en' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['price_discount', 'free_shipping']),
            'is_active' => $this->faker->boolean(70), // 70% chance of being active
            'starts_at' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
            'expires_at' => $this->faker->optional(0.5)->dateTimeBetween('now', '+3 months'),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterMaking(function (DirectPromotion $promotion) {
            if ($promotion->type === 'price_discount') {
                $promotion->discount_percentage = $this->faker->randomFloat(2, 5, 50);
                $promotion->apply_to = $this->faker->randomElement(['all_products', 'category', 'brand']);

                if ($promotion->apply_to === 'category') {
                    $promotion->category_id = Category::factory();
                } elseif ($promotion->apply_to === 'brand') {
                    $promotion->brand_id = Brand::factory();
                }
            } elseif ($promotion->type === 'free_shipping') {
                $promotion->minimum_order_amount = $this->faker->randomFloat(2, 50, 500);
            }
        });
    }

    /**
     * Create a price discount promotion.
     */
    public function priceDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'price_discount',
            'discount_percentage' => $this->faker->randomFloat(2, 5, 50),
            'apply_to' => 'all_products',
        ]);
    }

    /**
     * Create a category-specific price discount promotion.
     */
    public function categoryDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'price_discount',
            'discount_percentage' => $this->faker->randomFloat(2, 10, 30),
            'apply_to' => 'category',
            'category_id' => Category::factory(),
        ]);
    }

    /**
     * Create a brand-specific price discount promotion.
     */
    public function brandDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'price_discount',
            'discount_percentage' => $this->faker->randomFloat(2, 10, 30),
            'apply_to' => 'brand',
            'brand_id' => Brand::factory(),
        ]);
    }

    /**
     * Create a free shipping promotion.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'free_shipping',
            'minimum_order_amount' => $this->faker->randomFloat(2, 50, 300),
        ]);
    }

    /**
     * Create an active promotion.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive promotion.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
