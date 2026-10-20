<?php

namespace App\Models;

use App\Models\Backend\CustomerGroupPaymentTerm;
use App\Models\Backend\Delivery;
use App\Models\Frontend\FrontendUser;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'order_number',
    'user_id',
    'customer_id',
    'frontend_user_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'status',
    'payment_status',
    'payment_method',
    'payment_due_date',
    'payment_term_id',
    'deposit_amount',
    'deposit_paid',
    'deposit_paid_at',
    'early_payment_discount_amount',
    'early_payment_discount_deadline',
    'late_fee_amount',
    'late_fee_applied_at',
    'shipping_method_id',
    'subtotal',
    'tax_amount',
    'shipping_fee',
    'discount_amount',
    'total_amount',
    'shipping_address',
    'billing_address',
    'customer_notes',
    'admin_notes',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_due_date' => 'date',
            'deposit_amount' => 'decimal:2',
            'deposit_paid' => 'boolean',
            'deposit_paid_at' => 'datetime',
            'early_payment_discount_amount' => 'decimal:2',
            'early_payment_discount_deadline' => 'date',
            'late_fee_amount' => 'decimal:2',
            'late_fee_applied_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'shipping_address' => 'array',
            'billing_address' => 'array',
        ];
    }

    /**
     * User who placed this order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Storefront frontend user who placed this order.
     *
     * @return BelongsTo<FrontendUser, $this>
     */
    public function frontendUser(): BelongsTo
    {
        return $this->belongsTo(FrontendUser::class, 'frontend_user_id');
    }

    /**
     * Storefront Customer who placed this order.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Line items for this order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Deliveries tracking for this order.
     *
     * @return HasMany<Delivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Latest delivery for this order.
     *
     * @return HasOne<Delivery, $this>
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class)->latestOfMany();
    }

    /**
     * Selected shipping method for this order.
     *
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Scope query by order status.
     *
     * @param  Builder<Order>  $query
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query by payment status.
     *
     * @param  Builder<Order>  $query
     */
    public function scopePaymentStatus(Builder $query, string $status): Builder
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Hardware serial numbers attached to this order.
     *
     * @return HasMany<SerialNumber, $this>
     */
    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class, 'order_id');
    }

    /**
     * Total count of serial-tracked items required for this order.
     */
    public function totalSerialTrackingRequiredCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            if ($item->requiresSerialTracking()) {
                $count += $item->quantity;
            }
        }

        return $count;
    }

    /**
     * Total count of serial numbers currently attached to this order.
     */
    public function totalSerialsAttachedCount(): int
    {
        return $this->serialNumbers()->count();
    }

    /**
     * Whether all required hardware serials are scanned and attached.
     */
    public function isReadyForShipment(): bool
    {
        $required = $this->totalSerialTrackingRequiredCount();
        if ($required === 0) {
            return true;
        }

        return $this->totalSerialsAttachedCount() >= $required;
    }

    /**
     * B2B customer group payment term assigned to this order.
     *
     * @return BelongsTo<CustomerGroupPaymentTerm, $this>
     */
    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(CustomerGroupPaymentTerm::class, 'payment_term_id');
    }

    /**
     * Whether this order is operating under Net credit settlement terms.
     */
    public function isNetTerms(): bool
    {
        return ! empty($this->payment_term_id);
    }

    /**
     * Determine if payment is past due date.
     */
    public function isOverdue(): bool
    {
        return $this->payment_status !== 'paid'
            && ! empty($this->payment_due_date)
            && $this->payment_due_date->isPast();
    }

    /**
     * Calculate remaining outstanding balance taking deposits and late fees into account.
     */
    public function getRemainingBalanceAttribute(): float
    {
        if ($this->payment_status === 'paid') {
            return 0.00;
        }

        $base = (float) $this->total_amount + (float) $this->late_fee_amount;
        if ($this->deposit_paid) {
            $base -= (float) $this->deposit_amount;
        }

        return max(0.00, round($base, 2));
    }
}
