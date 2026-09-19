<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function currentOrder(): HasOne
    {
        return $this->hasOne(Order::class)->whereIn('status', ['reserved', 'paid', 'fulfilled'])->latestOfMany();
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
