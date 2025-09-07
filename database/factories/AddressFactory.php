<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => fake()->streetAddress(),
            'phone' => fake()->phoneNumber(),
            'area_id' => Area::factory(),
            'user_id' => User::factory(),
        ];
    }
}
