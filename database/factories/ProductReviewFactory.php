<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductReview>
 */
class ProductReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(1, 5);

        return [
            'product_id' => \App\Models\Product::factory(),
            'user_id' => \App\Models\User::factory(),
            'order_id' => null,
            'rating' => $rating,
            'comment' => $rating >= 4 ? fake()->paragraph() : ($rating === 3 ? fake()->sentence() : fake()->paragraph()),
            'is_verified_purchase' => fake()->boolean(70), // 70% chance of being verified
            'is_approved' => true,
            'approved_at' => now(),
        ];
    }

    /**
     * Indicate that the review is a verified purchase.
     */
    public function verifiedPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => true,
            'order_id' => \App\Models\Order::factory(),
        ]);
    }

    /**
     * Indicate that the review is not approved.
     */
    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate a 5-star review.
     */
    public function fiveStars(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => fake()->paragraph(),
        ]);
    }

    /**
     * Indicate a 1-star review.
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
            'comment' => fake()->paragraph(),
        ]);
    }
}
