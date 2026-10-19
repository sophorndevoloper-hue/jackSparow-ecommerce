<?php

namespace App\Models\Frontend;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BestSeller extends Model
{
    use HasFactory;

    protected $table = 'best_sellers';

    protected $fillable = [
        'title',
        'description',
        'badge',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
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

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
