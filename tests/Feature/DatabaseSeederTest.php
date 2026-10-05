<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_produces_a_coherent_dataset(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, User::count());
        $this->assertSame(2, Batch::count());
        $this->assertSame(6, Order::count());
        $this->assertGreaterThan(20, Item::count());
        $this->assertGreaterThan(10, AuditLog::count());

        // No empty batches.
        foreach (Batch::all() as $batch) {
            $this->assertGreaterThan(0, $batch->items()->count(), "Batch {$batch->batch_code} should contain items.");
        }

        // Price tiers are derived from the target price.
        $tiers = Item::pluck('price_tier')->unique()->values()->all();
        $this->assertEmpty(array_diff($tiers, ['Tier 1', 'Tier 2', 'Tier 3']));

        $this->assertDatabaseHas('users', ['email' => 'admin@theshoeboy.com']);
        $this->assertDatabaseHas('users', ['email' => 'staff@theshoeboy.com']);
    }
}
