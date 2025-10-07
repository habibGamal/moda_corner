<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Gov;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Area::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name_en' => fake()->streetName(),
            'name_ar' => fake()->streetName().' (عربي)',
            'gov_id' => Gov::factory(),
        ];
    }
}
