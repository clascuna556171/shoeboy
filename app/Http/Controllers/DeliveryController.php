<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsQueries;
use App\Models\Delivery;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    use SortsQueries;

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $method = $request->query('method');
        $search = $request->query('search');

        $query = Delivery::with(['order.items', 'order.customer', 'order.payment', 'order.staff'])
            ->whereHas('order', fn ($oq) => $oq->where('order_type', '!=', 'walkin_pos'));

        if ($status) {
            $query->where('status', $status);
        }

        if ($method) {
            $query->where('method', $method);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($oq) => $oq
                        ->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($cq) => $cq
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('messenger_contact', 'like', "%{$search}%"))
                        ->orWhereHas('items', fn ($iq) => $iq->where('sku', 'like', "%{$search}%")));
            });
        }

        // Default view prioritises outstanding work: Pending → Shipped → Completed.
        if ($request->filled('sort')) {
            $this->applySort($query, ['status', 'method', 'tracking_number', 'date_completed', 'created_at'], 'created_at', 'desc');
        } else {
            $query->reorder()
                ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'shipped' THEN 1 WHEN 'completed' THEN 2 ELSE 3 END")
                ->orderByDesc('created_at');
        }

        $deliveries = $query->paginate(20)->withQueryString();

        $counts = Delivery::whereHas('order', fn ($oq) => $oq->where('order_type', '!=', 'walkin_pos'))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingCount = (int) ($counts['pending'] ?? 0);
        $shippedCount = (int) ($counts['shipped'] ?? 0);
        $completedCount = (int) ($counts['completed'] ?? 0);

        return view('deliveries.index', compact('deliveries', 'status', 'method', 'search', 'pendingCount', 'shippedCount', 'completedCount'));
    }

    public function update(Request $request, Delivery $delivery): RedirectResponse
    {
        if ($delivery->status === 'completed') {
            return back()->with('error', "Delivery for Order {$delivery->order->order_number} is already completed and locked. Status can no longer be changed.");
        }

        $rules = [
            'method' => ['required', 'in:pickup,jnt_delivery'],
            'tracking_number' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\-]{6,40}$/'],
            'status' => ['required', 'in:pending,shipped,completed'],
        ];

        // A J&T delivery may be saved without a waybill while it is still
        // pending, but a tracking number is required once it ships or completes.
        if ($request->input('method') === 'jnt_delivery' && $request->input('status') !== 'pending') {
            $rules['tracking_number'][] = 'required';
        }

        $validated = $request->validate($rules, [
            'tracking_number.required' => 'A tracking / waybill number is required for J&T once the delivery is shipped or completed.',
            'tracking_number.regex' => 'Tracking number must be 6–40 letters, numbers or dashes.',
        ]);

        $data = [
            'method' => $validated['method'],
            'tracking_number' => $validated['tracking_number'] ?? null,
            'status' => $validated['status'],
        ];

        if ($validated['status'] === 'completed' && ! in_array($delivery->order->status, ['paid', 'fulfilled'], true)) {
            return back()->with('error', "Order {$delivery->order->order_number} must be paid before the delivery can be completed.");
        }

        if ($validated['status'] === 'completed' && ! $delivery->date_completed) {
            $data['date_completed'] = Carbon::now();

            // Fulfilled na ang order
            $delivery->order->update(['status' => 'fulfilled']);
        }

        $delivery->update($data);

        AuditService::log('delivery_updated', $delivery, [
            'order_number' => $delivery->order->order_number,
            'status' => $delivery->status,
            'method' => $delivery->method,
            'tracking' => $delivery->tracking_number,
        ]);

        return back()->with('success', "Delivery status for Order {$delivery->order->order_number} updated to {$delivery->status}.");
    }
}
