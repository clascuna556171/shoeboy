<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $search = $request->query('search');

        $query = Order::with(['item.batch', 'customer', 'staff', 'payment', 'delivery'])
            ->latest('date_awarded');

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('order_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('item', fn ($iq) => $iq->where('sku', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('messenger_contact', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('orders.index', compact('orders', 'status', 'type'));
    }

    // I-award ang sapatos sa nakadaog / nipalit
    public function award(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'messenger_contact' => ['required', 'string', 'max:255'],
            'awarded_price' => ['required', 'numeric', 'min:0'],
            'order_type' => ['nullable', 'in:live_stream,walkin_pos'],
            'reservation_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = Item::findOrFail($validated['item_id']);

        // Pangitaon or himuon bag-ong customer
        $customer = Customer::firstOrCreate(
            ['messenger_contact' => trim($validated['messenger_contact'])],
            ['name' => trim($validated['customer_name'])]
        );

        $order = $this->orderService->awardItem(
            item: $item,
            customer: $customer,
            staff: $request->user(),
            awardedPrice: (float) $validated['awarded_price'],
            orderType: $validated['order_type'] ?? 'live_stream',
            reservationMinutes: $validated['reservation_minutes'] ?? 120,
            notes: $validated['notes'] ?? null
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Item {$item->sku} successfully reserved for {$customer->name}.",
                'order' => $order->load(['item', 'customer', 'staff']),
            ]);
        }

        return back()->with('success', "Item {$item->sku} awarded to {$customer->name}. 2-hour reservation active.");
    }

    // Walk-in POS checkout (daghang sapatos + discount)
    public function posCheckout(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'exists:items,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_note' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:cash,gcash'],
            'cash_tendered' => ['nullable', 'numeric'],
            'gcash_ref' => ['nullable', 'string'],
        ]);

        $staff = $request->user();
        $discount = (float) ($validated['discount'] ?? 0);
        $itemIds = $validated['item_ids'];
        $itemsCount = count($itemIds);

        // Default walk-in customer record
        $walkinCustomer = Customer::firstOrCreate(
            ['messenger_contact' => '@walkin_customer'],
            ['name' => 'Walk-In Store Customer']
        );

        $completedOrders = [];

        DB::transaction(function () use ($itemIds, $walkinCustomer, $staff, $discount, $itemsCount, $validated, &$completedOrders) {
            $allocatedDiscountPerItem = $itemsCount > 0 ? ($discount / $itemsCount) : 0;

            foreach ($itemIds as $itemId) {
                $item = Item::where('id', $itemId)->lockForUpdate()->firstOrFail();
                $awardedPrice = max(0, (float) $item->listed_price - $allocatedDiscountPerItem);

                $order = $this->orderService->awardItem(
                    item: $item,
                    customer: $walkinCustomer,
                    staff: $staff,
                    awardedPrice: $awardedPrice,
                    orderType: 'walkin_pos',
                    reservationMinutes: null,
                    notes: $validated['discount_note'] ? "POS Sale. Discount note: {$validated['discount_note']}" : "POS Walk-In Sale"
                );

                $refNo = $validated['payment_method'] === 'gcash'
                    ? $validated['gcash_ref']
                    : 'CASH-' . $order->order_number;

                $this->paymentService->recordPayment(
                    order: $order,
                    amount: $awardedPrice,
                    method: $validated['payment_method'],
                    referenceNo: $refNo,
                    verifier: $staff
                );

                $completedOrders[] = $order;
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'POS sale completed successfully.',
                'orders_count' => count($completedOrders),
            ]);
        }

        return back()->with('success', 'POS sale completed and inventory updated.');
    }

    // Kansela ang order
    public function cancel(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $reason = $request->input('reason', 'Cancelled by staff');
        $this->orderService->cancelOrder($order, $reason, $request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order {$order->order_number} cancelled and item returned to available stock.",
            ]);
        }

        return back()->with('info', "Order {$order->order_number} cancelled and pair returned to available stock.");
    }
}
