<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'messenger_contact',
        'phone',
        'shipping_address',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Preferred buyer label: the Facebook handle, falling back to the full name.
     */
    protected function displayHandle(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->messenger_contact ?: $this->name,
        );
    }
}
