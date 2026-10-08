<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseExpiredReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_releases_expired_reservations(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $batch = Batch::factory()->create();
        $item = Item::factory()->create(['batch_id' => $batch->id, 'status' => 'reserved', 'triage_status' => 'available']);
        $order = Order::factory()->create([
            'customer_id' => Customer::factory()->create()->id,
            'staff_id' => $staff->id,
            'status' => 'reserved',
            'expires_at' => now()->subMinute(),
        ]);
        $order->items()->attach($item->id, ['awarded_price' => 1000]);

        $this->artisan('shoeboy:release-expired')->assertExitCode(0);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('available', $item->fresh()->status);
    }

    public function test_command_leaves_active_reservations_alone(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $batch = Batch::factory()->create();
        $item = Item::factory()->create(['batch_id' => $batch->id, 'status' => 'reserved', 'triage_status' => 'available']);
        $order = Order::factory()->create([
            'customer_id' => Customer::factory()->create()->id,
            'staff_id' => $staff->id,
            'status' => 'reserved',
            'expires_at' => now()->addMinutes(30),
        ]);
        $order->items()->attach($item->id, ['awarded_price' => 1000]);

        $this->artisan('shoeboy:release-expired')->assertExitCode(0);

        $this->assertSame('reserved', $order->fresh()->status);
        $this->assertSame('reserved', $item->fresh()->status);
    }
}
