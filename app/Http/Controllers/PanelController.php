<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    /**
     * Return the HTML body for a lazily-loaded detail panel (order, receipt, audit).
     * Keeps the initial page free of per-row modal markup.
     */
    public function show(Request $request, string $type, int $id): JsonResponse
    {
        switch ($type) {
            case 'order':
                $order = Order::with(['items.batch', 'customer', 'staff', 'payment', 'delivery'])->findOrFail($id);

                return response()->json([
                    'type' => 'order',
                    'eyebrow' => 'Order Details',
                    'title' => $order->order_number,
                    'size' => 'lg',
                    'html' => view('components.order-modal-body', [
                        'order' => $order,
                        'delivery' => $order->delivery,
                    ])->render(),
                ]);

            case 'receipt':
                $order = Order::with(['items.batch', 'customer', 'staff', 'payment'])->findOrFail($id);

                return response()->json([
                    'type' => 'receipt',
                    'eyebrow' => 'Receipt',
                    'title' => $order->order_number,
                    'size' => 'md',
                    'html' => view('components.receipt-panel-body', ['order' => $order])->render(),
                ]);

            case 'audit':
                abort_unless($request->user()?->isOwner(), 403);

                $log = AuditLog::with('user')->findOrFail($id);

                return response()->json([
                    'type' => 'audit',
                    'eyebrow' => 'Audit Entry',
                    'title' => $log->description,
                    'size' => 'md',
                    'html' => view('components.audit-log-body', ['log' => $log])->render(),
                ]);

            default:
                abort(404);
        }
    }
}
