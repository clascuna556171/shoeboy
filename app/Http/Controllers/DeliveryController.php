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

        $query = Delivery::with(['order.items', 'order.customer', 'order.payment', 'order.staff']);

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

        $this->applySort($query, ['status', 'method', 'tracking_number', 'date_completed', 'created_at'], 'created_at', 'desc');
        $deliveries = $query->paginate(20)->withQueryString();

        $pendingCount = Delivery::where('status', 'pending')->count();
        $shippedCount = Delivery::where('status', 'shipped')->count();
        $completedCount = Delivery::where('status', 'completed')->count();

        return view('deliveries.index', compact('deliveries', 'status', 'method', 'search', 'pendingCount', 'shippedCount', 'completedCount'));
    }

    public function update(Request $request, Delivery $delivery): RedirectResponse
    {
        if ($delivery->status === 'completed') {
            return back()->with('error', "Delivery for Order {$delivery->order->order_number} is already completed and locked. Status can no longer be changed.");
        }

        $validated = $request->validate([
            'method' => ['required', 'in:pickup,jnt_delivery'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:pending,shipped,completed'],
        ]);

        $data = [
            'method' => $validated['method'],
            'tracking_number' => $validated['tracking_number'] ?? null,
            'status' => $validated['status'],
        ];

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
