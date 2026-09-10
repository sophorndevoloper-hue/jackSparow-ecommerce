<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCompatibility extends Model
{
    use HasFactory;

    public const TYPE_SOCKET_MATCH = 'socket_match';

    public const TYPE_CHIPSET_MATCH = 'chipset_match';

    public const TYPE_RAM_GENERATION = 'ram_generation';

    public const TYPE_PSU_WATTAGE = 'psu_wattage';

    public const TYPE_FORM_FACTOR = 'form_factor_clearance';

    public const TYPE_RECOMMENDED = 'recommended_accessory';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'compatible_product_id',
        'compatibility_type',
        'is_verified',
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
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Primary hardware product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Compatible counterpart hardware product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function compatibleProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'compatible_product_id');
    }
}
