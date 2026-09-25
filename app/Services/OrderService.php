<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    // I-award ang usa o daghan ka pares ngadto sa usa ka customer (one order, many pairs).
    // Naka-lock ang matag item para way mag-ilog sa live stream.
    public function awardItems(
        array $items,
        array $prices,
        Customer $customer,
        User $staff,
        string $orderType = 'live_stream',
        ?int $reservationMinutes = 120,
        ?string $notes = null
    ): Order {
        return DB::transaction(function () use ($items, $prices, $customer, $staff, $orderType, $reservationMinutes, $notes) {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
            $expiresAt = $reservationMinutes ? Carbon::now()->addMinutes($reservationMinutes) : null;

            $lines = [];
            $total = 0.0;

            foreach (array_values($items) as $index => $item) {
                // I-lock ang row para iwas double claim
                $lockedItem = Item::where('id', $item->id)->lockForUpdate()->firstOrFail();

                if ($lockedItem->status !== 'available') {
                    throw ValidationException::withMessages([
                        'item_ids' => ["Item {$lockedItem->sku} has already been {$lockedItem->status} and cannot be awarded."],
                    ]);
                }

                $price = (float) ($prices[$index] ?? $lockedItem->listed_price);
                $lines[] = ['item' => $lockedItem, 'price' => $price];
                $total += $price;
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $customer->id,
                'staff_id' => $staff->id,
                'awarded_price' => round($total, 2),
                'status' => 'reserved',
                'order_type' => $orderType,
                'date_awarded' => Carbon::now(),
                'expires_at' => $expiresAt,
                'notes' => $notes,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $line['item']->id,
                    'awarded_price' => round($line['price'], 2),
                ]);

                // Reserved na
                $line['item']->update(['status' => 'reserved']);
            }

            AuditService::log('order_awarded', $order, [
                'item_skus' => collect($lines)->pluck('item.sku')->implode(', '),
                'items_count' => count($lines),
                'customer_name' => $customer->name,
                'total_awarded_price' => round($total, 2),
                'order_type' => $orderType,
            ], $staff);

            return $order;
        });
    }

    // Backward-compatible single pair award
    public function awardItem(
        Item $item,
        Customer $customer,
        User $staff,
        float $awardedPrice,
        string $orderType = 'live_stream',
        ?int $reservationMinutes = 120,
        ?string $notes = null
    ): Order {
        return $this->awardItems(
            items: [$item],
            prices: [$awardedPrice],
            customer: $customer,
            staff: $staff,
            orderType: $orderType,
            reservationMinutes: $reservationMinutes,
            notes: $notes
        );
    }

    // Kansela ang order, balik baligya ang tanang sapatos
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

            $itemIds = OrderItem::where('order_id', $lockedOrder->id)->pluck('item_id');
            $items = Item::whereIn('id', $itemIds)->lockForUpdate()->get();

            foreach ($items as $item) {
                if ($item->status === 'reserved') {
                    $item->update(['status' => 'available']);
                }
            }

            AuditService::log('order_cancelled', $lockedOrder, [
                'reason' => $reason,
                'item_skus' => $items->pluck('sku')->implode(', '),
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
