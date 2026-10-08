<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount' => fake()->numberBetween(1000, 9000),
            'method' => 'gcash',
            'reference_no' => 'GCASH-'.strtoupper(fake()->unique()->bothify('#########')),
            'verified_by' => User::factory(),
            'date_paid' => now(),
        ];
    }
}
