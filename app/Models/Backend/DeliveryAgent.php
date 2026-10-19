<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone',
        'email',
        'vehicle_type',
        'license_plate',
        'zone',
        'status',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Deliveries handled by this agent.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'delivery_agent_id');
    }

    /**
     * Scope to active agents.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to available agents (active and available status).
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'available');
    }

    /**
     * Status badge class.
     */
    public function statusBadgeClass(): string
    {
        if (! $this->is_active) {
            return 'bg-secondary-subtle text-secondary border';
        }

        return match ($this->status) {
            'available' => 'bg-success-subtle text-success border border-success-subtle',
            'on_delivery' => 'bg-primary-subtle text-primary border border-primary-subtle',
            'inactive' => 'bg-secondary-subtle text-muted border',
            default => 'bg-light text-dark border',
        };
    }

    /**
     * Human-readable status label.
     */
    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'Deactivated';
        }

        return match ($this->status) {
            'available' => 'Available',
            'on_delivery' => 'On Delivery',
            'inactive' => 'Inactive',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Vehicle icon.
     */
    public function vehicleIcon(): string
    {
        return match ($this->vehicle_type) {
            'motorcycle' => 'bi-bicycle',
            'van' => 'bi-truck',
            'truck' => 'bi-truck-flatbed',
            'bicycle' => 'bi-bicycle',
            'car' => 'bi-car-front',
            default => 'bi-box-seam',
        };
    }

    /**
     * Vehicle human label.
     */
    public function vehicleLabel(): string
    {
        return match ($this->vehicle_type) {
            'motorcycle' => 'Motorcycle / Scooter',
            'van' => 'Delivery Van',
            'truck' => 'Heavy Truck',
            'bicycle' => 'Bicycle Courier',
            'car' => 'Sedan / Car',
            default => ucfirst($this->vehicle_type ?? 'Other'),
        };
    }
}
