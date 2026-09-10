<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    use HasFactory;

    public const STATUS_IN_STOCK = 'IN_STOCK';

    public const STATUS_ALLOCATED = 'ALLOCATED';

    public const STATUS_SHIPPED = 'SHIPPED';

    public const STATUS_RETURNED_RMA = 'RETURNED_RMA';

    public const STATUS_DEFECTIVE_SCRAP = 'DEFECTIVE_SCRAP';

    public const STATUS_RETURNED_TO_VENDOR = 'RETURNED_TO_VENDOR';

    public const STATUS_OTHER = 'OTHER';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'supplier_id',
        'order_id',
        'order_item_id',
        'serial_number',
        'status',
        'cost_price',
        'inbound_date',
        'outbound_date',
        'warranty_start_date',
        'warranty_end_date',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'inbound_date' => 'date',
            'outbound_date' => 'date',
            'warranty_start_date' => 'date',
            'warranty_end_date' => 'date',
        ];
    }

    /**
     * Product this serialized unit belongs to.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Warehouse where this unit is stored.
     *
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Supplier who delivered this unit.
     *
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Order this unit is allocated to or shipped with.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Order line item this unit belongs to.
     *
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Scope to available in-stock units.
     *
     * @param  Builder<SerialNumber>  $query
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_STOCK);
    }

    /**
     * Scope to active units under warranty.
     *
     * @param  Builder<SerialNumber>  $query
     */
    public function scopeUnderWarranty(Builder $query): Builder
    {
        return $query->where('warranty_end_date', '>=', now()->toDateString());
    }
}
