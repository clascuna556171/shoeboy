<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Price tiers by target listed price (₱).
     * Tier 1: below ₱1,000 | Tier 2: ₱1,000–₱1,999.99 | Tier 3: ₱2,000 and up.
     */
    public const TIER_1 = 'Tier 1';

    public const TIER_2 = 'Tier 2';

    public const TIER_3 = 'Tier 3';

    /** Physical processing pipeline (separate from the sale status). */
    public const TRIAGE_WASHING = 'washing';

    public const TRIAGE_UNDER_REPAIR = 'under_repair';

    public const TRIAGE_READY = 'available';

    public const TRIAGE_STAGES = [
        self::TRIAGE_WASHING,
        self::TRIAGE_UNDER_REPAIR,
        self::TRIAGE_READY,
    ];

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

    protected static function booted(): void
    {
        static::saving(function (Item $item) {
            if ($item->listed_price !== null) {
                $item->price_tier = static::tierForPrice((float) $item->listed_price);
            }
        });
    }

    public static function tierForPrice(float $price): string
    {
        if ($price < 1000) {
            return self::TIER_1;
        }

        if ($price < 2000) {
            return self::TIER_2;
        }

        return self::TIER_3;
    }

    public function isEditable(): bool
    {
        return $this->status === 'available';
    }

    public function isTriageReady(): bool
    {
        return $this->triage_status === self::TRIAGE_READY;
    }

    public function triageLabel(): string
    {
        return match ($this->triage_status) {
            self::TRIAGE_WASHING => 'Washing',
            self::TRIAGE_UNDER_REPAIR => 'Under repair',
            self::TRIAGE_READY => 'Ready',
            default => ucfirst((string) $this->triage_status),
        };
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

    public function scopeTriageReady(Builder $query): Builder
    {
        return $query->where('triage_status', self::TRIAGE_READY);
    }
}
