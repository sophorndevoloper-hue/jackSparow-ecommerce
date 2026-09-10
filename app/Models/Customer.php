<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customers';

    /**
     * The default guard for this model in Spatie permissions.
     */
    protected string $guard_name = 'frontend';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'customer_group_id',
        'name',
        'company_name',
        'tax_vat_number',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'customer_type',
        'orders_count',
        'total_spent',
        'credit_limit',
        'credit_balance',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'orders_count' => 'integer',
            'total_spent' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'credit_balance' => 'decimal:2',
        ];
    }

    /**
     * B2B Customer Group assignment.
     *
     * @return BelongsTo<CustomerGroup, $this>
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Orders placed by this customer.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Determine if customer has achieved Special Customer VIP status.
     */
    public function isSpecial(): bool
    {
        return $this->customer_type === 'special' || $this->orders_count >= 3 || $this->total_spent >= 1000.00;
    }

    /**
     * Check order metrics and automatically promote to special if threshold is reached.
     */
    public function checkAndPromote(): bool
    {
        if ($this->orders_count >= 3 || $this->total_spent >= 1000.00) {
            if ($this->customer_type !== 'special') {
                $this->customer_type = 'special';
                $this->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Return orders remaining until automatically qualifying as Special Customer.
     */
    public function ordersUntilSpecial(): int
    {
        if ($this->customer_type === 'special') {
            return 0;
        }

        return max(0, 3 - $this->orders_count);
    }
}
