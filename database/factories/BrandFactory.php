<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name_en = $this->faker->unique()->company;

        return [
            'name_en' => $name_en,
            'name_ar' => $this->faker->unique()->company,
            'slug' => Str::slug($name_en),
            'image' => $this->faker->imageUrl(640, 480, 'logo', true), // Use Faker's imageUrl
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'parent_id' => null,
            'display_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the brand has a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Brand::factory(),
        ]);
    }
}
