<?php

namespace App\Models\Backend;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerGroupPaymentTerm extends Model
{
    use HasFactory;

    protected $table = 'customer_group_payment_terms';

    protected $fillable = [
        'customer_group_id',
        'name',
        'code',
        'days',
        'description',
        'early_payment_discount_percentage',
        'early_payment_days',
        'late_fee_percentage',
        'require_deposit',
        'deposit_percentage',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'integer',
            'early_payment_discount_percentage' => 'decimal:2',
            'early_payment_days' => 'integer',
            'late_fee_percentage' => 'decimal:2',
            'require_deposit' => 'boolean',
            'deposit_percentage' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationship: The CustomerGroup this payment term belongs to (optional/nullable).
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Scope query to only active payment terms.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to global terms (no specific group assigned).
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('customer_group_id');
    }

    /**
     * Get a clean formatted label for UI display.
     */
    public function getFormattedTermAttribute(): string
    {
        if ($this->days === 0) {
            return 'Immediate / COD';
        }

        $label = "Net {$this->days} Days";

        if ($this->early_payment_discount_percentage > 0 && $this->early_payment_days > 0) {
            $discount = rtrim(rtrim((string) $this->early_payment_discount_percentage, '0'), '.');
            $label .= " ({$discount}% if paid within {$this->early_payment_days}d)";
        }

        return $label;
    }
}
