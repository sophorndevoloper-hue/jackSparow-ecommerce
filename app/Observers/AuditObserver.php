<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    /**
     * Flag to disable auditing globally or within a closure.
     */
    protected static bool $disabled = false;

    /**
     * Temporarily disable auditing for a callback.
     */
    public static function withoutAuditing(callable $callback): mixed
    {
        static::$disabled = true;

        try {
            return $callback();
        } finally {
            static::$disabled = false;
        }
    }

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $module = class_basename($model);
        $entityId = $model->getKey();

        if (UserActivity::hasRecentlyLogged('CREATE', $module, $entityId)) {
            return;
        }

        $label = $this->getModelLabel($model);
        $attributes = collect($model->getAttributes())
            ->except(['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'])
            ->toArray();

        UserActivity::log(
            action: 'CREATE',
            module: $module,
            description: "Created {$module}: {$label}",
            entity: $model,
            properties: ! empty($attributes) ? ['attributes' => $attributes] : []
        );
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $module = class_basename($model);
        $entityId = $model->getKey();

        if (UserActivity::hasRecentlyLogged('UPDATE', $module, $entityId)) {
            return;
        }

        $changes = $model->getChanges();
        unset(
            $changes['updated_at'],
            $changes['remember_token'],
            $changes['password'],
            $changes['two_factor_secret'],
            $changes['two_factor_recovery_codes']
        );

        if (empty($changes)) {
            return;
        }

        $diff = [];
        foreach ($changes as $key => $newValue) {
            $diff[$key] = [
                'old' => $model->getOriginal($key),
                'new' => $newValue,
            ];
        }

        $label = $this->getModelLabel($model);
        $changedKeys = implode(', ', array_keys($diff));

        UserActivity::log(
            action: 'UPDATE',
            module: $module,
            description: "Updated {$module}: {$label} (Changed: {$changedKeys})",
            entity: $model,
            properties: ['changes' => $diff]
        );
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (! $this->shouldAudit($model)) {
            return;
        }

        $module = class_basename($model);
        $entityId = $model->getKey();

        if (UserActivity::hasRecentlyLogged('DELETE', $module, $entityId)) {
            return;
        }

        $label = $this->getModelLabel($model);

        UserActivity::log(
            action: 'DELETE',
            module: $module,
            description: "Deleted {$module}: {$label}",
            entity: $model
        );
    }

    /**
     * Determine if the model event should be audited.
     */
    protected function shouldAudit(Model $model): bool
    {
        if (static::$disabled) {
            return false;
        }

        if ($model instanceof UserActivity) {
            return false;
        }

        // Avoid logging automated console operations (migrations, seeders, queue workers) unless an authenticated user is present
        if (App::runningInConsole() && ! Auth::check() && ! Auth::guard('backend')->check() && ! Auth::guard('frontend')->check()) {
            return false;
        }

        return true;
    }

    /**
     * Format a readable descriptive label for the given model instance.
     */
    protected function getModelLabel(Model $model): string
    {
        if ($model instanceof ProductImage) {
            $productName = $model->product?->name ?? 'Product #'.$model->product_id;

            return "Image for {$productName}";
        }

        if (! empty($model->full_name)) {
            return (string) $model->full_name;
        }

        if (! empty($model->name) && ! empty($model->code)) {
            return "{$model->name} ({$model->code})";
        }

        if (! empty($model->name)) {
            return (string) $model->name;
        }

        if (! empty($model->title)) {
            return (string) $model->title;
        }

        if (! empty($model->reference_number)) {
            $extra = ! empty($model->type) ? " ({$model->type})" : (! empty($model->status) ? " ({$model->status})" : '');

            return (string) $model->reference_number.$extra;
        }

        if (! empty($model->order_number)) {
            return (string) $model->order_number;
        }

        if (! empty($model->quote_number)) {
            return (string) $model->quote_number;
        }

        if (! empty($model->serial_number)) {
            return (string) $model->serial_number;
        }

        if (! empty($model->staff_code)) {
            return (string) $model->staff_code;
        }

        if (! empty($model->code)) {
            return (string) $model->code;
        }

        if (! empty($model->email)) {
            return (string) $model->email;
        }

        return '#'.$model->getKey();
    }
}
