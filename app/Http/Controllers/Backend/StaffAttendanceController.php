<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    /**
     * Display the daily attendance sheet and logs.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : Carbon::today()->toDateString();

        $carbonDate = Carbon::parse($selectedDate);
        $prevDate = $carbonDate->copy()->subDay()->toDateString();
        $nextDate = $carbonDate->copy()->addDay()->toDateString();
        $isToday = $carbonDate->isToday();

        // Base query for active staff
        $staffQuery = Staff::query()->where('status', 'active');

        if ($request->filled('department')) {
            $staffQuery->where('department', $request->input('department'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $staffQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $allStaff = $staffQuery->orderBy('first_name')->get();

        // Load attendances for the selected date
        $attendances = StaffAttendance::where('date', $selectedDate)
            ->whereIn('staff_id', $allStaff->pluck('id'))
            ->get()
            ->keyBy('staff_id');

        // Filter by status if specified
        $statusFilter = $request->input('status');
        $roster = $allStaff->map(function (Staff $staff) use ($attendances) {
            $att = $attendances->get($staff->id);

            return [
                'staff' => $staff,
                'attendance' => $att,
                'status' => $att ? $att->status : 'not_marked',
                'check_in' => $att?->check_in,
                'check_out' => $att?->check_out,
                'work_hours' => $att?->work_hours ?? 0,
                'overtime_hours' => $att?->overtime_hours ?? 0,
                'notes' => $att?->notes,
            ];
        });

        if ($statusFilter) {
            $roster = $roster->filter(function ($item) use ($statusFilter) {
                if ($statusFilter === 'absent') {
                    return $item['status'] === 'absent' || $item['status'] === 'not_marked';
                }

                return $item['status'] === $statusFilter;
            });
        }

        // Daily KPI Metrics
        $totalEligible = $allStaff->count();
        $presentCount = $attendances->where('status', 'present')->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $halfDayCount = $attendances->where('status', 'half_day')->count();
        $onLeaveCount = $attendances->where('status', 'on_leave')->count();
        $checkedInNowCount = $attendances->filter(fn ($a) => $a->check_in && ! $a->check_out)->count();
        $totalAttended = $presentCount + $lateCount + $halfDayCount;
        $absentCount = max(0, $totalEligible - $totalAttended - $onLeaveCount);

        $completedAttendances = $attendances->filter(fn ($a) => (float) $a->work_hours > 0);
        $avgWorkHours = $completedAttendances->count() > 0
            ? round($completedAttendances->avg('work_hours'), 1)
            : 0.0;

        $departments = Staff::select('department')->distinct()->pluck('department')->filter()->values();

        return view('backend.staff-attendance.index', compact(
            'roster',
            'selectedDate',
            'prevDate',
            'nextDate',
            'isToday',
            'totalEligible',
            'presentCount',
            'lateCount',
            'halfDayCount',
            'absentCount',
            'onLeaveCount',
            'checkedInNowCount',
            'avgWorkHours',
            'departments',
            'allStaff'
        ));
    }

    /**
     * Fast check-in for a staff member.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $now = Carbon::now();

        $existing = StaffAttendance::where('staff_id', $validated['staff_id'])
            ->where('date', $date)
            ->first();

        if ($existing && $existing->check_in) {
            return back()->with('error', 'Staff member is already checked in for this date.');
        }

        // Detect if arrival is late (standard threshold 09:15 AM)
        $standardShiftStart = Carbon::parse($date.' 09:15:00');
        $status = $now->greaterThan($standardShiftStart) ? 'late' : 'present';

        $attendance = StaffAttendance::updateOrCreate(
            ['staff_id' => $validated['staff_id'], 'date' => $date],
            [
                'check_in' => $now,
                'status' => $status,
                'ip_address' => $request->ip(),
            ]
        );

        $staff = Staff::find($validated['staff_id']);

        UserActivity::log(
            'CHECK_IN',
            'StaffAttendance',
            "Recorded daily check-in for {$staff?->full_name} ({$staff?->staff_code}) on {$date} at {$now->format('h:i A')} (Status: {$status}).",
            $attendance,
            ['date' => $date, 'check_in' => $now->toDateTimeString(), 'status' => $status]
        );

        return back()->with('success', "Check-in recorded for {$staff?->full_name} at {$now->format('h:i A')}.");
    }

    /**
     * Fast check-out for a staff member.
     */
    public function checkOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['nullable', 'date'],
        ]);

        $date = $validated['date'] ?? Carbon::today()->toDateString();
        $now = Carbon::now();

        $attendance = StaffAttendance::where('staff_id', $validated['staff_id'])
            ->where('date', $date)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return back()->with('error', 'Staff member has not checked in yet for this date.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Staff member has already checked out for this date.');
        }

        $attendance->check_out = $now;
        $attendance->calculateWorkHours();

        // If worked less than 4 hours, categorize as half-day unless already marked on_leave
        if ($attendance->work_hours < 4.0 && $attendance->status !== 'on_leave') {
            $attendance->status = 'half_day';
        }

        $attendance->save();

        $staff = Staff::find($validated['staff_id']);

        UserActivity::log(
            'CHECK_OUT',
            'StaffAttendance',
            "Recorded departure check-out for {$staff?->full_name} ({$staff?->staff_code}). Total hours: {$attendance->work_hours}h (Overtime: {$attendance->overtime_hours}h).",
            $attendance,
            ['date' => $date, 'check_out' => $now->toDateTimeString(), 'work_hours' => $attendance->work_hours, 'overtime' => $attendance->overtime_hours]
        );

        return back()->with('success', "Check-out recorded for {$staff?->full_name}. Total hours: {$attendance->work_hours}h.");
    }

    /**
     * HR Manual entry or adjustment for attendance.
     */
    public function record(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:present,late,half_day,absent,on_leave'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $date = $validated['date'];
        $checkInDateTime = ! empty($validated['check_in']) ? Carbon::parse("{$date} {$validated['check_in']}") : null;
        $checkOutDateTime = ! empty($validated['check_out']) ? Carbon::parse("{$date} {$validated['check_out']}") : null;

        $attendance = StaffAttendance::firstOrNew([
            'staff_id' => $validated['staff_id'],
            'date' => $date,
        ]);

        $attendance->check_in = $checkInDateTime;
        $attendance->check_out = $checkOutDateTime;
        $attendance->status = $validated['status'];
        $attendance->notes = $validated['notes'] ?? null;

        if ($checkInDateTime && $checkOutDateTime) {
            $attendance->calculateWorkHours();
        } else {
            $attendance->work_hours = 0.00;
            $attendance->overtime_hours = 0.00;
        }

        $attendance->save();

        $staff = Staff::find($validated['staff_id']);

        UserActivity::log(
            'UPDATE',
            'StaffAttendance',
            "Manual attendance adjustment for {$staff?->full_name} ({$staff?->staff_code}) on {$date} set to {$validated['status']}.",
            $attendance,
            $validated
        );

        return back()->with('success', "Attendance updated successfully for {$staff?->full_name} on {$date}.");
    }

    /**
     * Delete an attendance record.
     */
    public function destroy(StaffAttendance $attendance): RedirectResponse
    {
        $staff = $attendance->staff;
        $date = $attendance->date;

        UserActivity::log(
            'DELETE',
            'StaffAttendance',
            "Deleted attendance record for {$staff?->full_name} ({$staff?->staff_code}) on {$date}.",
            $attendance
        );

        $attendance->delete();

        return back()->with('success', 'Attendance record deleted successfully.');
    }
}
