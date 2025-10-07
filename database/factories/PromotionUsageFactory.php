<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PromotionUsage>
 */
class PromotionUsageFactory extends Factory
{
    protected $model = PromotionUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'discount_amount' => $this->faker->randomFloat(2, 5, 100),
        ];
    }
}
