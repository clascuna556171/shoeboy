<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'sku',
        'brand',
        'model',
        'price_tier',
        'listed_price',
        'condition',
        'size',
        'status',
        'triage_status',
        'repair_cost',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'listed_price' => 'decimal:2',
            'repair_cost' => 'decimal:2',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('awarded_price')
            ->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeReserved(Builder $query): Builder
    {
        return $query->where('status', 'reserved');
    }

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', 'sold');
    }
}
