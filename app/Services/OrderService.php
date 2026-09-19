<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    // I-award sa customer. Naka-lock para way mag-ilog sa live stream.
    public function awardItem(
        Item $item,
        Customer $customer,
        User $staff,
        float $awardedPrice,
        string $orderType = 'live_stream',
        ?int $reservationMinutes = 120,
        ?string $notes = null
    ): Order {
        return DB::transaction(function () use ($item, $customer, $staff, $awardedPrice, $orderType, $reservationMinutes, $notes) {
            // I-lock ang row para iwas double claim
            $lockedItem = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();

            if ($lockedItem->status !== 'available') {
                throw ValidationException::withMessages([
                    'item_id' => ["Item {$lockedItem->sku} has already been {$lockedItem->status} and cannot be awarded."],
                ]);
            }

            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $expiresAt = $reservationMinutes ? Carbon::now()->addMinutes($reservationMinutes) : null;

            $order = Order::create([
                'order_number' => $orderNumber,
                'item_id' => $lockedItem->id,
                'customer_id' => $customer->id,
                'staff_id' => $staff->id,
                'awarded_price' => $awardedPrice,
                'status' => 'reserved',
                'order_type' => $orderType,
                'date_awarded' => Carbon::now(),
                'expires_at' => $expiresAt,
                'notes' => $notes,
            ]);

            // Reserved na
            $lockedItem->update([
                'status' => 'reserved',
            ]);

            AuditService::log('order_awarded', $order, [
                'item_sku' => $lockedItem->sku,
                'customer_name' => $customer->name,
                'awarded_price' => $awardedPrice,
                'order_type' => $orderType,
            ], $staff);

            return $order;
        });
    }

    // Kansela ang order, balik baligya ang sapatos
    public function cancelOrder(Order $order, ?string $reason = null, ?User $user = null): void
    {
        DB::transaction(function () use ($order, $reason, $user) {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            if (in_array($lockedOrder->status, ['paid', 'fulfilled'])) {
                throw ValidationException::withMessages([
                    'order' => ["Order {$lockedOrder->order_number} is already paid or fulfilled and cannot be directly cancelled."],
                ]);
            }

            $lockedOrder->update([
                'status' => 'cancelled',
                'notes' => $lockedOrder->notes ? $lockedOrder->notes . ' | Cancellation: ' . $reason : 'Cancellation: ' . $reason,
            ]);

            $item = Item::where('id', $lockedOrder->item_id)->lockForUpdate()->first();
            if ($item && $item->status === 'reserved') {
                $item->update(['status' => 'available']);
            }

            AuditService::log('order_cancelled', $lockedOrder, [
                'reason' => $reason,
                'item_sku' => $item?->sku,
            ], $user);
        });
    }

    // Buhi-an ang mga expired (2 hours), balik sa stock
    public function releaseExpiredReservations(): int
    {
        $expiredOrders = Order::where('status', 'reserved')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            $this->cancelOrder($order, 'Reservation expired (auto-released back to stock)');
            $count++;
        }

        return $count;
    }
}
