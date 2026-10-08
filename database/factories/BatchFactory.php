<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        $pairs = fake()->numberBetween(12, 40);
        $cost = $pairs * fake()->numberBetween(400, 900);

        return [
            'supplier_id' => Supplier::factory(),
            'batch_code' => 'B'.fake()->unique()->numberBetween(100, 99999),
            'date_acquired' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'total_sacks' => fake()->numberBetween(1, 3),
            'total_pairs' => $pairs,
            'total_cost' => $cost,
        ];
    }
}
