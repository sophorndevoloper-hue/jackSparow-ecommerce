<?php

namespace App\Models\Frontend;

use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\Product\Product;
use Database\Factories\FrontendUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class FrontendUser extends Authenticatable
{
    /** @use HasFactory<FrontendUserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected static function newFactory(): FrontendUserFactory
    {
        return FrontendUserFactory::new();
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'frontend_users';

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
        'is_blocked',
        'blocked_at',
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
            'blocked_at' => 'datetime',
            'is_blocked' => 'boolean',
            'password' => 'hashed',
            'orders_count' => 'integer',
            'total_spent' => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'credit_balance' => 'decimal:2',
        ];
    }

    /**
     * Determine if the customer account is currently blocked.
     */
    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    /**
     * Toggle the blocked status for this customer account.
     */
    public function toggleBlock(): bool
    {
        $this->is_blocked = ! $this->is_blocked;
        $this->blocked_at = $this->is_blocked ? now() : null;
        $this->save();

        return (bool) $this->is_blocked;
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
     * Hardware orders placed by this storefront user.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'frontend_user_id');
    }

    /**
     * Favorites saved by this customer.
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'frontend_user_id');
    }

    /**
     * Products favorited by this customer.
     *
     * @return BelongsToMany<Product, $this>
     */
    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites', 'frontend_user_id', 'product_id')->withTimestamps();
    }

    /**
     * Check whether this customer has Special Customer (VIP) status.
     */
    public function isSpecial(): bool
    {
        return $this->customer_type === 'special' || $this->orders_count >= 3 || $this->total_spent >= 1000.00;
    }

    /**
     * Automatically promote customer to special when order count or spending threshold is reached.
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
     * Calculate orders remaining until auto-promotion to Special VIP status.
     */
    public function ordersUntilSpecial(): int
    {
        if ($this->customer_type === 'special') {
            return 0;
        }

        return max(0, 3 - $this->orders_count);
    }
}
