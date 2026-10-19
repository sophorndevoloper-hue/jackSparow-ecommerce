<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'client_name',
        'company_name',
        'client_email',
        'client_phone',
        'company_tier',
        'status',
        'target_delivery_date',
        'client_notes',
        'admin_notes',
        'total_cost',
        'target_margin_percent',
        'subtotal',
        'tax_amount',
        'total_quoted_price',
        'quoted_at',
        'valid_until',
        'converted_order_id',
    ];

    protected function casts(): array
    {
        return [
            'total_cost' => 'decimal:2',
            'target_margin_percent' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_quoted_price' => 'decimal:2',
            'quoted_at' => 'datetime',
            'valid_until' => 'date',
            'target_delivery_date' => 'date',
        ];
    }

    /**
     * Bill of Materials (BOM) items in this quote.
     */
    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }

    /**
     * The order generated from this quote if converted.
     */
    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    /**
     * Scope to pending review quotes.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending_review');
    }

    /**
     * Scope to quoted quotes.
     */
    public function scopeQuoted(Builder $query): Builder
    {
        return $query->where('status', 'quoted');
    }

    /**
     * Status badge class.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending_review' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'quoted' => 'bg-info-subtle text-info border border-info-subtle',
            'accepted' => 'bg-success-subtle text-success border border-success-subtle',
            'rejected' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'converted_to_order' => 'bg-primary-subtle text-primary border border-primary-subtle',
            default => 'bg-light text-dark border',
        };
    }

    /**
     * Status readable label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_review' => 'Pending Review',
            'quoted' => 'Quote Sent',
            'accepted' => 'Accepted by Client',
            'rejected' => 'Declined',
            'converted_to_order' => 'Converted to Order',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Company tier badge class.
     */
    public function tierBadgeClass(): string
    {
        return match ($this->company_tier) {
            'Standard' => 'bg-secondary-subtle text-secondary border',
            'Gold' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'OEM' => 'bg-primary-subtle text-primary border border-primary-subtle',
            default => 'bg-light text-dark border',
        };
    }
}
