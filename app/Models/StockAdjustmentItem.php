<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'old_quantity',
        'adjusted_quantity',
        'new_quantity',
        'serial_numbers',
    ];

    protected function casts(): array
    {
        return [
            'old_quantity' => 'integer',
            'adjusted_quantity' => 'integer',
            'new_quantity' => 'integer',
            'serial_numbers' => 'array',
        ];
    }

    /**
     * Parent stock adjustment.
     *
     * @return BelongsTo<StockAdjustment, $this>
     */
    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    /**
     * Product adjusted.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
