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
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users (Owner & Staff)
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

        // 2. Suppliers
        $supplier1 = Supplier::create([
            'name' => 'Cebu Port Import Bales',
            'contact_number' => '09221112233',
            'notes' => 'Primary source for Men\'s Basketball Mix bales. Grade A inspection.',
        ]);

        $supplier2 = Supplier::create([
            'name' => 'Suntop Bales Warehouse',
            'contact_number' => '09334445566',
            'notes' => 'Specializes in Japanese runner and lifestyle bales.',
        ]);

        // 3. Batches
        $batch1 = Batch::create([
            'supplier_id' => $supplier1->id,
            'batch_code' => 'B04',
            'date_acquired' => Carbon::now()->subDays(6)->format('Y-m-d'),
            'total_sacks' => 1,
            'total_pairs' => 24,
            'total_cost' => 18000.00, // Avg cost: 750
        ]);

        $batch2 = Batch::create([
            'supplier_id' => $supplier2->id,
            'batch_code' => 'B05',
            'date_acquired' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'total_sacks' => 1,
            'total_pairs' => 24,
            'total_cost' => 21600.00, // Avg cost: 900
        ]);

        // 4. Customers
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

        // 5. Items for Batch B04 (24 pairs catalog)
        $shoesB04 = [
            ['sku' => 'B04-001', 'brand' => 'Li-Ning', 'model' => 'Way of Wade 10 "South Beach"', 'tier' => 'Tier 1 (High Value)', 'price' => 4500, 'cond' => 'Pristine', 'size' => 'US 10.5', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-002', 'brand' => 'Anta', 'model' => 'KT 8 "Splash Party"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 3200, 'cond' => 'Good', 'size' => 'US 9.0', 'status' => 'reserved', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-003', 'brand' => 'Peak', 'model' => 'Taichi Flash 4 "Underground"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 2800, 'cond' => 'Good', 'size' => 'US 10.0', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-004', 'brand' => 'Nike', 'model' => 'Dunk Low Retro "Panda"', 'tier' => 'Tier 1 (High Value)', 'price' => 4200, 'cond' => 'Pristine', 'size' => 'US 8.5', 'status' => 'available', 'repair' => 0, 'cat' => 'Lifestyle'],
            ['sku' => 'B04-005', 'brand' => 'Jordan', 'model' => 'Air Jordan 1 High "Lost & Found"', 'tier' => 'Tier 1 (High Value)', 'price' => 5800, 'cond' => 'Pristine', 'size' => 'US 11.0', 'status' => 'reserved', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-006', 'brand' => 'Li-Ning', 'model' => 'Yushuai 16 V2 Low', 'tier' => 'Tier 3 (Budget/Fair)', 'price' => 2400, 'cond' => 'Fair', 'size' => 'US 9.5', 'status' => 'available', 'repair' => 150, 'cat' => 'Basketball'],
            ['sku' => 'B04-007', 'brand' => 'Anta', 'model' => 'Shock The Game 6.0', 'tier' => 'Tier 3 (Budget/Fair)', 'price' => 2200, 'cond' => 'Fair', 'size' => 'US 10.5', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-008', 'brand' => 'Nike', 'model' => 'Kobe 6 Protro "Grinch"', 'tier' => 'Tier 1 (High Value)', 'price' => 6500, 'cond' => 'Pristine', 'size' => 'US 10.0', 'status' => 'sold', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-009', 'brand' => 'Asics', 'model' => 'Gel-Kayano 14 "Silver Cream"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 3900, 'cond' => 'Pristine', 'size' => 'US 8.0', 'status' => 'available', 'repair' => 0, 'cat' => 'Running'],
            ['sku' => 'B04-010', 'brand' => 'Peak', 'model' => 'Attitude Basketball Mid', 'tier' => 'Tier 3 (Budget/Fair)', 'price' => 2600, 'cond' => 'Good', 'size' => 'US 11.5', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-011', 'brand' => 'Jordan', 'model' => 'Air Jordan 4 Retro "Military Black"', 'tier' => 'Tier 1 (High Value)', 'price' => 5200, 'cond' => 'Good', 'size' => 'US 9.0', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-012', 'brand' => 'Li-Ning', 'model' => 'Jimmy Butler JB1 "Tough"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 3600, 'cond' => 'Pristine', 'size' => 'US 10.0', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-013', 'brand' => 'Anta', 'model' => 'Gordon Hayward GH3 "Racing"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 2900, 'cond' => 'Good', 'size' => 'US 9.5', 'status' => 'available', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-014', 'brand' => 'Nike', 'model' => 'Ja 1 "Day One"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 3800, 'cond' => 'Pristine', 'size' => 'US 10.5', 'status' => 'sold', 'repair' => 0, 'cat' => 'Basketball'],
            ['sku' => 'B04-015', 'brand' => 'New Balance', 'model' => '550 "White Green"', 'tier' => 'Tier 2 (Mid Range)', 'price' => 3100, 'cond' => 'Good', 'size' => 'US 8.5', 'status' => 'available', 'repair' => 0, 'cat' => 'Lifestyle'],
            ['sku' => 'B04-016', 'brand' => 'Peak', 'model' => 'Lou Williams Streetball Master', 'tier' => 'Tier 3 (Budget/Fair)', 'price' => 2100, 'cond' => 'Fair', 'size' => 'US 11.0', 'status' => 'available', 'repair' => 120, 'cat' => 'Basketball'],
        ];

        $createdItems = [];
        foreach ($shoesB04 as $s) {
            $createdItems[$s['sku']] = Item::create([
                'batch_id' => $batch1->id,
                'sku' => $s['sku'],
                'brand' => $s['brand'],
                'model' => $s['model'],
                'price_tier' => $s['tier'],
                'listed_price' => $s['price'],
                'condition' => $s['cond'],
                'size' => $s['size'],
                'status' => $s['status'],
                'triage_status' => $s['repair'] > 0 ? 'under_repair' : 'available',
                'repair_cost' => $s['repair'],
                'category' => $s['cat'],
            ]);
        }

        // 6. Orders, Payments & Deliveries
        // A. Kobe 6 Protro (Sold online via live stream to Adrian Sole, Paid GCash, Shipped via J&T)
        $orderKobe = Order::create([
            'order_number' => 'ORD-20260316-KB008',
            'item_id' => $createdItems['B04-008']->id,
            'customer_id' => $customer1->id,
            'staff_id' => $staff->id,
            'awarded_price' => 6500.00,
            'status' => 'fulfilled',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subDays(3)->setTime(14, 22),
            'expires_at' => null,
            'notes' => 'Awarded on FB Live Stream Session 1',
        ]);

        Payment::create([
            'order_id' => $orderKobe->id,
            'amount' => 6500.00,
            'method' => 'gcash',
            'reference_no' => 'GCASH-992182746',
            'verified_by' => $staff->id,
            'date_paid' => Carbon::now()->subDays(3)->setTime(14, 45),
        ]);

        Delivery::create([
            'order_id' => $orderKobe->id,
            'method' => 'jnt_delivery',
            'tracking_number' => 'JNT-PH-7788990011',
            'status' => 'completed',
            'date_completed' => Carbon::now()->subDays(1),
        ]);

        // B. Ja 1 Day One (Sold in-store walk-in POS, Paid Cash, Pickup completed)
        $orderJa = Order::create([
            'order_number' => 'ORD-20260316-JA014',
            'item_id' => $createdItems['B04-014']->id,
            'customer_id' => $walkinCustomer->id,
            'staff_id' => $staff->id,
            'awarded_price' => 3800.00,
            'status' => 'fulfilled',
            'order_type' => 'walkin_pos',
            'date_awarded' => Carbon::now()->subDays(3)->setTime(16, 45),
            'expires_at' => null,
            'notes' => 'Walk-in cash sale at storefront counter',
        ]);

        Payment::create([
            'order_id' => $orderJa->id,
            'amount' => 3800.00,
            'method' => 'cash',
            'reference_no' => 'CASH-ORD-20260316-JA014',
            'verified_by' => $staff->id,
            'date_paid' => Carbon::now()->subDays(3)->setTime(16, 46),
        ]);

        Delivery::create([
            'order_id' => $orderJa->id,
            'method' => 'pickup',
            'tracking_number' => null,
            'status' => 'completed',
            'date_completed' => Carbon::now()->subDays(3)->setTime(16, 48),
        ]);

        // C. KT 8 (Active Reservation for Ken Hoops, pending GCash payment)
        Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-KT002',
            'item_id' => $createdItems['B04-002']->id,
            'customer_id' => $customer2->id,
            'staff_id' => $staff->id,
            'awarded_price' => 3200.00,
            'status' => 'reserved',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subMinutes(35),
            'expires_at' => Carbon::now()->addMinutes(85),
            'notes' => 'Claimed during current live stream session. Awaiting GCash transfer.',
        ]);

        // D. Jordan 1 Lost & Found (Active Reservation for Davao Sneakerhead, pending GCash payment)
        Order::create([
            'order_number' => 'ORD-' . date('Ymd') . '-AJ005',
            'item_id' => $createdItems['B04-005']->id,
            'customer_id' => $customer3->id,
            'staff_id' => $staff->id,
            'awarded_price' => 5800.00,
            'status' => 'reserved',
            'order_type' => 'live_stream',
            'date_awarded' => Carbon::now()->subMinutes(100),
            'expires_at' => Carbon::now()->addMinutes(20),
            'notes' => 'Claimed on FB Live stream. Timer expires in 20 minutes.',
        ]);

        // 7. Store Operating Expenses
        Expense::create([
            'batch_id' => $batch1->id,
            'category' => 'Sack Purchase',
            'description' => 'Bale B04 (24 pairs basketball mix intake)',
            'amount' => 18000.00,
            'date' => Carbon::now()->subDays(6)->format('Y-m-d'),
        ]);

        Expense::create([
            'batch_id' => $batch1->id,
            'category' => 'Shipping & Freight',
            'description' => 'Sea freight cargo delivery fee from Cebu to Davao',
            'amount' => 1850.00,
            'date' => Carbon::now()->subDays(5)->format('Y-m-d'),
        ]);

        Expense::create([
            'batch_id' => $batch1->id,
            'category' => 'Shoe Restoration',
            'description' => 'Deep cleaner foam, brushes & sole contact cement',
            'amount' => 920.00,
            'date' => Carbon::now()->subDays(4)->format('Y-m-d'),
        ]);

        Expense::create([
            'batch_id' => null,
            'category' => 'Packaging & Labels',
            'description' => 'J&T parcel pouches & thermal label rolls',
            'amount' => 650.00,
            'date' => Carbon::now()->subDays(3)->format('Y-m-d'),
        ]);

        Expense::create([
            'batch_id' => null,
            'category' => 'Store Utilities',
            'description' => 'Physical shop power & lighting allowance',
            'amount' => 1200.00,
            'date' => Carbon::now()->subDays(2)->format('Y-m-d'),
        ]);

        // 8. Audit Logs
        AuditLog::create([
            'user_id' => $owner->id,
            'action' => 'system_bootstrapped',
            'auditable_type' => User::class,
            'auditable_id' => $owner->id,
            'details' => ['system' => 'The Shoe Boy Order & Inventory Management System initialized'],
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $staff->id,
            'action' => 'payment_verified',
            'auditable_type' => Payment::class,
            'auditable_id' => 1,
            'details' => ['order_number' => 'ORD-20260316-KB008', 'amount' => 6500, 'method' => 'gcash'],
            'ip_address' => '127.0.0.1',
        ]);
    }
}
