<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $method = $request->query('method');

        $query = Delivery::with(['order.items', 'order.customer', 'order.payment', 'order.staff'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($method) {
            $query->where('method', $method);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        $pendingCount = Delivery::where('status', 'pending')->count();
        $shippedCount = Delivery::where('status', 'shipped')->count();
        $completedCount = Delivery::where('status', 'completed')->count();

        return view('deliveries.index', compact('deliveries', 'status', 'method', 'pendingCount', 'shippedCount', 'completedCount'));
    }

    public function update(Request $request, Delivery $delivery): RedirectResponse
    {
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
