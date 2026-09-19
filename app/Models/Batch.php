<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'batch_code',
        'date_acquired',
        'total_sacks',
        'total_pairs',
        'total_cost',
    ];

    protected function casts(): array
    {
        return [
            'date_acquired' => 'date',
            'total_sacks' => 'integer',
            'total_pairs' => 'integer',
            'total_cost' => 'decimal:2',
        ];
    }

    // Gasto kada pares: Total Cost ÷ Total Pairs
    protected function averageItemCost(): Attribute
    {
        return Attribute::make(
            get: function () {
                $pairs = max(1, (int) $this->total_pairs);
                return round((float) $this->total_cost / $pairs, 2);
            }
        );
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
