<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name_en = $this->faker->unique()->words(2, true);
        return [
            'name_en' => $name_en,
            'name_ar' => $this->faker->unique()->words(2, true),
            'slug' => Str::slug($name_en),
            'image' => $this->faker->imageUrl(640, 480, 'category', true), // Use Faker's imageUrl
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'parent_id' => null,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Category::factory(),
        ]);
    }
}
