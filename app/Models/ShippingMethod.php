<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasActiveStatus, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'carrier',
        'base_cost',
        'min_order_amount',
        'estimated_days',
        'description',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Orders using this shipping method.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Deliveries fulfilled via this shipping method.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Scope query ordered by sort_order and name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
