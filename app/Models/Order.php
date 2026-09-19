<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'item_id',
        'customer_id',
        'staff_id',
        'awarded_price',
        'status',
        'order_type',
        'date_awarded',
        'expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'awarded_price' => 'decimal:2',
            'date_awarded' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // Ginansya: Awarded Price - Avg Cost - Repair Cost
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: function () {
                $baseCost = $this->item?->batch?->average_item_cost ?? 0;
                $repairCost = (float) ($this->item?->repair_cost ?? 0);
                return round((float) $this->awarded_price - $baseCost - $repairCost, 2);
            }
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }
}
