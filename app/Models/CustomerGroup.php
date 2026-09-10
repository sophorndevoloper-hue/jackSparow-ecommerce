<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'code',
        'description',
        'default_discount_percentage',
        'min_order_amount',
        'credit_limit',
        'payment_terms_days',
        'tax_exempt',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_discount_percentage' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'payment_terms_days' => 'integer',
            'tax_exempt' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Frontend users assigned to this customer group.
     *
     * @return HasMany<FrontendUser, $this>
     */
    public function frontendUsers(): HasMany
    {
        return $this->hasMany(FrontendUser::class);
    }

    /**
     * Legacy customers assigned to this customer group.
     *
     * @return HasMany<Customer, $this>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Custom product price tiers specifically defined for this customer group.
     *
     * @return HasMany<ProductPriceTier, $this>
     */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class);
    }
}
