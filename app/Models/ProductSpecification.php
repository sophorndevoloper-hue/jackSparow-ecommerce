<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'specification_attribute_id',
        'value_string',
        'value_number',
        'value_boolean',
        'value_json',
        'formatted_value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value_number' => 'decimal:4',
            'value_boolean' => 'boolean',
            'value_json' => 'array',
        ];
    }

    /**
     * Product this specification belongs to.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The attribute definition for this specification.
     *
     * @return BelongsTo<SpecificationAttribute, $this>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SpecificationAttribute::class, 'specification_attribute_id');
    }

    /**
     * Helper to get typed display value.
     */
    public function getDisplayValue(): string
    {
        if ($this->formatted_value) {
            return $this->formatted_value;
        }

        if ($this->value_string !== null) {
            return $this->value_string;
        }

        if ($this->value_number !== null) {
            $unit = $this->attribute?->unit ? ' '.$this->attribute->unit : '';

            return (float) $this->value_number.$unit;
        }

        if ($this->value_boolean !== null) {
            return $this->value_boolean ? 'Yes' : 'No';
        }

        return '';
    }
}
