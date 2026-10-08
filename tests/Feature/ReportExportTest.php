<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private function seedSale(User $owner, User $staff): void
    {
        $batch = Batch::factory()->create();
        $item = Item::factory()->create(['batch_id' => $batch->id, 'status' => 'available', 'triage_status' => 'available']);
        $customer = Customer::factory()->create();
        $order = app(OrderService::class)->awardItem($item, $customer, $staff, 2500.00);
        app(PaymentService::class)->recordPayment($order, 2500.00, 'gcash', 'GCASH-EXPORT-1', $staff);
    }

    public function test_owner_can_export_with_filters(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'staff']);
        $this->seedSale($owner, $staff);

        $response = $this->actingAs($owner)->get('/reports/export?channel=live_stream&payment_method=gcash&preset=month&sections[]=summary&sections[]=sales');

        $response->assertOk();
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_export_all_returns_a_spreadsheet(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'staff']);
        $this->seedSale($owner, $staff);

        $response = $this->actingAs($owner)->get('/reports/export-all');

        $response->assertOk();
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_staff_cannot_export(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get('/reports/export')->assertForbidden();
    }
}
