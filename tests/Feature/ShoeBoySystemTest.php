<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuditService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class ShoeBoySystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $staff;
    protected Supplier $supplier;
    protected Batch $batch;
    protected Item $item;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Adrian Dael',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->staff = User::create([
            'name' => 'Kent Staff',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Cebu Port Imports',
            'contact_number' => '09171112233',
        ]);

        // 24 pairs for 18000 -> average cost is 750
        $this->batch = Batch::create([
            'supplier_id' => $this->supplier->id,
            'batch_code' => 'B04',
            'date_acquired' => now(),
            'total_sacks' => 1,
            'total_pairs' => 24,
            'total_cost' => 18000.00,
        ]);

        $this->item = Item::create([
            'batch_id' => $this->batch->id,
            'sku' => 'B04-001',
            'brand' => 'Li-Ning',
            'model' => 'Way of Wade 10',
            'price_tier' => 'Tier 1',
            'listed_price' => 4500.00,
            'condition' => 'Pristine',
            'size' => 'US 10.5',
            'status' => 'available',
            'repair_cost' => 0.00,
        ]);

        $this->customer = Customer::create([
            'name' => 'Adrian Sole',
            'messenger_contact' => '@adrian_sole',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => $this->owner->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($this->owner);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => $this->owner->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->owner)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_owner_can_access_reports_and_staff_management(): void
    {
        $response = $this->actingAs($this->owner)->get('/reports');
        $response->assertStatus(200);

        $responseStaff = $this->actingAs($this->owner)->get('/staff');
        $responseStaff->assertStatus(200);
    }

    public function test_workspaces_render_for_owner_and_staff(): void
    {
        $this->actingAs($this->owner)->get('/')->assertOk();
        $this->actingAs($this->staff)->get('/')->assertOk();
    }

    public function test_staff_is_forbidden_from_reports_and_staff_management(): void
    {
        $responseReports = $this->actingAs($this->staff)->get('/reports');
        $responseReports->assertStatus(403);

        $responseStaff = $this->actingAs($this->staff)->get('/staff');
        $responseStaff->assertStatus(403);
    }

    public function test_reports_page_lists_individual_sales_and_expenses(): void
    {
        $order = app(OrderService::class)->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );
        app(PaymentService::class)->recordPayment($order, 4500.00, 'cash', null, $this->staff);

        $this->actingAs($this->owner)->post('/expenses', [
            'category' => 'Store Utilities',
            'description' => 'Ledger test power bill',
            'amount' => 500.00,
            'date' => now()->toDateString(),
        ])->assertSessionHas('success');

        $response = $this->actingAs($this->owner)->get('/reports');
        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Ledger test power bill');
    }

    public function test_staff_can_award_available_item_to_customer(): void
    {
        $response = $this->actingAs($this->staff)->post('/orders/award', [
            'item_id' => $this->item->id,
            'customer_name' => 'Ken Hoops',
            'messenger_contact' => '@ken_hoops',
            'awarded_price' => 4500.00,
            'order_type' => 'live_stream',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'status' => 'reserved',
            'awarded_price' => 4500.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'item_id' => $this->item->id,
            'awarded_price' => 4500.00,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $this->item->id,
            'status' => 'reserved',
        ]);
    }

    public function test_staff_can_bulk_award_multiple_items_in_one_order(): void
    {
        $secondItem = Item::create([
            'batch_id' => $this->batch->id,
            'sku' => 'B04-002',
            'brand' => 'Anta',
            'model' => 'KT 8',
            'price_tier' => 'Tier 2',
            'listed_price' => 3200.00,
            'condition' => 'Good',
            'size' => 'US 9.0',
            'status' => 'available',
            'repair_cost' => 0.00,
        ]);

        $response = $this->actingAs($this->staff)->post('/orders/award', [
            'item_ids' => [$this->item->id, $secondItem->id],
            'prices' => [4500.00, 3200.00],
            'customer_name' => 'Bulk Buyer',
            'messenger_contact' => '@bulk_buyer',
            'order_type' => 'live_stream',
        ]);

        $response->assertSessionHas('success');

        $order = Order::latest('id')->first();
        $this->assertCount(2, $order->fresh()->items);
        $this->assertEquals(7700.00, (float) $order->awarded_price);
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'reserved']);
        $this->assertDatabaseHas('items', ['id' => $secondItem->id, 'status' => 'reserved']);
    }

    public function test_cannot_award_already_reserved_or_sold_item(): void
    {
        $orderService = app(OrderService::class);

        // First award succeeds
        $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        // Second award attempt must throw validation exception
        $this->expectException(ValidationException::class);

        $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );
    }

    public function test_pos_checkout_creates_one_order_for_multiple_items(): void
    {
        $secondItem = Item::create([
            'batch_id' => $this->batch->id,
            'sku' => 'B04-003',
            'brand' => 'Peak',
            'model' => 'Taichi Flash',
            'price_tier' => 'Tier 2',
            'listed_price' => 2800.00,
            'condition' => 'Good',
            'size' => 'US 10.0',
            'status' => 'available',
            'repair_cost' => 0.00,
        ]);

        $response = $this->actingAs($this->staff)->post('/orders/pos-checkout', [
            'item_ids' => [$this->item->id, $secondItem->id],
            'payment_method' => 'cash',
            'cash_tendered' => 10000.00,
        ]);

        $response->assertSessionHas('success');

        $order = Order::latest('id')->first();
        $this->assertCount(2, $order->fresh()->items);
        $this->assertEquals('walkin_pos', $order->order_type);
        $this->assertEquals('fulfilled', $order->status);

        // One order, one payment, one delivery for the whole ticket.
        $this->assertSame(1, Order::where('id', $order->id)->count());
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
        $this->assertSame(1, Delivery::where('order_id', $order->id)->count());

        // Walk-in POS is fulfilled on the spot: the delivery is auto-completed.
        $delivery = Delivery::where('order_id', $order->id)->first();
        $this->assertEquals('completed', $delivery->status);
        $this->assertNotNull($delivery->date_completed);

        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'sold']);
        $this->assertDatabaseHas('items', ['id' => $secondItem->id, 'status' => 'sold']);
    }

    public function test_expense_records_optional_reference_number(): void
    {
        $response = $this->actingAs($this->owner)->post('/expenses', [
            'category' => 'Store Utilities',
            'description' => 'Internet bill for the month',
            'reference_no' => 'OR-2026-00123',
            'amount' => 1699.00,
            'date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', [
            'description' => 'Internet bill for the month',
            'reference_no' => 'OR-2026-00123',
            'amount' => 1699.00,
        ]);
    }

    public function test_payment_verification_transitions_order_and_item_status(): void
    {
        $orderService = app(OrderService::class);
        $paymentService = app(PaymentService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $payment = $paymentService->recordPayment(
            order: $order,
            amount: 4500.00,
            method: 'gcash',
            referenceNo: 'GCASH-123456789',
            verifier: $this->staff
        );

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals('sold', $this->item->fresh()->status);
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_average_item_cost_and_profit_formula_calculation(): void
    {
        // Average cost = 18000 / 24 = 750
        $this->assertEquals(750.00, $this->batch->average_item_cost);

        $orderService = app(OrderService::class);
        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        // Profit = 4500 - 750 = 3750
        $this->assertEquals(3750.00, $order->profit);
    }

    public function test_delivery_status_completion_transitions_order_to_fulfilled(): void
    {
        $orderService = app(OrderService::class);
        $paymentService = app(PaymentService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $paymentService->recordPayment(
            order: $order,
            amount: 4500.00,
            method: 'cash',
            referenceNo: null,
            verifier: $this->staff
        );

        $delivery = Delivery::where('order_id', $order->id)->first();

        $response = $this->actingAs($this->staff)->put("/deliveries/{$delivery->id}", [
            'method' => 'jnt_delivery',
            'tracking_number' => 'JNT-998877',
            'status' => 'completed',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('fulfilled', $order->fresh()->status);
        $this->assertEquals('completed', $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->date_completed);
    }

    public function test_completed_delivery_cannot_be_updated_again(): void
    {
        $orderService = app(OrderService::class);
        $paymentService = app(PaymentService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $paymentService->recordPayment(
            order: $order,
            amount: 4500.00,
            method: 'cash',
            referenceNo: null,
            verifier: $this->staff
        );

        $delivery = Delivery::where('order_id', $order->id)->first();

        $this->actingAs($this->staff)->put("/deliveries/{$delivery->id}", [
            'method' => 'pickup',
            'tracking_number' => 'JNT-0001',
            'status' => 'completed',
        ])->assertSessionHas('success');

        $response = $this->actingAs($this->staff)->put("/deliveries/{$delivery->id}", [
            'method' => 'jnt_delivery',
            'tracking_number' => 'JNT-HACK',
            'status' => 'pending',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('completed', $delivery->fresh()->status);
        $this->assertEquals('JNT-0001', $delivery->fresh()->tracking_number);
    }

    public function test_jnt_delivery_requires_a_valid_tracking_number(): void
    {
        $order = app(OrderService::class)->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );
        app(PaymentService::class)->recordPayment($order, 4500.00, 'cash', null, $this->staff);
        $delivery = Delivery::where('order_id', $order->id)->first();

        // Missing tracking on a J&T delivery is rejected.
        $this->actingAs($this->staff)->put("/deliveries/{$delivery->id}", [
            'method' => 'jnt_delivery',
            'status' => 'shipped',
        ])->assertSessionHasErrors('tracking_number');

        // Too short / invalid characters are rejected.
        $this->actingAs($this->staff)->put("/deliveries/{$delivery->id}", [
            'method' => 'jnt_delivery',
            'tracking_number' => 'abc',
            'status' => 'shipped',
        ])->assertSessionHasErrors('tracking_number');

        // A valid waybill is accepted.
        $this->actingAs($this->staff)->put("/deliveries/{$delivery->id}", [
            'method' => 'jnt_delivery',
            'tracking_number' => 'JNT-PH-99887766',
            'status' => 'shipped',
        ])->assertSessionHas('success');

        $this->assertEquals('shipped', $delivery->fresh()->status);
    }

    public function test_deliveries_page_excludes_walkin_pos_orders(): void
    {
        $this->actingAs($this->staff)->post('/orders/pos-checkout', [
            'item_ids' => [$this->item->id],
            'payment_method' => 'cash',
            'cash_tendered' => 5000.00,
        ])->assertSessionHas('success');

        $posOrder = Order::where('order_type', 'walkin_pos')->latest('id')->firstOrFail();

        // POS is fulfilled at the counter and its delivery is auto-completed.
        $this->assertEquals('fulfilled', $posOrder->status);
        $this->assertEquals('completed', $posOrder->delivery->status);

        $this->actingAs($this->owner)->get('/deliveries')
            ->assertOk()
            ->assertDontSee($posOrder->order_number);
    }

    public function test_item_price_tier_is_auto_assigned_from_target_price(): void
    {
        $tierOne = Item::create([
            'batch_id' => $this->batch->id, 'sku' => 'T1', 'brand' => 'A', 'model' => 'A',
            'listed_price' => 800.00, 'condition' => 'Good', 'size' => 'US 9', 'status' => 'available',
        ]);
        $tierTwo = Item::create([
            'batch_id' => $this->batch->id, 'sku' => 'T2', 'brand' => 'B', 'model' => 'B',
            'listed_price' => 1500.00, 'condition' => 'Good', 'size' => 'US 9', 'status' => 'available',
        ]);
        $tierThree = Item::create([
            'batch_id' => $this->batch->id, 'sku' => 'T3', 'brand' => 'C', 'model' => 'C',
            'listed_price' => 2500.00, 'condition' => 'Good', 'size' => 'US 9', 'status' => 'available',
        ]);

        $this->assertEquals('Tier 1', $tierOne->price_tier);
        $this->assertEquals('Tier 2', $tierTwo->price_tier);
        $this->assertEquals('Tier 3', $tierThree->price_tier);

        // Price change re-derives the tier automatically.
        $tierOne->update(['listed_price' => 3000.00]);
        $this->assertEquals('Tier 3', $tierOne->fresh()->price_tier);
    }

    public function test_item_cannot_be_edited_once_reserved_or_sold(): void
    {
        $this->item->update(['status' => 'reserved']);

        $response = $this->actingAs($this->staff)->put("/items/{$this->item->id}", [
            'listed_price' => 9999.00,
            'condition' => 'Pristine',
            'size' => 'US 10.5',
            'status' => 'reserved',
            'repair_cost' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->assertNotEquals(9999.00, (float) $this->item->fresh()->listed_price);
    }

    public function test_item_can_be_created_without_a_category(): void
    {
        $response = $this->actingAs($this->staff)->post('/items', [
            'batch_id' => $this->batch->id,
            'brand' => 'Asics',
            'model' => 'Gel Kayano',
            'listed_price' => 232.00,
            'condition' => 'Good',
            'size' => 'US 9.0',
            'status' => 'available',
            'repair_cost' => 0,
            'category' => '',
        ]);

        $response->assertSessionHas('success');

        $item = Item::where('brand', 'Asics')->where('model', 'Gel Kayano')->firstOrFail();
        $this->assertNull($item->category);
    }

    public function test_triage_endpoint_can_no_longer_flip_item_status(): void
    {
        // Reservations must come from an order, never a bare triage status flip.
        $response = $this->actingAs($this->staff)->patch("/items/{$this->item->id}/triage", [
            'status' => 'reserved',
        ]);

        $response->assertNotFound();
        $this->assertEquals('available', $this->item->fresh()->status);
    }

    public function test_printable_receipt_is_available_for_an_order(): void
    {
        $orderService = app(OrderService::class);
        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $response = $this->actingAs($this->staff)->get("/orders/{$order->id}/receipt");

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    public function test_export_all_includes_every_section(): void
    {
        $response = $this->actingAs($this->owner)->get('/reports/export-all');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith('PK', $response->getContent());

        $xml = $this->xlsxXml($response);
        $this->assertStringContainsString('Total Sales', $xml);
        $this->assertStringContainsString('Total Expenses', $xml);
        $this->assertStringContainsString('Sales Today', $xml);
        $this->assertStringContainsString('Operating Expense Ledger', $xml);
        $this->assertStringContainsString('Batch Profitability', $xml);
        $this->assertStringContainsString('Sales Ledger', $xml);
    }

    public function test_custom_export_includes_only_selected_sections(): void
    {
        $response = $this->actingAs($this->owner)->get('/reports/export?preset=all&sections[]=summary');

        $response->assertStatus(200);
        $this->assertStringStartsWith('PK', $response->getContent());

        $xml = $this->xlsxXml($response);
        $this->assertStringContainsString('Total Sales', $xml);
        $this->assertStringNotContainsString('Operating Expense Ledger', $xml);
        $this->assertStringNotContainsString('Batch Profitability', $xml);
    }

    /**
     * Collect the XML parts from a generated .xlsx (a ZIP) for assertions.
     */
    private function xlsxXml($response): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($tmp, $response->getContent());

        $zip = new ZipArchive();
        $zip->open($tmp);

        $xml = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with((string) $name, 'xl/')) {
                $xml .= $zip->getFromIndex($i);
            }
        }

        $zip->close();
        @unlink($tmp);

        return $xml;
    }

    public function test_inventory_defaults_to_all_batches(): void
    {
        Item::create([
            'batch_id' => $this->batch->id, 'sku' => 'B04-002', 'brand' => 'Anta', 'model' => 'KT 8',
            'listed_price' => 3200.00, 'condition' => 'Good', 'size' => 'US 9.0', 'status' => 'available',
        ]);

        $response = $this->actingAs($this->staff)->get('/items');

        $response->assertStatus(200);
        $response->assertSee('B04-001');
        $response->assertSee('B04-002');
    }

    public function test_expense_delete_is_soft_and_can_be_restored(): void
    {
        $expense = Expense::create([
            'category' => 'Store Utilities',
            'description' => 'Power bill',
            'amount' => 1200.00,
            'date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($this->owner)->delete("/expenses/{$expense->id}")->assertSessionHas('undo');
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);

        $this->actingAs($this->owner)->post("/expenses/{$expense->id}/restore")->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'deleted_at' => null]);
    }

    public function test_supplier_delete_is_soft_and_can_be_restored(): void
    {
        $this->actingAs($this->owner)->delete("/suppliers/{$this->supplier->id}")->assertSessionHas('undo');
        $this->assertSoftDeleted('suppliers', ['id' => $this->supplier->id]);

        $this->actingAs($this->owner)->post("/suppliers/{$this->supplier->id}/restore")->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id, 'deleted_at' => null]);
    }

    public function test_audit_log_has_readable_description_and_details(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->owner->id,
            'action' => 'user_login',
            'details' => ['role' => 'owner'],
        ]);

        $this->assertEquals('Signed in', $log->description);
        $this->assertStringContainsString('signed in', $log->sentence);
        $this->assertEquals('Role', $log->detail_items[0]['label']);
        $this->assertEquals('owner', $log->detail_items[0]['value']);
    }

    public function test_failed_login_is_audited(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'nobody@test.com',
            'password' => 'wrong-password',
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'login_failed']);
    }

    public function test_items_can_be_sorted_by_price(): void
    {
        Item::create([
            'batch_id' => $this->batch->id, 'sku' => 'B04-050', 'brand' => 'Peak', 'model' => 'Cheap',
            'listed_price' => 900.00, 'condition' => 'Good', 'size' => 'US 9.0', 'status' => 'available',
        ]);

        $asc = $this->actingAs($this->staff)->get('/items?sort=listed_price&direction=asc');
        $asc->assertStatus(200);
        $asc->assertSeeInOrder(['B04-050', 'B04-001']);

        $desc = $this->actingAs($this->staff)->get('/items?sort=listed_price&direction=desc');
        $desc->assertStatus(200);
        $desc->assertSeeInOrder(['B04-001', 'B04-050']);
    }

    public function test_batch_can_be_updated(): void
    {
        $response = $this->actingAs($this->staff)->put("/batches/{$this->batch->id}", [
            'supplier_id' => $this->supplier->id,
            'batch_code' => 'B04X',
            'date_acquired' => now()->format('Y-m-d'),
            'total_sacks' => 2,
            'total_pairs' => 30,
            'total_cost' => 21000,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('batches', ['id' => $this->batch->id, 'batch_code' => 'B04X', 'total_pairs' => 30]);
    }

    public function test_batch_with_pairs_cannot_be_deleted(): void
    {
        $response = $this->actingAs($this->staff)->delete("/batches/{$this->batch->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('batches', ['id' => $this->batch->id]);
    }

    public function test_supplier_can_be_updated(): void
    {
        $response = $this->actingAs($this->owner)->put("/suppliers/{$this->supplier->id}", [
            'name' => 'Cebu Port Imports Ltd',
            'contact_number' => '09170000000',
            'notes' => 'Updated terms',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['id' => $this->supplier->id, 'name' => 'Cebu Port Imports Ltd']);
    }

    public function test_staff_account_can_be_updated(): void
    {
        $response = $this->actingAs($this->owner)->put("/staff/{$this->staff->id}", [
            'name' => 'Kent Updated',
            'email' => $this->staff->email,
            'role' => 'staff',
            'is_active' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $this->staff->id, 'name' => 'Kent Updated']);
    }

    public function test_released_item_can_be_resold_after_cancellation(): void
    {
        $orderService = app(OrderService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $orderService->cancelOrder($order, 'Test release', $this->staff);
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'available']);

        $response = $this->actingAs($this->staff)->post('/orders/pos-checkout', [
            'item_ids' => [$this->item->id],
            'payment_method' => 'cash',
            'cash_tendered' => 5000.00,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'sold']);
    }

    public function test_pos_requires_cash_received_and_gcash_reference(): void
    {
        $missingCash = $this->actingAs($this->staff)->post('/orders/pos-checkout', [
            'item_ids' => [$this->item->id],
            'payment_method' => 'cash',
        ]);
        $missingCash->assertSessionHasErrors('cash_tendered');

        $missingRef = $this->actingAs($this->staff)->post('/orders/pos-checkout', [
            'item_ids' => [$this->item->id],
            'payment_method' => 'gcash',
        ]);
        $missingRef->assertSessionHasErrors('gcash_ref');
    }

    public function test_owner_can_access_audit_log_but_staff_cannot(): void
    {
        $this->actingAs($this->owner)->get('/audit-log')->assertOk();
        $this->actingAs($this->staff)->get('/audit-log')->assertForbidden();
    }

    public function test_expired_reservation_is_auto_released_by_service(): void
    {
        $orderService = app(OrderService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00,
            reservationMinutes: 120
        );

        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'reserved']);

        $this->travel(121)->minutes();

        $released = $orderService->releaseExpiredReservations();

        $this->assertSame(1, $released);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'available']);
    }

    public function test_release_endpoint_releases_expired_reserved_order(): void
    {
        $orderService = app(OrderService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00,
            reservationMinutes: 120
        );

        $this->travel(121)->minutes();

        $response = $this->actingAs($this->staff)->postJson("/orders/{$order->id}/release");

        $response->assertOk()->assertJson(['released' => true]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'available']);
    }

    public function test_release_endpoint_refuses_order_that_is_not_reserved(): void
    {
        $orderService = app(OrderService::class);
        $paymentService = app(PaymentService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $paymentService->recordPayment($order, 4500.00, 'gcash', 'REF-123', $this->staff);

        $response = $this->actingAs($this->staff)->postJson("/orders/{$order->id}/release");

        $response->assertOk()->assertJson(['released' => false]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'sold']);
    }

    public function test_dashboard_load_sweeps_expired_reservations(): void
    {
        $orderService = app(OrderService::class);

        $order = $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00,
            reservationMinutes: 120
        );

        $this->travel(121)->minutes();

        $this->actingAs($this->staff)->get('/')->assertOk();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('items', ['id' => $this->item->id, 'status' => 'available']);
    }

    public function test_validation_errors_render_as_toasts_not_a_banner(): void
    {
        $page = $this->actingAs($this->owner)
            ->followingRedirects()
            ->from('/suppliers')
            ->post('/suppliers', []);

        $page->assertOk();
        $page->assertSee('The name field is required.', false);
        $page->assertDontSee('Please correct the following errors', false);
    }

    public function test_status_badges_use_the_unified_component(): void
    {
        $orderService = app(OrderService::class);
        $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00
        );

        $response = $this->actingAs($this->staff)->get('/orders');

        $response->assertOk();
        $response->assertSee('badge-pending', false); // reserved order status
        $response->assertSee('badge-live', false);    // live stream channel
    }

    public function test_staff_dashboard_renders_the_reservation_countdown(): void
    {
        $orderService = app(OrderService::class);
        $orderService->awardItem(
            item: $this->item,
            customer: $this->customer,
            staff: $this->staff,
            awardedPrice: 4500.00,
            reservationMinutes: 120
        );

        $response = $this->actingAs($this->staff)->get('/');

        $response->assertOk();
        $response->assertSee('reservationCountdown', false);
        $response->assertSee('releaseUrl', false);
        $response->assertSee('Expires in', false);
    }

    public function test_audit_category_uses_the_unified_badge_pill(): void
    {
        AuditService::log('user_login', null, null, $this->owner);

        $response = $this->actingAs($this->owner)->get('/audit-log');

        $response->assertOk();
        $response->assertSee('badge-security', false);
    }

    public function test_list_pages_render_header_status_counters(): void
    {
        $this->actingAs($this->staff)->get('/items')
            ->assertOk()
            ->assertSee('Available', false)
            ->assertSee('Reserved', false)
            ->assertSee('Sold', false);

        $this->actingAs($this->staff)->get('/orders')
            ->assertOk()
            ->assertSee('Paid', false)
            ->assertSee('Fulfilled', false);

        $this->actingAs($this->staff)->get('/deliveries')
            ->assertOk()
            ->assertSee('Pending', false)
            ->assertSee('Shipped', false);
    }
}
