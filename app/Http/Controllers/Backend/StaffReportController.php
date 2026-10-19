<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StaffReportController extends Controller
{
    /**
     * Display the monthly staff activity, attendance, and payroll summary report.
     * Includes print-friendly formatting with @media print and signatures.
     */
    public function monthlyReport(Request $request): View
    {
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $periodStart = Carbon::parse($selectedMonth.'-01')->startOfMonth()->toDateString();
        $periodEnd = Carbon::parse($selectedMonth.'-01')->endOfMonth()->toDateString();

        $allStaff = Staff::query()->orderBy('department')->orderBy('first_name')->get();

        // Get attendances for this month
        $attendances = StaffAttendance::whereBetween('date', [$periodStart, $periodEnd])->get();

        // Get payrolls for this month
        $payrolls = StaffPayroll::where('period_start', '>=', $periodStart)
            ->where('period_end', '<=', $periodEnd)
            ->get()
            ->keyBy('staff_id');

        // Compile comprehensive per-staff row
        $staffRows = $allStaff->map(function (Staff $staff) use ($attendances, $payrolls) {
            $staffAtts = $attendances->where('staff_id', $staff->id);
            $payroll = $payrolls->get($staff->id);

            $presentDays = $staffAtts->where('status', 'present')->count();
            $lateDays = $staffAtts->where('status', 'late')->count();
            $halfDays = $staffAtts->where('status', 'half_day')->count();
            $onLeaveDays = $staffAtts->where('status', 'on_leave')->count();
            $workedDays = $presentDays + $lateDays + $halfDays;
            $overtimeHours = (float) $staffAtts->sum('overtime_hours');
            $totalHours = (float) $staffAtts->sum('work_hours');

            $baseSalary = $payroll ? (float) $payroll->base_salary : (float) $staff->base_salary;
            $overtimeAmount = $payroll ? (float) $payroll->overtime_amount : 0.00;
            $bonusAmount = $payroll ? (float) $payroll->bonus_amount : 0.00;
            $deductionsAmount = $payroll ? (float) $payroll->deductions_amount : 0.00;
            $netSalary = $payroll ? (float) $payroll->net_salary : $baseSalary;
            $payrollStatus = $payroll ? $payroll->status : 'unprocessed';

            return [
                'staff' => $staff,
                'worked_days' => $workedDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'half_days' => $halfDays,
                'on_leave_days' => $onLeaveDays,
                'overtime_hours' => $overtimeHours,
                'total_hours' => $totalHours,
                'base_salary' => $baseSalary,
                'overtime_amount' => $overtimeAmount,
                'bonus_amount' => $bonusAmount,
                'deductions_amount' => $deductionsAmount,
                'net_salary' => $netSalary,
                'payroll_status' => $payrollStatus,
                'payroll' => $payroll,
            ];
        });

        // Summary Aggregates
        $totalStaffCount = $allStaff->count();
        $totalActiveCount = $allStaff->where('status', 'active')->count();
        $totalShiftsWorked = $staffRows->sum('worked_days');
        $totalOvertimeHours = $staffRows->sum('overtime_hours');
        $totalBaseSalary = $staffRows->sum('base_salary');
        $totalAdditions = $staffRows->sum('overtime_amount') + $staffRows->sum('bonus_amount');
        $totalDeductions = $staffRows->sum('deductions_amount');
        $totalNetPayroll = $staffRows->sum('net_salary');
        $totalDisbursed = $staffRows->filter(fn ($r) => $r['payroll_status'] === 'paid')->sum('net_salary');

        // Recent Staff Actions Logged in this Month
        $monthStartDt = Carbon::parse($periodStart)->startOfDay();
        $monthEndDt = Carbon::parse($periodEnd)->endOfDay();
        $staffActions = UserActivity::whereBetween('created_at', [$monthStartDt, $monthEndDt])
            ->whereIn('module', ['Staff', 'StaffAttendance', 'StaffPayroll'])
            ->latest('id')
            ->limit(20)
            ->get();

        return view('backend.staff.monthly-report', compact(
            'staffRows',
            'selectedMonth',
            'periodStart',
            'periodEnd',
            'totalStaffCount',
            'totalActiveCount',
            'totalShiftsWorked',
            'totalOvertimeHours',
            'totalBaseSalary',
            'totalAdditions',
            'totalDeductions',
            'totalNetPayroll',
            'totalDisbursed',
            'staffActions'
        ));
    }
}
