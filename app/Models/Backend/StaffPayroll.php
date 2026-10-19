<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayroll extends Model
{
    use HasFactory;

    protected $table = 'staff_payrolls';

    protected $fillable = [
        'staff_id',
        'payroll_code',
        'period_start',
        'period_end',
        'base_salary',
        'worked_days',
        'overtime_amount',
        'bonus_amount',
        'deductions_amount',
        'net_salary',
        'payment_method',
        'payment_date',
        'status',
        'reference_note',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'string',
            'period_end' => 'string',
            'payment_date' => 'string',
            'base_salary' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'deductions_amount' => 'decimal:2',
            'net_salary' => 'decimal:2',
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
     * Calculate and set the net salary.
     */
    public function calculateNetSalary(): void
    {
        $base = (float) $this->base_salary;
        $overtime = (float) $this->overtime_amount;
        $bonus = (float) $this->bonus_amount;
        $deductions = (float) $this->deductions_amount;

        $this->net_salary = max(0, round($base + $overtime + $bonus - $deductions, 2));
    }

    /**
     * Status badge CSS styling.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'badge bg-secondary-subtle text-secondary border border-secondary-subtle',
            'approved' => 'badge bg-primary-subtle text-primary border border-primary-subtle',
            'paid' => 'badge bg-success-subtle text-success border border-success-subtle',
            default => 'badge bg-light text-dark',
        };
    }
}
