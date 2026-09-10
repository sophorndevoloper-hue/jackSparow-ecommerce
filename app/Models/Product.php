<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'category_id',
    'brand_id',
    'make_id',
    'name',
    'slug',
    'sku',
    'mpn',
    'upc_ean',
    'hs_code',
    'unspsc_code',
    'short_description',
    'description',
    'price',
    'sale_price',
    'cost_price',
    'map_price',
    'min_margin_percentage',
    'moq',
    'case_pack_multiple',
    'max_order_quantity',
    'stock_quantity',
    'low_stock_threshold',
    'requires_serial_tracking',
    'is_active',
    'is_featured',
    'specifications',
    'specs',
    'warranty_period',
    'manufacturer_warranty_months',
    'distributor_warranty_months',
    'form_factor',
    'socket',
    'chipset',
    'tdp_watts',
    'power_requirement_watts',
    'weight_kg',
    'length_cm',
    'width_cm',
    'height_cm',
    'is_hazmat',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'map_price' => 'decimal:2',
            'min_margin_percentage' => 'decimal:2',
            'moq' => 'integer',
            'case_pack_multiple' => 'integer',
            'max_order_quantity' => 'integer',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'requires_serial_tracking' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'specifications' => 'array',
            'specs' => 'array',
            'manufacturer_warranty_months' => 'integer',
            'distributor_warranty_months' => 'integer',
            'tdp_watts' => 'integer',
            'power_requirement_watts' => 'integer',
            'weight_kg' => 'decimal:3',
            'length_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'is_hazmat' => 'boolean',
        ];
    }

    /**
     * Category this product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Brand of this product.
     *
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Make of this product.
     *
     * @return BelongsTo<Make, $this>
     */
    public function make(): BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    /**
     * Images for this product.
     *
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * Primary image for this product.
     *
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Get accessible primary image URL.
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        $primary = $this->primaryImage ?: $this->images->first();

        return $primary ? $primary->image_url : null;
    }

    /**
     * Order items associated with this product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Warehouses storing this product with quantities.
     *
     * @return BelongsToMany<Warehouse, $this>
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * B2B Quantity & Customer Group Pricing Tiers.
     *
     * @return HasMany<ProductPriceTier, $this>
     */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_quantity');
    }

    /**
     * Normalized Product Specifications (EAV).
     *
     * @return HasMany<ProductSpecification, $this>
     */
    public function productSpecifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class);
    }

    /**
     * Serialized hardware units inventory.
     *
     * @return HasMany<SerialNumber, $this>
     */
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    /**
     * Hardware Compatibility definitions.
     *
     * @return HasMany<ProductCompatibility, $this>
     */
    public function compatibilities(): HasMany
    {
        return $this->hasMany(ProductCompatibility::class, 'product_id');
    }

    /**
     * Calculate absolute floor price based on cost_price and min_margin_percentage.
     */
    public function getFloorPrice(): float
    {
        if (! $this->cost_price || (float) $this->cost_price <= 0) {
            return 0.00;
        }

        $marginMultiplier = 1 + ((float) ($this->min_margin_percentage ?? 10.00) / 100);

        return round((float) $this->cost_price * $marginMultiplier, 2);
    }

    /**
     * Calculate effective B2B price for a given quantity and customer group.
     */
    public function calculateTierPrice(int $quantity = 1, ?CustomerGroup $group = null): float
    {
        // 1. Check for specific Customer Group tier first
        if ($group) {
            $groupTier = $this->priceTiers()
                ->where('customer_group_id', $group->id)
                ->where('min_quantity', '<=', $quantity)
                ->where(function ($q) use ($quantity) {
                    $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
                })
                ->orderByDesc('min_quantity')
                ->first();

            if ($groupTier) {
                return (float) $groupTier->unit_price;
            }
        }

        // 2. Check for general quantity break tier
        $generalTier = $this->priceTiers()
            ->whereNull('customer_group_id')
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderByDesc('min_quantity')
            ->first();

        if ($generalTier) {
            return (float) $generalTier->unit_price;
        }

        // 3. Fallback to Customer Group default discount % on base price
        $basePrice = (float) ($this->sale_price ?: $this->price);
        if ($group && (float) $group->default_discount_percentage > 0) {
            $discounted = $basePrice * (1 - ((float) $group->default_discount_percentage / 100));
            $floor = $this->getFloorPrice();

            return max(round($discounted, 2), $floor);
        }

        return $basePrice;
    }

    /**
     * Recalculate total product stock quantity across all warehouses.
     */
    public function syncTotalStock(): int
    {
        $sum = (int) $this->warehouses()->sum('product_warehouse.quantity');
        $this->update(['stock_quantity' => $sum]);

        return $sum;
    }

    /**
     * Effective current price (sale price if active, otherwise base price).
     */
    protected function effectivePrice(): Attribute
    {
        return Attribute::make(
            get: fn (): string => ($this->sale_price !== null && (float) $this->sale_price > 0 && (float) $this->sale_price < (float) $this->price)
                ? (string) $this->sale_price
                : (string) $this->price,
        );
    }

    /**
     * Stock status helper.
     */
    protected function isInStock(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->stock_quantity > 0,
        );
    }

    /**
     * Low stock status helper.
     */
    protected function isLowStock(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold,
        );
    }

    /**
     * Scope query to only active products.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only featured products.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope query to products with low stock.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope query to out-of-stock products.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '<=', 0);
    }
}
