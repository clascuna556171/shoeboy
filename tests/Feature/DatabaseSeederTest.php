<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
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
        $this->assertSame(7, Order::count());
        $this->assertGreaterThan(20, Item::count());
        $this->assertGreaterThan(10, AuditLog::count());

        // No empty batches.
        foreach (Batch::all() as $batch) {
            $this->assertGreaterThan(0, $batch->items()->count(), "Batch {$batch->batch_code} should contain items.");
        }

        // Price tiers are derived from the target price.
        $tiers = Item::pluck('price_tier')->unique()->values()->all();
        $this->assertEmpty(array_diff($tiers, ['Tier 1', 'Tier 2', 'Tier 3']));

        // The full wash/repair pipeline is represented.
        $stages = Item::pluck('triage_status')->unique()->values()->all();
        $this->assertEmpty(array_diff(['washing', 'under_repair', 'available'], $stages), 'Seeder should exercise every triage stage.');

        // The bale purchase cost must not be double-counted as a batch expense.
        $this->assertSame(0, Expense::whereNotNull('batch_id')->where('category', 'Sack Purchase')->count());

        // Every sold pair belongs to a paid/fulfilled order...
        $sold = Item::where('status', 'sold')->get();
        $this->assertGreaterThan(0, $sold->count());
        foreach ($sold as $item) {
            $this->assertTrue(
                $item->orders()->whereIn('status', ['paid', 'fulfilled'])->exists(),
                "Sold pair {$item->sku} must belong to a paid or fulfilled order."
            );
        }

        // ...and every reserved pair belongs to a reserved order.
        $reserved = Item::where('status', 'reserved')->get();
        $this->assertGreaterThan(0, $reserved->count());
        foreach ($reserved as $item) {
            $this->assertTrue(
                $item->orders()->where('status', 'reserved')->exists(),
                "Reserved pair {$item->sku} must belong to a reserved order."
            );
        }

        // Verified payments must equal their order total.
        foreach (Payment::with('order')->get() as $payment) {
            $this->assertEqualsWithDelta((float) $payment->order->awarded_price, (float) $payment->amount, 0.01);
        }

        // J&T deliveries that have left pending carry a waybill.
        foreach (Delivery::where('method', 'jnt_delivery')->where('status', '!=', 'pending')->get() as $delivery) {
            $this->assertNotNull($delivery->tracking_number, 'A non-pending J&T delivery needs a tracking number.');
        }

        // Walk-in POS orders are fulfilled on the spot via pickup.
        foreach (Delivery::with('order')->get() as $delivery) {
            if ($delivery->order->order_type === 'walkin_pos') {
                $this->assertSame('pickup', $delivery->method);
                $this->assertSame('completed', $delivery->status);
            }
        }

        $this->assertDatabaseHas('users', ['email' => 'admin@theshoeboy.com']);
        $this->assertDatabaseHas('users', ['email' => 'staff@theshoeboy.com']);
    }
}
