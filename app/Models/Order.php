<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
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

    // Ginansya: sumada ang kada pares (Awarded Price - Avg Cost - Repair Cost)
    protected function profit(): Attribute
    {
        return Attribute::make(
            get: function () {
                $profit = $this->items->sum(function (Item $item) {
                    $baseCost = $item->batch?->average_item_cost ?? 0;
                    $repairCost = (float) $item->repair_cost;
                    $awardedPrice = (float) ($item->pivot->awarded_price ?? 0);

                    return $awardedPrice - $baseCost - $repairCost;
                });

                return round((float) $profit, 2);
            }
        );
    }

    // Convenience accessor: the first pair on the order (safety net for single-item reads)
    protected function item(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items->first()
        );
    }

    public function orderedItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'order_items')
            ->withPivot('awarded_price')
            ->withTimestamps();
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
