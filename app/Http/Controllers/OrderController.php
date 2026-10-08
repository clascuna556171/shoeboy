<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    use SortsQueries;

    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $search = $request->query('search');
        $focus = $request->query('focus');

        $query = Order::with(['items.batch', 'customer', 'staff', 'payment', 'delivery']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('order_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('items', fn ($iq) => $iq->where('sku', 'like', "%{$search}%"))
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('messenger_contact', 'like', "%{$search}%"));
            });
        }

        // Bring a specifically requested order to the top of the list for highlighting.
        if ($focus) {
            $query->reorder()
                ->orderByRaw('CASE WHEN order_number = ? THEN 0 ELSE 1 END', [$focus])
                ->orderByDesc('date_awarded');
        } else {
            $this->applySort($query, ['order_number', 'awarded_price', 'status', 'order_type', 'date_awarded', 'created_at'], 'date_awarded', 'desc');
        }

        $orders = $query->paginate(20)->withQueryString();

        // Status breakdown for the header counters (shop-wide, ignoring filters).
        $statusCounts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reservedCount = (int) ($statusCounts['reserved'] ?? 0);
        $paidCount = (int) ($statusCounts['paid'] ?? 0);
        $fulfilledCount = (int) ($statusCounts['fulfilled'] ?? 0);
        $cancelledCount = (int) ($statusCounts['cancelled'] ?? 0);

        return view('orders.index', compact('orders', 'status', 'type', 'focus', 'reservedCount', 'paidCount', 'fulfilledCount', 'cancelledCount'));
    }

    // I-award ang usa o daghan ka sapatos sa nakadaog / nipalit
    public function award(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => ['nullable', 'array'],
            'item_ids.*' => ['required', 'exists:items,id'],
            'item_id' => ['nullable', 'exists:items,id'],
            'prices' => ['nullable', 'array'],
            'prices.*' => ['nullable', 'numeric', 'min:0'],
            'awarded_price' => ['nullable', 'numeric', 'min:0'],
            'customer_name' => ['required', 'string', 'max:255'],
            'messenger_contact' => ['required', 'string', 'max:255'],
            'order_type' => ['nullable', 'in:live_stream,walkin_pos'],
            'reservation_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'notes' => ['nullable', 'string'],
        ]);

        // Normalize to a unique list of item ids (accepts legacy single item_id too)
        $itemIds = $validated['item_ids'] ?? [];
        if (! empty($validated['item_id'])) {
            $itemIds[] = $validated['item_id'];
        }
        $itemIds = array_values(array_unique($itemIds));

        if (empty($itemIds)) {
            throw ValidationException::withMessages([
                'item_ids' => ['Please select at least one pair to award.'],
            ]);
        }

        $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');
        $itemModels = array_map(fn ($id) => $items[$id], $itemIds);

        // Resolve per-pair prices
        $prices = [];
        foreach ($itemIds as $index => $id) {
            if (! empty($validated['prices'])) {
                $prices[] = (float) ($validated['prices'][$index] ?? $items[$id]->listed_price);
            } elseif (isset($validated['awarded_price']) && count($itemIds) === 1) {
                $prices[] = (float) $validated['awarded_price'];
            } else {
                $prices[] = (float) $items[$id]->listed_price;
            }
        }

        // Pangitaon or himuon bag-ong customer
        $customer = Customer::firstOrCreate(
            ['messenger_contact' => trim($validated['messenger_contact'])],
            ['name' => trim($validated['customer_name'])]
        );

        $order = $this->orderService->awardItems(
            items: $itemModels,
            prices: $prices,
            customer: $customer,
            staff: $request->user(),
            orderType: $validated['order_type'] ?? 'live_stream',
            reservationMinutes: $validated['reservation_minutes'] ?? 120,
            notes: $validated['notes'] ?? null
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Reserved {$order->items->count()} pair(s) for {$customer->name}.",
                'order' => $order->load(['items', 'customer', 'staff']),
            ]);
        }

        return redirect()->route('staff.workspace', ['tab' => 'claims'])
            ->with('success', "Reserved {$order->items->count()} pair(s) for {$customer->name}. Reservation active.");
    }

    // Walk-in POS checkout (usa ka order, daghang sapatos + discount)
    public function posCheckout(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'exists:items,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_note' => ['nullable', 'string'],
            'payment_method' => ['required', 'in:cash,gcash'],
            'cash_tendered' => ['nullable', 'numeric', 'min:0', 'required_if:payment_method,cash'],
            'gcash_ref' => ['nullable', 'string', 'max:100', 'required_if:payment_method,gcash'],
        ], [
            'cash_tendered.required_if' => 'Please enter the cash received from the customer.',
            'gcash_ref.required_if' => 'A GCash reference number is required for GCash payments.',
        ]);

        $staff = $request->user();
        $discount = (float) ($validated['discount'] ?? 0);
        $itemIds = array_values(array_unique($validated['item_ids']));
        $itemsCount = count($itemIds);

        // Default walk-in customer record
        $walkinCustomer = Customer::firstOrCreate(
            ['messenger_contact' => '@walkin_customer'],
            ['name' => 'Walk-In Store Customer']
        );

        $items = Item::whereIn('id', $itemIds)->get()->keyBy('id');
        $allocatedDiscountPerItem = $itemsCount > 0 ? ($discount / $itemsCount) : 0;

        $prices = [];
        $itemModels = [];
        foreach ($itemIds as $id) {
            $itemModels[] = $items[$id];
            $prices[] = round(max(0, (float) $items[$id]->listed_price - $allocatedDiscountPerItem), 2);
        }

        $discountNote = $validated['discount_note'] ?? null;
        $paymentMethod = $validated['payment_method'];
        $gcashRef = $validated['gcash_ref'] ?? null;

        if ($paymentMethod === 'cash') {
            $due = array_sum($prices);
            $tendered = (float) ($validated['cash_tendered'] ?? 0);
            if ($tendered + 0.001 < $due) {
                throw ValidationException::withMessages([
                    'cash_tendered' => 'Cash received (₱'.number_format($tendered, 2).') is less than the amount due (₱'.number_format($due, 2).').',
                ]);
            }
        }

        $order = DB::transaction(function () use ($itemModels, $prices, $walkinCustomer, $staff, $discountNote, $paymentMethod, $gcashRef) {
            $order = $this->orderService->awardItems(
                items: $itemModels,
                prices: $prices,
                customer: $walkinCustomer,
                staff: $staff,
                orderType: 'walkin_pos',
                reservationMinutes: null,
                notes: $discountNote ? "POS Sale. Discount note: {$discountNote}" : 'POS Walk-In Sale'
            );

            $total = (float) $order->awarded_price;

            $refNo = $paymentMethod === 'gcash'
                ? $gcashRef
                : 'CASH-'.$order->order_number;

            $this->paymentService->recordPayment(
                order: $order,
                amount: $total,
                method: $paymentMethod,
                referenceNo: $refNo,
                verifier: $staff,
                immediateFulfillment: true
            );

            return $order;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'POS sale completed successfully.',
                'order_number' => $order->order_number,
                'items_count' => $order->items->count(),
            ]);
        }

        return redirect()->route('staff.workspace', ['tab' => 'pos'])
            ->with('success', "POS sale completed ({$order->items->count()} pair(s)) and inventory updated.")
            ->with('receipt', ['label' => 'Print receipt', 'url' => route('orders.receipt', $order)]);
    }

    // Printable receipt for any order
    public function receipt(Order $order): View
    {
        $order->load(['items.batch', 'customer', 'staff', 'payment', 'delivery']);

        return view('orders.receipt', compact('order'));
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

    // Auto-release a reservation whose window has elapsed (called by the live countdown).
    public function release(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        if ($order->status !== 'reserved') {
            if ($request->wantsJson()) {
                return response()->json([
                    'released' => false,
                    'message' => "Order {$order->order_number} is no longer reserved.",
                ]);
            }

            return back()->with('info', "Order {$order->order_number} is no longer reserved.");
        }

        // Small tolerance for client clock skew: ask the client to retry until the server agrees.
        if ($order->expires_at && $order->expires_at->isFuture()) {
            if ($request->wantsJson()) {
                return response()->json(['released' => false, 'retry' => true]);
            }

            return back()->with('info', 'Reservation window has not elapsed yet.');
        }

        $skus = $order->items->pluck('sku')->implode(', ');

        $this->orderService->cancelOrder($order, 'Reservation window elapsed (auto-released)', $request->user());

        if ($request->wantsJson()) {
            return response()->json([
                'released' => true,
                'order_number' => $order->order_number,
                'skus' => $skus,
                'message' => "Reservation {$order->order_number} expired — {$skus} returned to stock.",
            ]);
        }

        return back()->with('info', "Reservation {$order->order_number} expired and returned to stock.");
    }
}
