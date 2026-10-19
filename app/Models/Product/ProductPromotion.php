<?php

namespace App\Models\Product;

use App\Models\Frontend\StorefrontProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class ProductPromotion extends Model
{
    use HasFactory;

    protected $table = 'product_promotions';

    protected $fillable = [
        'title',
        'subtitle',
        'badge',
        'banner_image',
        'cta_text',
        'cta_url',
        'start_date',
        'end_date',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'banner_url',
    ];

    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner_image) {
            return null;
        }

        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        if (Storage::disk('public')->exists($this->banner_image)) {
            return Storage::disk('public')->url($this->banner_image);
        }

        return asset($this->banner_image);
    }

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
        $today = now()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->toDateString();
        if ($this->start_date && $this->start_date->toDateString() > $today) {
            return false;
        }
        if ($this->end_date && $this->end_date->toDateString() < $today) {
            return false;
        }

        return true;
    }
}
