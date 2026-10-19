<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'order_id',
    'product_id',
    'product_name',
    'product_sku',
    'unit_price',
    'quantity',
    'total_price',
    'specifications_snapshot',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
            'specifications_snapshot' => 'array',
        ];
    }

    /**
     * Order this item belongs to.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Product referenced by this line item.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Serial numbers allocated or attached to this order item.
     *
     * @return HasMany<SerialNumber, $this>
     */
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class, 'order_item_id');
    }

    /**
     * Check if product requires serial numbers.
     */
    public function requiresSerialTracking(): bool
    {
        return (bool) ($this->product?->requires_serial_tracking ?? false);
    }

    /**
     * Check if all required serials for this line item have been scanned.
     */
    public function isFullyScanned(): bool
    {
        if (! $this->requiresSerialTracking()) {
            return true;
        }

        return $this->serialNumbers()->count() >= $this->quantity;
    }
}
