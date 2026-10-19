<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffSalaryController extends Controller
{
    /**
     * Display payroll list and salary management dashboard.
     */
    public function index(Request $request): View
    {
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $periodStart = Carbon::parse($selectedMonth.'-01')->startOfMonth()->toDateString();
        $periodEnd = Carbon::parse($selectedMonth.'-01')->endOfMonth()->toDateString();

        $query = StaffPayroll::query()
            ->with('staff')
            ->where('period_start', '>=', $periodStart)
            ->where('period_end', '<=', $periodEnd);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->whereHas('staff', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->latest('id')->paginate(15)->withQueryString();

        // Financial KPIs for the selected month
        $monthPayrolls = StaffPayroll::where('period_start', '>=', $periodStart)
            ->where('period_end', '<=', $periodEnd)
            ->get();

        $totalPayrollExpense = (float) $monthPayrolls->sum('net_salary');
        $paidAmount = (float) $monthPayrolls->where('status', 'paid')->sum('net_salary');
        $pendingAmount = (float) $monthPayrolls->where('status', '!=', 'paid')->sum('net_salary');
        $paidCount = $monthPayrolls->where('status', 'paid')->count();
        $pendingCount = $monthPayrolls->where('status', '!=', 'paid')->count();

        // Check how many active staff lack payroll for this month
        $staffWithPayroll = $monthPayrolls->pluck('staff_id');
        $unprocessedStaffCount = Staff::where('status', 'active')
            ->whereNotIn('id', $staffWithPayroll)
            ->count();

        $staffList = Staff::where('status', 'active')->orderBy('first_name')->get();

        return view('backend.staff-salaries.index', compact(
            'payrolls',
            'selectedMonth',
            'periodStart',
            'periodEnd',
            'totalPayrollExpense',
            'paidAmount',
            'pendingAmount',
            'paidCount',
            'pendingCount',
            'unprocessedStaffCount',
            'staffList'
        ));
    }

    /**
     * Batch generate monthly payroll records for all active staff.
     */
    public function generatePayroll(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = $validated['month'];
        $periodStart = Carbon::parse($month.'-01')->startOfMonth()->toDateString();
        $periodEnd = Carbon::parse($month.'-01')->endOfMonth()->toDateString();

        $activeStaff = Staff::where('status', 'active')->get();
        $generatedCount = 0;

        foreach ($activeStaff as $staff) {
            $exists = StaffPayroll::where('staff_id', $staff->id)
                ->where('period_start', $periodStart)
                ->where('period_end', $periodEnd)
                ->exists();

            if ($exists) {
                continue;
            }

            // Calculate worked days and overtime hours from attendances in this period
            $attendances = StaffAttendance::where('staff_id', $staff->id)
                ->whereBetween('date', [$periodStart, $periodEnd])
                ->whereIn('status', ['present', 'late', 'half_day'])
                ->get();

            $workedDays = $attendances->count();
            $totalOvertimeHours = (float) $attendances->sum('overtime_hours');

            $hourlyRate = (float) ($staff->hourly_rate > 0 ? $staff->hourly_rate : ($staff->base_salary / 160));
            $overtimeAmount = round($totalOvertimeHours * $hourlyRate * 1.5, 2);

            $payrollCode = 'PAY-'.str_replace('-', '', $month).'-'.str_pad((string) $staff->id, 4, '0', STR_PAD_LEFT);

            $payroll = new StaffPayroll([
                'staff_id' => $staff->id,
                'payroll_code' => $payrollCode,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'base_salary' => $staff->base_salary,
                'worked_days' => $workedDays,
                'overtime_amount' => $overtimeAmount,
                'bonus_amount' => 0.00,
                'deductions_amount' => 0.00,
                'status' => 'draft',
                'payment_method' => $staff->bank_name ? 'bank_transfer' : 'cash',
            ]);

            $payroll->calculateNetSalary();
            $payroll->save();

            $generatedCount++;
        }

        UserActivity::log(
            'PAYROLL_GENERATED',
            'StaffPayroll',
            "Generated monthly payroll batch for {$month} ({$generatedCount} staff pay slips created).",
            null,
            ['month' => $month, 'generated_count' => $generatedCount]
        );

        return redirect()->route('admin.staff-salaries.index', ['month' => $month])
            ->with('success', "Successfully generated {$generatedCount} payroll records for {$month}.");
    }

    /**
     * Update payroll status (draft -> approved -> paid).
     */
    public function updatePayrollStatus(Request $request, StaffPayroll $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,approved,paid'],
            'bonus_amount' => ['nullable', 'numeric', 'min:0'],
            'deductions_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'in:bank_transfer,cash,check'],
        ]);

        if (isset($validated['bonus_amount'])) {
            $payroll->bonus_amount = $validated['bonus_amount'];
        }
        if (isset($validated['deductions_amount'])) {
            $payroll->deductions_amount = $validated['deductions_amount'];
        }

        $oldStatus = $payroll->status;
        $payroll->status = $validated['status'];

        if ($validated['status'] === 'paid') {
            $payroll->payment_date = $validated['payment_date'] ?? Carbon::today()->toDateString();
            if (! empty($validated['payment_method'])) {
                $payroll->payment_method = $validated['payment_method'];
            }
        }

        $payroll->calculateNetSalary();
        $payroll->save();

        UserActivity::log(
            'STATUS_CHANGE',
            'StaffPayroll',
            "Updated pay slip {$payroll->payroll_code} for {$payroll->staff->full_name} to {$payroll->status} (Net: \${$payroll->net_salary}).",
            $payroll,
            ['old_status' => $oldStatus, 'new_status' => $payroll->status, 'net_salary' => $payroll->net_salary]
        );

        return back()->with('success', "Payroll {$payroll->payroll_code} updated successfully.");
    }

    /**
     * Record payment for a payroll record.
     */
    public function recordPayment(Request $request, StaffPayroll $payroll): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:bank_transfer,cash,check'],
            'reference_note' => ['nullable', 'string', 'max:255'],
        ]);

        $payroll->status = 'paid';
        $payroll->payment_date = $validated['payment_date'];
        $payroll->payment_method = $validated['payment_method'];
        $payroll->reference_note = $validated['reference_note'] ?? null;
        $payroll->save();

        UserActivity::log(
            'DISBURSEMENT',
            'StaffPayroll',
            "Recorded salary disbursement of \${$payroll->net_salary} for {$payroll->staff->full_name} ({$payroll->payroll_code}) via {$payroll->payment_method}.",
            $payroll,
            ['amount' => $payroll->net_salary, 'method' => $payroll->payment_method, 'reference' => $payroll->reference_note]
        );

        return back()->with('success', "Payment recorded for {$payroll->staff->full_name} ({$payroll->payroll_code}).");
    }
}
