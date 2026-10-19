<?php

namespace App\Models\Backend;

use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'order_id',
        'shipping_method_id',
        'delivery_agent_id',
        'tracking_number',
        'carrier',
        'status',
        'recipient_name',
        'recipient_phone',
        'recipient_email',
        'shipping_address',
        'estimated_delivery_date',
        'dispatched_at',
        'delivered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'estimated_delivery_date' => 'date',
            'dispatched_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * The order linked to this delivery.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The shipping method used for this delivery.
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * The assigned delivery agent.
     */
    public function deliveryAgent(): BelongsTo
    {
        return $this->belongsTo(DeliveryAgent::class, 'delivery_agent_id');
    }

    /**
     * Scope query by delivery status.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Badge CSS class for the status.
     */
    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-secondary-subtle text-body border',
            'preparing' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            'dispatched', 'in_transit' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'out_for_delivery' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
            'delivered' => 'bg-success-subtle text-success border border-success-subtle',
            'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
            default => 'bg-light text-dark border',
        };
    }

    /**
     * Human-friendly label for status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Dispatch',
            'preparing' => 'Packing & Preparing',
            'dispatched' => 'Dispatched',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Status icon helper.
     */
    public function statusIcon(): string
    {
        return match ($this->status) {
            'pending' => 'bi-hourglass-split',
            'preparing' => 'bi-box-seam',
            'dispatched' => 'bi-send',
            'in_transit' => 'bi-truck',
            'out_for_delivery' => 'bi-pin-map',
            'delivered' => 'bi-check-circle-fill',
            'cancelled' => 'bi-x-circle',
            default => 'bi-circle',
        };
    }
}
