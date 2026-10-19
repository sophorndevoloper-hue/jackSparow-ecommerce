<?php

namespace App\Models;

use App\Traits\HasActiveStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasActiveStatus, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'instructions',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Orders placed using this payment method.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_method', 'code');
    }

    /**
     * Scope query ordered by sort_order and name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Bootstrap icon helper.
     */
    public function icon(): string
    {
        return match ($this->code) {
            'credit_card' => 'bi-credit-card-2-front',
            'bank_transfer' => 'bi-bank',
            'cash_on_delivery' => 'bi-cash-stack',
            'aba_pay' => 'bi-qr-code-scan',
            'paypal' => 'bi-paypal',
            default => 'bi-wallet2',
        };
    }
}
