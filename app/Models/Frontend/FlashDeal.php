<?php

namespace App\Models\Frontend;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class FlashDeal extends Model
{
    use HasFactory;

    protected $table = 'flash_deals';

    protected $fillable = [
        'title',
        'description',
        'badge',
        'starts_at',
        'ends_at',
        'discount_percentage',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'discount_percentage' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function storefrontProducts(): MorphMany
    {
        return $this->morphMany(StorefrontProduct::class, 'showcaseable');
    }

    public function products(): MorphToMany
    {
        return $this->morphToMany(Product::class, 'showcaseable', 'storefront_products')
            ->withPivot(['badge', 'custom_title', 'sort_order', 'is_active'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
