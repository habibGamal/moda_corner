<?php

namespace Database\Factories;

use App\Enums\PromotionType;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => $this->faker->words(3, true),
            'name_ar' => $this->faker->words(3, true),
            'code' => strtoupper($this->faker->unique()->lexify('????')),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(PromotionType::cases()),
            'value' => $this->faker->numberBetween(5, 50),
            'min_order_value' => null,
            'usage_limit' => $this->faker->optional()->numberBetween(10, 100),
            'usage_count' => 0,
            'is_active' => true,
            'starts_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }

    /**
     * Indicate that the promotion is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the promotion is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the promotion hasn't started yet.
     */
    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
        ]);
    }

    /**
     * Indicate that the promotion has reached its usage limit.
     */
    public function usageLimitReached(): static
    {
        return $this->state(function (array $attributes) {
            $usageLimit = $this->faker->numberBetween(1, 10);

            return [
                'usage_limit' => $usageLimit,
                'usage_count' => $usageLimit,
            ];
        });
    }

    /**
     * Create a percentage promotion.
     */
    public function percentage(?float $percentage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionType::PERCENTAGE,
            'value' => $percentage ?? $this->faker->numberBetween(10, 50),
            'min_order_value' => null,
        ]);
    }

    /**
     * Create a fixed amount promotion.
     */
    public function fixed(?float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionType::FIXED,
            'value' => $amount ?? $this->faker->numberBetween(10, 100),
            'min_order_value' => null,
        ]);
    }

    /**
     * Create a free shipping promotion.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionType::FREE_SHIPPING,
            'value' => null,
            'min_order_value' => null,
        ]);
    }

    /**
     * Create a buy X get Y promotion.
     */
    public function buyXGetY(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PromotionType::BUY_X_GET_Y,
            'value' => null,
            'min_order_value' => null,
        ]);
    }

    /**
     * Create a promotion without a code (automatic promotion).
     */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => null,
            'min_order_value' => null,
        ]);
    }

    /**
     * Create a promotion with a specific minimum order value.
     */
    public function withMinOrderValue(float $minOrderValue): static
    {
        return $this->state(fn (array $attributes) => [
            'min_order_value' => $minOrderValue,
        ]);
    }
}
