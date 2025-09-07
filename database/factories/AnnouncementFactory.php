<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnouncementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Announcement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title_en' => $this->faker->sentence(4),
            'title_ar' => 'عربي ' . $this->faker->sentence(4),
            'is_active' => $this->faker->boolean(80),
            'display_order' => $this->faker->numberBetween(0, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the announcement is active.
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