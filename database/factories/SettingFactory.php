<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->slug(2),
            'group' => $this->faker->randomElement(['general', 'appearance', 'seo', 'social']),
            'type' => $this->faker->randomElement(['text', 'textarea', 'url', 'image', 'boolean', 'json']),
            'value' => $this->faker->text(50),
            'label_en' => $this->faker->words(3, true),
            'label_ar' => $this->faker->words(3, true),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'is_required' => $this->faker->boolean(30),
            'display_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the setting is required.
     */
    public function required(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_required' => true,
        ]);
    }

    /**
     * Indicate that the setting is of a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the setting belongs to a specific group.
     */
    public function inGroup(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }
}
