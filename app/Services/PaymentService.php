<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Item;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    // Dawat bayad: i-paid ang order ug i-sold ang sapatos
    public function recordPayment(
        Order $order,
        float $amount,
        string $method,
        ?string $referenceNo,
        User $verifier
    ): Payment {
        return DB::transaction(function () use ($order, $amount, $method, $referenceNo, $verifier) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'reserved') {
                throw ValidationException::withMessages([
                    'order_id' => ["Order {$lockedOrder->order_number} is in status '{$lockedOrder->status}' and cannot receive payment."],
                ]);
            }

            if ($lockedOrder->payment()->exists()) {
                throw ValidationException::withMessages([
                    'order_id' => ["Order {$lockedOrder->order_number} already has a recorded payment."],
                ]);
            }

            if ($method === 'gcash') {
                if (empty($referenceNo) || trim($referenceNo) === '') {
                    throw ValidationException::withMessages([
                        'reference_no' => ['A GCash reference number is required for GCash payments.'],
                    ]);
                }

                $cleanRef = trim($referenceNo);
                $exists = Payment::where('method', 'gcash')
                    ->where('reference_no', $cleanRef)
                    ->exists();

                if ($exists) {
                    throw ValidationException::withMessages([
                        'reference_no' => ["GCash reference number '{$cleanRef}' has already been used on another order."],
                    ]);
                }
            }

            $payment = Payment::create([
                'order_id' => $lockedOrder->id,
                'amount' => $amount,
                'method' => $method,
                'reference_no' => $method === 'gcash' ? trim($referenceNo) : ($referenceNo ? trim($referenceNo) : 'CASH-' . $lockedOrder->order_number),
                'verified_by' => $verifier->id,
                'date_paid' => Carbon::now(),
            ]);

            // Paid na, tanggal reservation timer
            $lockedOrder->update([
                'status' => 'paid',
                'expires_at' => null,
            ]);

            // Sold na ang sapatos
            $item = Item::where('id', $lockedOrder->item_id)->lockForUpdate()->first();
            if ($item) {
                $item->update([
                    'status' => 'sold',
                ]);
            }

            // Andam daan delivery entry
            Delivery::firstOrCreate(
                ['order_id' => $lockedOrder->id],
                [
                    'method' => 'pickup',
                    'status' => 'pending',
                ]
            );

            AuditService::log('payment_verified', $payment, [
                'order_number' => $lockedOrder->order_number,
                'amount' => $amount,
                'method' => $method,
                'reference_no' => $payment->reference_no,
            ], $verifier);

            return $payment;
        });
    }
}
