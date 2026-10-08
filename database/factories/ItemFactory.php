<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('ITM-####')),
            'brand' => fake()->randomElement(['Nike', 'Adidas', 'Asics', 'Li-Ning', 'Anta', 'Peak', 'Jordan']),
            'model' => fake()->words(2, true),
            'listed_price' => fake()->numberBetween(500, 8000),
            'condition' => fake()->randomElement(['Pristine', 'Good', 'Fair']),
            'size' => 'US '.fake()->randomFloat(1, 6, 12),
            'status' => 'available',
            'triage_status' => 'available',
            'repair_cost' => 0,
            'category' => fake()->optional()->randomElement(['Basketball', 'Running', 'Lifestyle']),
        ];
    }
}
