<?php

namespace App\Models\Product;

use App\Traits\GeneratesSlug;
use App\Traits\HasActiveStatus;
use App\Traits\Searchable;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'slug', 'logo', 'website', 'description', 'is_active'])]
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use GeneratesSlug, HasActiveStatus, HasFactory, Searchable;

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Products by this brand.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Models belonging to this brand.
     *
     * @return HasMany<ProductModel, $this>
     */
    public function models(): HasMany
    {
        return $this->hasMany(ProductModel::class, 'brand_id');
    }

    /**
     * Get the logo URL if uploaded.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        return null;
    }
}
