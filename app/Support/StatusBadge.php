<?php

namespace App\Support;

class StatusBadge
{
    /**
     * kind => value => [css variant, label]
     */
    protected const MAP = [
        'order' => [
            'reserved'  => ['badge-pending', 'Reserved'],
            'paid'      => ['badge-paid', 'Paid'],
            'fulfilled' => ['badge-fulfilled', 'Fulfilled'],
            'cancelled' => ['badge-cancelled', 'Cancelled'],
        ],
        'item' => [
            'available' => ['badge-available', 'Available'],
            'reserved'  => ['badge-reserved', 'Reserved'],
            'repair'    => ['badge-repair', 'Repair'],
            'sold'      => ['badge-sold', 'Sold'],
            'washing'   => ['badge-washing', 'Washing'],
        ],
        'delivery' => [
            'pending'   => ['badge-pending', 'Pending'],
            'shipped'   => ['badge-shipped', 'Shipped'],
            'completed' => ['badge-fulfilled', 'Completed'],
        ],
        'payment' => [
            'gcash' => ['badge-gcash', 'GCash'],
            'cash'  => ['badge-cash', 'Cash'],
        ],
        'channel' => [
            'live_stream' => ['badge-live', 'Live Stream'],
            'walkin_pos'  => ['badge-pos', 'POS Walk-In'],
        ],
        'method' => [
            'jnt_delivery' => ['badge-jnt', 'J&T Express'],
            'pickup'       => ['badge-pickup', 'Store Pickup'],
        ],
        'role' => [
            'owner' => ['badge-owner', 'Owner'],
            'staff' => ['badge-staff', 'Staff'],
        ],
        'balance' => [
            'positive' => ['badge-fulfilled', 'Positive balance'],
            'negative' => ['badge-pending', 'Recovering costs'],
        ],
    ];

    public static function variant(string $kind, ?string $value): string
    {
        return self::MAP[$kind][$value][0] ?? 'badge-neutral';
    }

    public static function label(string $kind, ?string $value): string
    {
        return self::MAP[$kind][$value][1] ?? ($value !== null && $value !== '' ? ucfirst(str_replace('_', ' ', $value)) : '—');
    }
}
