<?php

namespace Database\Factories;

use App\Enums\ReturnOrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReturnOrder>
 */
class ReturnOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'return_number' => 'RET-'.Str::upper(Str::random(8)),
            'status' => ReturnOrderStatus::REQUESTED,
            'reason' => $this->faker->paragraph(),
            'total_amount' => $this->faker->randomFloat(2, 10, 500),
            'refund_amount' => $this->faker->randomFloat(2, 10, 500),
            'requested_at' => now(),
            'approved_at' => null,
            'completed_at' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
    }

    /**
     * Indicate that the return order is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReturnOrderStatus::APPROVED,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the return order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReturnOrderStatus::COMPLETED,
            'approved_at' => now()->subHours(2),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the return order is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReturnOrderStatus::REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }
}
