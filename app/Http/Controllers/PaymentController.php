<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    public function verify(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'method' => ['required', 'in:gcash,cash'],
            'reference_no' => ['required_if:method,gcash', 'nullable', 'string', 'max:100'],
        ]);

        $order = Order::findOrFail($validated['order_id']);

        $payment = $this->paymentService->recordPayment(
            order: $order,
            amount: (float) $validated['amount'],
            method: $validated['method'],
            referenceNo: $validated['reference_no'] ?? null,
            verifier: $request->user()
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payment verified for Order {$order->order_number}.",
                'payment' => $payment,
            ]);
        }

        return back()->with('success', "Payment of ₱" . number_format($payment->amount, 2) . " verified for Order {$order->order_number}.");
    }
}
