<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveStatus
{
    /**
     * Scope query to only active records.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->getTable().'.is_active', true);
    }

    /**
     * Scope query to only inactive records.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where($this->getTable().'.is_active', false);
    }

    /**
     * Toggle active state and persist to database.
     */
    public function toggleActive(): bool
    {
        $this->is_active = ! (bool) $this->is_active;

        return $this->save();
    }

    /**
     * Check if the model is currently active.
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
