<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Factories\Factory;

class HeroSlideFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HeroSlide::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title_en' => $this->faker->words(3, true),
            'title_ar' => 'عربي ' . $this->faker->words(3, true),
            'description_en' => $this->faker->paragraph(2),
            'description_ar' => 'عربي ' . $this->faker->paragraph(2),
            'image' => 'https://images.unsplash.com/photo-' . $this->faker->numberBetween(1000000000, 9999999999) . '?q=80&w=1200',
            'cta_link' => '/products/' . $this->faker->slug(2),
            'is_active' => $this->faker->boolean(80),
            'display_order' => $this->faker->numberBetween(0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the hero slide is active.
     *
     * @return static
     */
    public function active(): static
    {
        return $this->state(function () {
            return [
                'is_active' => true,
            ];
        });
    }
} 