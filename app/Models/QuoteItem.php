<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'product_id',
        'item_name',
        'item_sku',
        'description',
        'quantity',
        'target_unit_price',
        'unit_cost',
        'margin_percent',
        'quoted_unit_price',
        'total_quoted_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'target_unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'margin_percent' => 'decimal:2',
            'quoted_unit_price' => 'decimal:2',
            'total_quoted_price' => 'decimal:2',
        ];
    }

    /**
     * Parent quote.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    /**
     * Catalog product if linked.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
