<?php

namespace App\Models\Backend;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $table = 'staff_attendances';

    protected $fillable = [
        'staff_id',
        'date',
        'check_in',
        'check_out',
        'work_hours',
        'overtime_hours',
        'status',
        'ip_address',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'string',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'work_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }

    /**
     * Parent staff member.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Automatically recalculate total work hours based on check-in and check-out.
     */
    public function calculateWorkHours(float $standardShiftHours = 8.0): void
    {
        if ($this->check_in && $this->check_out) {
            $in = Carbon::parse($this->check_in);
            $out = Carbon::parse($this->check_out);

            if ($out->greaterThan($in)) {
                $totalMinutes = $in->diffInMinutes($out);
                $calculatedHours = round($totalMinutes / 60, 2);
                $this->work_hours = $calculatedHours;

                if ($calculatedHours > $standardShiftHours) {
                    $this->overtime_hours = round($calculatedHours - $standardShiftHours, 2);
                } else {
                    $this->overtime_hours = 0.00;
                }
            }
        }
    }

    /**
     * Status badge CSS styling.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'present' => 'badge bg-success-subtle text-success border border-success-subtle',
            'late' => 'badge bg-warning-subtle text-warning border border-warning-subtle',
            'half_day' => 'badge bg-info-subtle text-info border border-info-subtle',
            'absent' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'on_leave' => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            default => 'badge bg-light text-dark',
        };
    }
}
