<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\ShippingCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingCost>
 */
class ShippingCostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ShippingCost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'value' => fake()->randomFloat(2, 5, 50),
            'area_id' => Area::factory(),
        ];
    }
}
