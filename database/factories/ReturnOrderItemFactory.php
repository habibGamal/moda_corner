<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\ReturnOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnOrderItem>
 */
class ReturnOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $this->faker->randomFloat(2, 10, 100);
        $subtotal = $quantity * $unitPrice;

        return [
            'return_order_id' => ReturnOrder::factory(),
            'order_item_id' => OrderItem::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'reason' => $this->faker->optional()->sentence(),
        ];
    }
}
