<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff members.
     */
    public function index(Request $request): View
    {
        $query = Staff::query()->with('user');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('staff_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $staffMembers = $query->latest('id')->paginate(15)->withQueryString();

        // KPI Summary Counters
        $totalStaff = Staff::count();
        $activeStaff = Staff::where('status', 'active')->count();
        $onLeaveStaff = Staff::where('status', 'on_leave')->count();
        $suspendedStaff = Staff::where('status', 'suspended')->count();
        $totalMonthlyPayroll = (float) Staff::where('status', 'active')->sum('base_salary');

        // Department breakdown
        $departments = Staff::select('department')->distinct()->pluck('department')->filter()->values();
        $users = User::where('is_approved', true)->get(['id', 'name', 'email']);

        return view('backend.staff.index', compact(
            'staffMembers',
            'totalStaff',
            'activeStaff',
            'onLeaveStaff',
            'suspendedStaff',
            'totalMonthlyPayroll',
            'departments',
            'users'
        ));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_code' => ['nullable', 'string', 'max:50', 'unique:staff,staff_code'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:staff,email'],
            'phone' => ['required', 'string', 'max:50'],
            'department' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:100'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,intern'],
            'status' => ['required', 'in:active,on_leave,suspended,terminated'],
            'join_date' => ['nullable', 'date'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'salary_type' => ['required', 'in:monthly,hourly,weekly'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        // Auto-generate staff code if not provided
        if (empty($validated['staff_code'])) {
            $nextId = (Staff::max('id') ?? 0) + 1;
            $validated['staff_code'] = 'STF-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
        }

        $created = Staff::create($validated);

        UserActivity::log(
            'CREATE',
            'Staff',
            "Registered new staff member {$created->full_name} ({$created->staff_code}) in {$created->department}.",
            $created,
            ['role' => $created->role, 'salary' => $created->base_salary]
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$validated['first_name']} {$validated['last_name']}' created successfully.");
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'staff_code' => ['required', 'string', 'max:50', Rule::unique('staff', 'staff_code')->ignore($staff->id)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('staff', 'email')->ignore($staff->id)],
            'phone' => ['required', 'string', 'max:50'],
            'department' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:100'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,intern'],
            'status' => ['required', 'in:active,on_leave,suspended,terminated'],
            'join_date' => ['nullable', 'date'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'salary_type' => ['required', 'in:monthly,hourly,weekly'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $staff->update($validated);

        UserActivity::log(
            'UPDATE',
            'Staff',
            "Updated profile and compensation details for {$staff->full_name} ({$staff->staff_code}).",
            $staff,
            ['role' => $staff->role, 'salary' => $staff->base_salary]
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff profile for '{$staff->full_name}' updated successfully.");
    }

    /**
     * Toggle status of a staff member.
     */
    public function toggleStatus(Request $request, Staff $staff): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:active,on_leave,suspended,terminated'],
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $staff->status;
        $staff->update(['status' => $newStatus]);

        UserActivity::log(
            'STATUS_CHANGE',
            'Staff',
            "Changed employment status for {$staff->full_name} ({$staff->staff_code}) from '{$oldStatus}' to '{$newStatus}'.",
            $staff,
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );

        return redirect()->route('admin.staff.index')
            ->with('success', "Status for '{$staff->full_name}' changed to '{$newStatus}'.");
    }

    /**
     * Remove the specified staff member from storage.
     */
    public function destroy(Staff $staff): RedirectResponse
    {
        $name = $staff->full_name;
        $code = $staff->staff_code;

        UserActivity::log(
            'DELETE',
            'Staff',
            "Deleted staff member {$name} ({$code}).",
            $staff
        );

        $staff->delete();

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff member '{$name}' deleted successfully.");
    }
}
