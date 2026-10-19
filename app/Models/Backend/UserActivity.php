<?php

namespace App\Models\Backend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activities';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'module',
        'entity_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * User who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Cache of activity keys logged during the current lifecycle.
     *
     * @var array<string, bool>
     */
    protected static array $loggedKeys = [];

    /**
     * Map of logged activity records by action:module:entityId.
     *
     * @var array<string, self>
     */
    protected static array $requestActivities = [];

    public static function markAsLogged(string $action, string $module, string|int $entityId, ?self $activity = null): void
    {
        $key = strtoupper(trim($action)).':'.trim($module).':'.$entityId;
        static::$loggedKeys[$key] = true;
        if ($activity) {
            static::$requestActivities[$key] = $activity;
        }
    }

    public static function hasRecentlyLogged(string $action, string $module, string|int $entityId): bool
    {
        $key = strtoupper(trim($action)).':'.trim($module).':'.$entityId;

        return isset(static::$loggedKeys[$key]);
    }

    public static function getRecentlyLogged(string $action, string $module, string|int $entityId): ?self
    {
        $key = strtoupper(trim($action)).':'.trim($module).':'.$entityId;

        return static::$requestActivities[$key] ?? null;
    }

    public static function clearRecentlyLogged(): void
    {
        static::$loggedKeys = [];
        static::$requestActivities = [];
    }

    /**
     * Record an audit activity log entry.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        mixed $entity = null,
        array $properties = [],
        mixed $user = null
    ): self {
        $action = strtoupper(trim($action));
        $module = trim($module);

        $entityId = null;
        if ($entity instanceof Model) {
            $entityId = (string) $entity->getKey();
        } elseif (is_scalar($entity)) {
            $entityId = (string) $entity;
        }

        // If this exact entity was already audited during this request, allow custom controller logs to specialize or upgrade it
        if ($entityId) {
            $existing = static::getRecentlyLogged($action, $module, $entityId);
            if (! $existing && in_array($action, ['CHECK_IN', 'CHECK_OUT', 'STATUS_CHANGE', 'PAYROLL_GENERATED'])) {
                $existing = static::getRecentlyLogged('CREATE', $module, $entityId)
                    ?? static::getRecentlyLogged('UPDATE', $module, $entityId);
            }

            if ($existing && $existing->exists && $existing->fresh()) {
                $mergedProps = array_merge($existing->properties ?? [], $properties);
                $existing->update([
                    'action' => $action,
                    'description' => $description,
                    'properties' => ! empty($mergedProps) ? $mergedProps : null,
                ]);
                static::markAsLogged($action, $module, $entityId, $existing);

                return $existing;
            }
        }

        $actor = $user
            ?? Auth::guard('backend')->user()
            ?? Auth::guard('frontend')->user()
            ?? Auth::user();

        $userId = ($actor instanceof User) ? $actor->id : null;
        $userName = $actor?->name ?? ($actor ? 'User #'.$actor->getKey() : (Request::ip() ? 'System / Guest' : 'System'));
        $userEmail = $actor?->email ?? ($actor ? 'unknown' : 'system@internal');

        $activity = static::create([
            'user_id' => $userId,
            'user_name' => $userName,
            'user_email' => $userEmail,
            'action' => $action,
            'module' => $module,
            'entity_id' => $entityId,
            'description' => $description,
            'properties' => ! empty($properties) ? $properties : null,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent(),
        ]);

        if ($entityId) {
            static::markAsLogged($action, $module, $entityId, $activity);
        }

        return $activity;
    }

    /**
     * Action badge CSS styling.
     */
    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'CREATE' => 'badge bg-success-subtle text-success border border-success-subtle',
            'UPDATE' => 'badge bg-primary-subtle text-primary border border-primary-subtle',
            'DELETE' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'LOGIN', 'LOGOUT' => 'badge bg-info-subtle text-info border border-info-subtle',
            'CHECK_IN' => 'badge bg-emerald-subtle text-success border border-success-subtle',
            'CHECK_OUT' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            'STATUS_CHANGE' => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            'PAYROLL_GENERATED', 'DISBURSEMENT' => 'badge bg-dark-subtle text-dark border border-dark-subtle',
            default => 'badge bg-light text-dark border',
        };
    }

    /**
     * Action Icon class.
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'CREATE' => 'bi-plus-circle-fill text-success',
            'UPDATE' => 'bi-pencil-fill text-primary',
            'DELETE' => 'bi-trash-fill text-danger',
            'LOGIN' => 'bi-box-arrow-in-right text-info',
            'LOGOUT' => 'bi-box-arrow-right text-secondary',
            'CHECK_IN' => 'bi-calendar-check-fill text-success',
            'CHECK_OUT' => 'bi-calendar-x-fill text-warning',
            'STATUS_CHANGE' => 'bi-arrow-left-right text-secondary',
            'PAYROLL_GENERATED', 'DISBURSEMENT' => 'bi-cash-coin text-dark',
            default => 'bi-activity text-secondary',
        };
    }
}
