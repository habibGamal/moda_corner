<?php

namespace Database\Factories;

use App\Enums\PromotionRewardType;
use App\Models\Promotion;
use App\Models\PromotionReward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionReward>
 */
class PromotionRewardFactory extends Factory
{
    protected $model = PromotionReward::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'type' => $this->faker->randomElement(PromotionRewardType::cases()),
            'entity_id' => $this->faker->numberBetween(1, 10),
            'quantity' => $this->faker->numberBetween(1, 3),
            'discount_percentage' => $this->faker->optional()->numberBetween(10, 100),
        ];
    }

    /**
     * Create a product reward.
     */
    public function product(?int $productId = null, ?int $quantity = null, ?float $discountPercentage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionRewardType::PRODUCT,
            'entity_id' => $productId ?? $this->faker->numberBetween(1, 10),
            'quantity' => $quantity ?? 1,
            'discount_percentage' => $discountPercentage ?? 100,
        ]);
    }

    /**
     * Create a category reward.
     */
    public function category(?int $categoryId = null, ?int $quantity = null, ?float $discountPercentage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionRewardType::CATEGORY,
            'entity_id' => $categoryId ?? $this->faker->numberBetween(1, 10),
            'quantity' => $quantity ?? 1,
            'discount_percentage' => $discountPercentage ?? 100,
        ]);
    }

    /**
     * Create a brand reward.
     */
    public function brand(?int $brandId = null, ?int $quantity = null, ?float $discountPercentage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionRewardType::BRAND,
            'entity_id' => $brandId ?? $this->faker->numberBetween(1, 10),
            'quantity' => $quantity ?? 1,
            'discount_percentage' => $discountPercentage ?? 100,
        ]);
    }

    /**
     * Create a free reward (100% discount).
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => 100,
        ]);
    }
}
