<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\User;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

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

    public function test_staff_is_forbidden_from_reports_and_staff_management(): void
    {
        $responseReports = $this->actingAs($this->staff)->get('/reports');
        $responseReports->assertStatus(403);

        $responseStaff = $this->actingAs($this->staff)->get('/staff');
        $responseStaff->assertStatus(403);
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
        $this->assertEquals('paid', $order->status);

        // One order, one payment, one delivery for the whole ticket.
        $this->assertSame(1, Order::where('id', $order->id)->count());
        $this->assertSame(1, Payment::where('order_id', $order->id)->count());
        $this->assertSame(1, Delivery::where('order_id', $order->id)->count());
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
}
