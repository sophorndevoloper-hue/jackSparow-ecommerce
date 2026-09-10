<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'address',
        'city',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Scope query to only active warehouses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Products stored in this warehouse with quantities.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Stock adjustments recorded at this warehouse.
     *
     * @return HasMany<StockAdjustment, $this>
     */
    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Outbound stock transfers originating from this warehouse.
     *
     * @return HasMany<StockTransfer, $this>
     */
    public function outboundTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Inbound stock transfers arriving at this warehouse.
     *
     * @return HasMany<StockTransfer, $this>
     */
    public function inboundTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    /**
     * Helper to get total units stored in this warehouse.
     */
    public function getTotalStockAttribute(): int
    {
        return (int) $this->products()->sum('product_warehouse.quantity');
    }

    /**
     * Helper to get stock of a specific product in this warehouse.
     */
    public function getProductStock(int|Product $product): int
    {
        $productId = $product instanceof Product ? $product->id : $product;
        $record = $this->products()->where('products.id', $productId)->first();

        return (int) ($record?->pivot?->quantity ?? 0);
    }

    /**
     * Set or increment stock of a product in this warehouse.
     */
    public function setProductStock(int|Product $product, int $quantity): void
    {
        $productId = $product instanceof Product ? $product->id : $product;

        $this->products()->syncWithoutDetaching([
            $productId => ['quantity' => max(0, $quantity)],
        ]);
    }
}
