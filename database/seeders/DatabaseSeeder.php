<?php

namespace Database\Seeders;

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
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users ---------------------------------------------------------
        $owner = User::create([
            'name' => 'Adrian Dael (Owner)',
            'email' => 'admin@theshoeboy.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'contact_number' => '09171234567',
            'is_active' => true,
        ]);

        $staff = User::create([
            'name' => 'Kent Staff',
            'email' => 'staff@theshoeboy.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'contact_number' => '09187654321',
            'is_active' => true,
        ]);

        // 2. Suppliers -----------------------------------------------------
        $supplier1 = Supplier::create([
            'name' => 'Cebu Port Import Bales',
            'contact_number' => '09221112233',
            'notes' => "Primary source for Men's Basketball Mix bales. Grade A inspection.",
        ]);

        $supplier2 = Supplier::create([
            'name' => 'Suntop Bales Warehouse',
            'contact_number' => '09334445566',
            'notes' => 'Specializes in Japanese runner and lifestyle bales.',
        ]);

        // 3. Batches -------------------------------------------------------
        $batch1 = Batch::create([
            'supplier_id' => $supplier1->id,
            'batch_code' => 'B04',
            'date_acquired' => Carbon::now()->subDays(6)->format('Y-m-d'),
            'total_sacks' => 1,
            'total_pairs' => 24,
            'total_cost' => 18000.00,
        ]);

        $batch2 = Batch::create([
            'supplier_id' => $supplier2->id,
            'batch_code' => 'B05',
            'date_acquired' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'total_sacks' => 1,
            'total_pairs' => 18,
            'total_cost' => 16200.00,
        ]);

        // 4. Customers -----------------------------------------------------
        $customer1 = Customer::create([
            'name' => 'Adrian Sole',
            'messenger_contact' => '@adrian_sole',
            'phone' => '09178889900',
            'shipping_address' => 'Matina, Davao City',
        ]);

        $customer2 = Customer::create([
            'name' => 'Ken Hoops',
            'messenger_contact' => '@ken_hoops23',
            'phone' => '09185556677',
            'shipping_address' => 'Buhangin, Davao City',
        ]);

        $customer3 = Customer::create([
            'name' => 'Davao Sneakerhead',
            'messenger_contact' => '@davao_sneakerhead',
            'phone' => '09203334455',
            'shipping_address' => 'Lanang, Davao City',
        ]);

        $walkinCustomer = Customer::create([
            'name' => 'Walk-In Store Customer',
            'messenger_contact' => '@walkin_customer',
            'phone' => null,
            'shipping_address' => 'In-Store Physical Shop Walk-in',
        ]);

        // 5. Items ---------------------------------------------------------
        // price_tier is derived automatically by the Item model from listed_price.
        $makeItems = function (Batch $batch, array $rows) {
            $created = [];
            foreach ($rows as $r) {
                $item = Item::create([
                    'batch_id' => $batch->id,
                    'sku' => $r['sku'],
                    'brand' => $r['brand'],
                    'model' => $r['model'],
                    'listed_price' => $r['price'],
                    'condition' => $r['cond'],
                    'size' => $r['size'],
                    'status' => $r['status'] ?? 'available',
                    'triage_status' => ($r['repair'] ?? 0) > 0 ? 'under_repair' : 'available',
                    'repair_cost' => $r['repair'] ?? 0,
                    'category' => $r['cat'] ?? null,
                ]);
                $created[$item->sku] = $item;
            }

            return $created;
        };

        $itemsB04 = $makeItems($batch1, [
            ['sku' => 'B04-001', 'brand' => 'Li-Ning', 'model' => 'Way of Wade 10 "South Beach"', 'price' => 4500, 'cond' => 'Pristine', 'size' => 'US 10.5', 'cat' => 'Basketball'],
            ['sku' => 'B04-002', 'brand' => 'Anta', 'model' => 'KT 8 "Splash Party"', 'price' => 3200, 'cond' => 'Good', 'size' => 'US 9.0', 'status' => 'reserved', 'cat' => 'Basketball'],
            ['sku' => 'B04-003', 'brand' => 'Peak', 'model' => 'Taichi Flash 4 "Underground"', 'price' => 2800, 'cond' => 'Good', 'size' => 'US 10.0', 'cat' => 'Basketball'],
            ['sku' => 'B04-004', 'brand' => 'Nike', 'model' => 'Dunk Low Retro "Panda"', 'price' => 4200, 'cond' => 'Pristine', 'size' => 'US 8.5', 'cat' => 'Lifestyle'],
            ['sku' => 'B04-005', 'brand' => 'Jordan', 'model' => 'Air Jordan 1 High "Lost & Found"', 'price' => 5800, 'cond' => 'Pristine', 'size' => 'US 11.0', 'status' => 'reserved', 'cat' => 'Basketball'],
            ['sku' => 'B04-006', 'brand' => 'Li-Ning', 'model' => 'Yushuai 16 V2 Low', 'price' => 2400, 'cond' => 'Fair', 'size' => 'US 9.5', 'repair' => 150, 'cat' => 'Basketball'],
            ['sku' => 'B04-007', 'brand' => 'Anta', 'model' => 'Shock The Game 6.0', 'price' => 2200, 'cond' => 'Fair', 'size' => 'US 10.5', 'cat' => 'Basketball'],
            ['sku' => 'B04-008', 'brand' => 'Nike', 'model' => 'Kobe 6 Protro "Grinch"', 'price' => 6500, 'cond' => 'Pristine', 'size' => 'US 10.0', 'status' => 'sold', 'cat' => 'Basketball'],
            ['sku' => 'B04-009', 'brand' => 'Asics', 'model' => 'Gel-Kayano 14 "Silver Cream"', 'price' => 3900, 'cond' => 'Pristine', 'size' => 'US 8.0', 'cat' => 'Running'],
            ['sku' => 'B04-010', 'brand' => 'Peak', 'model' => 'Attitude Basketball Mid', 'price' => 2600, 'cond' => 'Good', 'size' => 'US 11.5', 'cat' => 'Basketball'],
            ['sku' => 'B04-011', 'brand' => 'Jordan', 'model' => 'Air Jordan 4 Retro "Military Black"', 'price' => 5200, 'cond' => 'Good', 'size' => 'US 9.0', 'cat' => 'Basketball'],
            ['sku' => 'B04-012', 'brand' => 'Li-Ning', 'model' => 'Jimmy Butler JB1 "Tough"', 'price' => 3600, 'cond' => 'Pristine', 'size' => 'US 10.0', 'cat' => 'Basketball'],
            ['sku' => 'B04-013', 'brand' => 'Anta', 'model' => 'Gordon Hayward GH3 "Racing"', 'price' => 2900, 'cond' => 'Good', 'size' => 'US 9.5', 'cat' => 'Basketball'],
            ['sku' => 'B04-014', 'brand' => 'Nike', 'model' => 'Ja 1 "Day One"', 'price' => 3800, 'cond' => 'Pristine', 'size' => 'US 10.5', 'status' => 'sold', 'cat' => 'Basketball'],
            ['sku' => 'B04-015', 'brand' => 'New Balance', 'model' => '550 "White Green"', 'price' => 3100, 'cond' => 'Good', 'size' => 'US 8.5', 'cat' => 'Lifestyle'],
            ['sku' => 'B04-016', 'brand' => 'Peak', 'model' => 'Lou Williams Streetball Master', 'price' => 2100, 'cond' => 'Fair', 'size' => 'US 11.0', 'repair' => 120, 'cat' => 'Basketball'],
            ['sku' => 'B04-017', 'brand' => 'Asics', 'model' => 'Gel-Nimbus 25', 'price' => 4100, 'cond' => 'Pristine', 'size' => 'US 9.0', 'cat' => 'Running'],
            ['sku' => 'B04-018', 'brand' => 'Adidas', 'model' => 'Samba OG "White Black"', 'price' => 3500, 'cond' => 'Good', 'size' => 'US 8.0', 'cat' => 'Lifestyle'],
            ['sku' => 'B04-019', 'brand' => 'Peak', 'model' => 'Streetball Basic', 'price' => 850, 'cond' => 'Fair', 'size' => 'US 9.0', 'cat' => 'Basketball'],
            ['sku' => 'B04-020', 'brand' => 'Anta', 'model' => 'Runner Lite', 'price' => 1500, 'cond' => 'Good', 'size' => 'US 10.0', 'cat' => 'Running'],
        ]);

        $itemsB05 = $makeItems($batch2, [
            ['sku' => 'B05-001', 'brand' => 'New Balance', 'model' => '990v6 "Grey"', 'price' => 7200, 'cond' => 'Pristine', 'size' => 'US 9.5', 'cat' => 'Lifestyle'],
            ['sku' => 'B05-002', 'brand' => 'Asics', 'model' => 'Gel-Kayano 14 "Outdoor"', 'price' => 4300, 'cond' => 'Pristine', 'size' => 'US 10.0', 'cat' => 'Running'],
            ['sku' => 'B05-003', 'brand' => 'Nike', 'model' => 'Air Max 1 "Patta"', 'price' => 6100, 'cond' => 'Good', 'size' => 'US 10.5', 'status' => 'sold', 'cat' => 'Lifestyle'],
            ['sku' => 'B05-004', 'brand' => 'Adidas', 'model' => 'Gazelle Indoor', 'price' => 3300, 'cond' => 'Good', 'size' => 'US 8.5', 'cat' => 'Lifestyle'],
            ['sku' => 'B05-005', 'brand' => 'Asics', 'model' => 'Gel-1130 "Cream"', 'price' => 2900, 'cond' => 'Fair', 'size' => 'US 9.0', 'repair' => 90, 'cat' => 'Running'],
            ['sku' => 'B05-006', 'brand' => 'Saucony', 'model' => 'Shadow 6000', 'price' => 3600, 'cond' => 'Pristine', 'size' => 'US 11.0', 'status' => 'sold', 'cat' => 'Running'],
            ['sku' => 'B05-007', 'brand' => 'Mizuno', 'model' => 'Wave Rider 10', 'price' => 3200, 'cond' => 'Good', 'size' => 'US 9.5', 'cat' => 'Running'],
            ['sku' => 'B05-008', 'brand' => 'Nike', 'model' => 'Vomero 5 "Supersonic"', 'price' => 5600, 'cond' => 'Pristine', 'size' => 'US 10.0', 'cat' => 'Running'],
        ]);

        // 6. Orders, Payments & Deliveries ---------------------------------
        // A. Kobe 6 (live stream, GCash, J&T completed)
        $orderKobe = Order::create([
            'order_number' => 'ORD-20260316-KB008',
            'customer_id' => $customer1->id,
            'staff_id' => $staff->id,
            'awarded_price' => 6500.00,
            'status' => 'fulfilled',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subDays(3)->setTime(14, 22),
            'expires_at' => null,
            'notes' => 'Awarded on FB Live Stream Session 1',
        ]);
        $orderKobe->items()->attach($itemsB04['B04-008']->id, ['awarded_price' => 6500.00]);
        Payment::create([
            'order_id' => $orderKobe->id, 'amount' => 6500.00, 'method' => 'gcash',
            'reference_no' => 'GCASH-992182746', 'verified_by' => $staff->id,
            'date_paid' => Carbon::now()->subDays(3)->setTime(14, 45),
        ]);
        Delivery::create([
            'order_id' => $orderKobe->id, 'method' => 'jnt_delivery',
            'tracking_number' => 'JNT-PH-7788990011', 'status' => 'completed',
            'date_completed' => Carbon::now()->subDays(1),
        ]);

        // B. Ja 1 (walk-in POS, cash, pickup completed)
        $orderJa = Order::create([
            'order_number' => 'ORD-20260316-JA014',
            'customer_id' => $walkinCustomer->id,
            'staff_id' => $staff->id,
            'awarded_price' => 3800.00,
            'status' => 'fulfilled',
            'order_type' => 'walkin_pos',
            'date_awarded' => Carbon::now()->subDays(3)->setTime(16, 45),
            'expires_at' => null,
            'notes' => 'Walk-in cash sale at storefront counter',
        ]);
        $orderJa->items()->attach($itemsB04['B04-014']->id, ['awarded_price' => 3800.00]);
        Payment::create([
            'order_id' => $orderJa->id, 'amount' => 3800.00, 'method' => 'cash',
            'reference_no' => 'CASH-ORD-20260316-JA014', 'verified_by' => $staff->id,
            'date_paid' => Carbon::now()->subDays(3)->setTime(16, 46),
        ]);
        Delivery::create([
            'order_id' => $orderJa->id, 'method' => 'pickup',
            'tracking_number' => null, 'status' => 'completed',
            'date_completed' => Carbon::now()->subDays(3)->setTime(16, 48),
        ]);

        // C. Active reservation (pending GCash) — KT 8
        $orderKt = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-KT002',
            'customer_id' => $customer2->id,
            'staff_id' => $staff->id,
            'awarded_price' => 3200.00,
            'status' => 'reserved',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subMinutes(35),
            'expires_at' => Carbon::now()->addMinutes(85),
            'notes' => 'Claimed during current live stream session. Awaiting GCash transfer.',
        ]);
        $orderKt->items()->attach($itemsB04['B04-002']->id, ['awarded_price' => 3200.00]);

        // D. Active reservation (pending GCash) — Jordan 1
        $orderAj = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-AJ005',
            'customer_id' => $customer3->id,
            'staff_id' => $staff->id,
            'awarded_price' => 5800.00,
            'status' => 'reserved',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subMinutes(100),
            'expires_at' => Carbon::now()->addMinutes(20),
            'notes' => 'Claimed on FB Live stream. Timer expires in 20 minutes.',
        ]);
        $orderAj->items()->attach($itemsB04['B04-005']->id, ['awarded_price' => 5800.00]);

        // E. Air Max 1 (walk-in POS, cash, pickup completed) — B05
        $orderMax = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-MX003',
            'customer_id' => $walkinCustomer->id,
            'staff_id' => $staff->id,
            'awarded_price' => 6100.00,
            'status' => 'fulfilled',
            'order_type' => 'walkin_pos',
            'date_awarded' => Carbon::now()->subHours(5),
            'expires_at' => null,
            'notes' => 'POS Walk-In Sale',
        ]);
        $orderMax->items()->attach($itemsB05['B05-003']->id, ['awarded_price' => 6100.00]);
        Payment::create([
            'order_id' => $orderMax->id, 'amount' => 6100.00, 'method' => 'cash',
            'reference_no' => 'CASH-' . $orderMax->order_number, 'verified_by' => $staff->id,
            'date_paid' => Carbon::now()->subHours(5),
        ]);
        Delivery::create([
            'order_id' => $orderMax->id, 'method' => 'pickup',
            'tracking_number' => null, 'status' => 'completed',
            'date_completed' => Carbon::now()->subHours(5)->addMinutes(2),
        ]);

        // F. Saucony Shadow (live stream, GCash, J&T SHIPPED) — B05
        $orderSaucony = Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-SC006',
            'customer_id' => $customer2->id,
            'staff_id' => $staff->id,
            'awarded_price' => 3600.00,
            'status' => 'paid',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subDay(),
            'expires_at' => null,
            'notes' => 'Awarded on FB Live Stream Session 2',
        ]);
        $orderSaucony->items()->attach($itemsB05['B05-006']->id, ['awarded_price' => 3600.00]);
        Payment::create([
            'order_id' => $orderSaucony->id, 'amount' => 3600.00, 'method' => 'gcash',
            'reference_no' => 'GCASH-556677889', 'verified_by' => $staff->id,
            'date_paid' => Carbon::now()->subDay()->addMinutes(20),
        ]);
        Delivery::create([
            'order_id' => $orderSaucony->id, 'method' => 'jnt_delivery',
            'tracking_number' => 'JNT-PH-1122334455', 'status' => 'shipped',
        ]);

        // 7. Store Operating Expenses --------------------------------------
        Expense::create(['batch_id' => $batch1->id, 'category' => 'Sack Purchase', 'description' => 'Bale B04 (24 pairs basketball mix intake)', 'reference_no' => 'OR-2026-000101', 'amount' => 18000.00, 'date' => Carbon::now()->subDays(6)->format('Y-m-d')]);
        Expense::create(['batch_id' => $batch1->id, 'category' => 'Shipping & Freight', 'description' => 'Sea freight cargo delivery fee from Cebu to Davao', 'reference_no' => 'BILL-88231', 'amount' => 1850.00, 'date' => Carbon::now()->subDays(5)->format('Y-m-d')]);
        Expense::create(['batch_id' => $batch1->id, 'category' => 'Shoe Restoration', 'description' => 'Deep cleaner foam, brushes & sole contact cement', 'amount' => 920.00, 'date' => Carbon::now()->subDays(4)->format('Y-m-d')]);
        Expense::create(['batch_id' => $batch2->id, 'category' => 'Sack Purchase', 'description' => 'Bale B05 (18 pairs runner mix intake)', 'reference_no' => 'OR-2026-000118', 'amount' => 16200.00, 'date' => Carbon::now()->subDays(3)->format('Y-m-d')]);
        Expense::create(['batch_id' => null, 'category' => 'Packaging & Labels', 'description' => 'J&T parcel pouches & thermal label rolls', 'reference_no' => 'OR-2026-000124', 'amount' => 650.00, 'date' => Carbon::now()->subDays(3)->format('Y-m-d')]);
        Expense::create(['batch_id' => null, 'category' => 'Store Utilities', 'description' => 'Physical shop power & lighting allowance', 'amount' => 1200.00, 'date' => Carbon::now()->subDays(2)->format('Y-m-d')]);

        // 8. Audit Logs (varied categories so the audit page is populated) --
        AuditService::log('system_bootstrapped', null, ['system' => 'The Shoe Boy Order & Inventory Management System initialized'], $owner);
        AuditService::log('user_login', null, ['role' => 'owner'], $owner);
        AuditService::log('user_login', null, ['role' => 'staff'], $staff);
        AuditService::log('supplier_created', $supplier1, ['name' => $supplier1->name], $owner);
        AuditService::log('supplier_created', $supplier2, ['name' => $supplier2->name], $owner);
        AuditService::log('staff_account_created', $staff, ['name' => $staff->name, 'email' => $staff->email, 'role' => $staff->role], $owner);
        AuditService::log('batch_intake_created', $batch1, ['batch_code' => $batch1->batch_code, 'total_pairs' => $batch1->total_pairs, 'total_cost' => (float) $batch1->total_cost, 'avg_cost' => $batch1->average_item_cost], $staff);
        AuditService::log('batch_intake_created', $batch2, ['batch_code' => $batch2->batch_code, 'total_pairs' => $batch2->total_pairs, 'total_cost' => (float) $batch2->total_cost, 'avg_cost' => $batch2->average_item_cost], $staff);

        foreach (['B04-001', 'B05-001'] as $sku) {
            $item = $itemsB04[$sku] ?? $itemsB05[$sku];
            AuditService::log('item_added', $item, ['sku' => $item->sku, 'brand' => $item->brand, 'model' => $item->model, 'price' => (float) $item->listed_price], $staff);
        }

        foreach ([$orderKobe, $orderJa, $orderMax, $orderSaucony, $orderKt, $orderAj] as $order) {
            AuditService::log('order_awarded', $order, [
                'order_number' => $order->order_number,
                'items_count' => $order->items->count(),
                'item_skus' => $order->items->pluck('sku')->implode(', '),
                'customer_name' => $order->customer->name,
                'total_awarded_price' => (float) $order->awarded_price,
                'order_type' => $order->order_type,
            ], $staff);
        }

        foreach ([$orderKobe, $orderJa, $orderMax, $orderSaucony] as $order) {
            $payment = $order->payment;
            AuditService::log('payment_verified', $order, [
                'order_number' => $order->order_number,
                'method' => $payment->method,
                'amount' => (float) $payment->amount,
                'reference_no' => $payment->reference_no,
            ], $staff);
        }

        foreach ([$orderKobe, $orderJa, $orderMax, $orderSaucony] as $order) {
            AuditService::log('delivery_updated', $order, [
                'order_number' => $order->order_number,
                'method' => $order->delivery->method,
                'tracking' => $order->delivery->tracking_number,
                'status' => $order->delivery->status,
            ], $staff);
        }

        foreach (Expense::all() as $expense) {
            AuditService::log('expense_recorded', $expense, [
                'category' => $expense->category,
                'description' => $expense->description,
                'amount' => (float) $expense->amount,
            ], $owner);
        }
    }
}
