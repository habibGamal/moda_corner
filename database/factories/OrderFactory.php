<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 1000);
        $shippingCost = fake()->randomFloat(2, 10, 50);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.2);

        return [
            'user_id' => User::factory(),
            'order_status' => fake()->randomElement(OrderStatus::cases()),
            'payment_status' => fake()->randomElement(PaymentStatus::cases()),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total' => $subtotal + $shippingCost - $discount,
            'coupon_code' => fake()->boolean(30) ? strtoupper(fake()->word()) : null,
            'shipping_address_id' => Address::factory(),
            'notes' => fake()->boolean(70) ? fake()->sentence() : null,
        ];
    }
}
