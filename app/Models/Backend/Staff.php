<?php

namespace App\Models\Backend;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'staff_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department',
        'role',
        'employment_type',
        'status',
        'join_date',
        'base_salary',
        'salary_type',
        'hourly_rate',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'base_salary' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
        ];
    }

    /**
     * Optional linked user account for system login.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Attendance history for this staff member.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'staff_id');
    }

    /**
     * Today's attendance record.
     */
    public function todayAttendance(): HasOne
    {
        return $this->hasOne(StaffAttendance::class, 'staff_id')
            ->where('date', Carbon::today()->toDateString());
    }

    /**
     * Payroll records.
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(StaffPayroll::class, 'staff_id');
    }

    /**
     * Full Name accessor.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Initials for avatar rendering.
     */
    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->first_name ?? '', 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);

        return strtoupper($first.$last) ?: 'ST';
    }

    /**
     * Check if staff has checked in today.
     */
    public function isCheckedInToday(): bool
    {
        $today = $this->todayAttendance;

        return $today && $today->check_in !== null;
    }

    /**
     * Check if staff has checked out today.
     */
    public function isCheckedOutToday(): bool
    {
        $today = $this->todayAttendance;

        return $today && $today->check_out !== null;
    }

    /**
     * Status badge CSS classes.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge bg-success-subtle text-success border border-success-subtle',
            'on_leave' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            'suspended' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'terminated' => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            default => 'badge bg-light text-dark',
        };
    }

    /**
     * Scope to active staff only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by department.
     */
    public function scopeByDepartment(Builder $query, string $department): Builder
    {
        return $query->where('department', $department);
    }
}
