<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->ean13(),
            'images' => [
                $this->faker->imageUrl(640, 480, 'product', true),
                $this->faker->imageUrl(640, 480, 'product', true),
            ],
            'quantity' => $this->faker->numberBetween(0, 100),
            'price' => null, // Default to use product price
            'sale_price' => null, // Default to use product sale price
            'color' => $this->faker->optional(0.7)->colorName(),
            'size' => $this->faker->optional(0.7)->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
            'capacity' => $this->faker->optional(0.3)->randomElement(['16GB', '32GB', '64GB', '128GB', '256GB']),
            'additional_attributes' => null,
            'is_default' => false,
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that this variant is the default variant for its product.
     */
    public function default(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_default' => true,
            ];
        });
    }
}
