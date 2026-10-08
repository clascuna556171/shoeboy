<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'order_number' => 'ORD-'.strtoupper(fake()->unique()->bothify('######')),
            'customer_id' => Customer::factory(),
            'staff_id' => User::factory(),
            'awarded_price' => fake()->numberBetween(1000, 9000),
            'status' => 'reserved',
            'order_type' => 'live_stream',
            'date_awarded' => now(),
            'expires_at' => now()->addMinutes(120),
            'notes' => null,
        ];
    }
}
