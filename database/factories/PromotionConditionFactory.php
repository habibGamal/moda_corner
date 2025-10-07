<?php

namespace Database\Factories;

use App\Enums\PromotionConditionType;
use App\Models\Promotion;
use App\Models\PromotionCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionCondition>
 */
class PromotionConditionFactory extends Factory
{
    protected $model = PromotionCondition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'type' => $this->faker->randomElement(PromotionConditionType::cases()),
            'entity_id' => $this->faker->numberBetween(1, 10),
            'quantity' => $this->faker->optional()->numberBetween(1, 5),
        ];
    }

    /**
     * Create a product condition.
     */
    public function product(?int $productId = null, ?int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionConditionType::PRODUCT,
            'entity_id' => $productId ?? $this->faker->numberBetween(1, 10),
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create a category condition.
     */
    public function category(?int $categoryId = null, ?int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionConditionType::CATEGORY,
            'entity_id' => $categoryId ?? $this->faker->numberBetween(1, 10),
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create a brand condition.
     */
    public function brand(?int $brandId = null, ?int $quantity = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionConditionType::BRAND,
            'entity_id' => $brandId ?? $this->faker->numberBetween(1, 10),
            'quantity' => $quantity,
        ]);
    }

    /**
     * Create a customer condition.
     */
    public function customer(?int $customerId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionConditionType::CUSTOMER,
            'entity_id' => $customerId ?? $this->faker->numberBetween(1, 10),
            'quantity' => null,
        ]);
    }
}
