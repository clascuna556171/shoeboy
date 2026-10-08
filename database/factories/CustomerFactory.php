<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'messenger_contact' => '@'.fake()->unique()->userName(),
            'phone' => fake()->optional()->numerify('09#########'),
            'shipping_address' => fake()->optional()->address(),
        ];
    }
}
