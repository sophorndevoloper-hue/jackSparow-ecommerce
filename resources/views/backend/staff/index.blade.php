<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People &bull; Workforce &amp; Roles</p>
                    <h1 class="h3 mb-1">Staff Directory</h1>
                    <p class="text-muted mb-0">Manage internal staff members, designations, salary structures, and department assignments.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.staff.monthly-report') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i> Monthly Report
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStaffModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Register New Staff
                </button>
                <a href="{{ route('admin.staff-attendance.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-calendar-check me-1"></i> Daily Attendance
                </a>
                <a href="{{ route('admin.staff-salaries.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-cash-coin me-1"></i> Salaries &amp; Payroll
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Staff</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ $totalStaff }}</h3>
                            <small class="text-muted">Registered team members</small>
                        </div>
                        <div class="fs-1 text-primary opacity-25">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active Staff</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ $activeStaff }}</h3>
                            <small class="text-muted">Currently active &amp; on duty</small>
                        </div>
                        <div class="fs-1 text-success opacity-25">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">On Leave / Inactive</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning">{{ $onLeaveStaff + $suspendedStaff }}</h3>
                            <small class="text-muted">{{ $onLeaveStaff }} leave / {{ $suspendedStaff }} suspended</small>
                        </div>
                        <div class="fs-1 text-warning opacity-25">
                            <i class="bi bi-pause-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Monthly Payroll Base</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">${{ number_format($totalMonthlyPayroll, 2) }}</h3>
                            <small class="text-muted">Active staff salary liability</small>
                        </div>
                        <div class="fs-1 text-info opacity-25">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="panel mb-3 p-3">
            <form method="GET" action="{{ route('admin.staff.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, code, phone, role..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="department" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'department', 'status']))
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Staff Table -->
        <div class="panel overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted text-uppercase small">
                        <tr>
                            <th class="ps-3" style="width: 280px;">Staff Member</th>
                            <th>Code</th>
                            <th>Role &amp; Department</th>
                            <th>Employment</th>
                            <th>Base Salary</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffMembers as $staff)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ $staff->initials }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $staff->full_name }}</div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-telephone me-1"></i>{{ $staff->phone }}
                                                @if($staff->email) &bull; {{ $staff->email }} @endif
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">{{ $staff->staff_code }}</span>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $staff->role }}</div>
                                    <small class="text-muted"><i class="bi bi-building me-1"></i>{{ $staff->department }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary text-capitalize">
                                        {{ str_replace('_', ' ', $staff->employment_type) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark font-monospace">${{ number_format($staff->base_salary, 2) }}</div>
                                    <small class="text-muted text-capitalize">/ {{ $staff->salary_type }}</small>
                                </td>
                                <td>
                                    <span class="{{ $staff->status_badge_class }} text-capitalize">
                                        {{ str_replace('_', ' ', $staff->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $staff->id }}">
                                                    <i class="bi bi-pencil me-2 text-primary"></i> Edit Profile &amp; Salary
                                                </button>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.staff-attendance.check-in') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                    <button type="submit" class="dropdown-item text-success" {{ $staff->status !== 'active' ? 'disabled' : '' }}>
                                                        <i class="bi bi-box-arrow-in-right me-2"></i> Quick Check-In Today
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.staff-attendance.check-out') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                    <button type="submit" class="dropdown-item text-warning" {{ $staff->status !== 'active' ? 'disabled' : '' }}>
                                                        <i class="bi bi-box-arrow-right me-2"></i> Quick Check-Out Today
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li class="dropdown-header small text-uppercase">Change Status</li>
                                            @foreach(['active', 'on_leave', 'suspended', 'terminated'] as $stOption)
                                                @if($stOption !== $staff->status)
                                                    <li>
                                                        <form action="{{ route('admin.staff.toggle-status', $staff) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="{{ $stOption }}">
                                                            <button type="submit" class="dropdown-item text-capitalize">
                                                                Set to {{ str_replace('_', ' ', $stOption) }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endforeach
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete staff member {{ $staff->full_name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i> Delete Staff
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Staff Modal -->
                            <div class="modal fade" id="editStaffModal{{ $staff->id }}" tabindex="-1" aria-labelledby="editStaffModalLabel{{ $staff->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold" id="editStaffModalLabel{{ $staff->id }}">
                                                    <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Staff Member: {{ $staff->full_name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-12"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-person me-1"></i> Personal &amp; Identification</h6></div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Staff Employee Code *</label>
                                                        <input type="text" name="staff_code" class="form-control form-control-sm font-monospace" required value="{{ old('staff_code', $staff->staff_code) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">First Name *</label>
                                                        <input type="text" name="first_name" class="form-control form-control-sm" required value="{{ old('first_name', $staff->first_name) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Last Name *</label>
                                                        <input type="text" name="last_name" class="form-control form-control-sm" required value="{{ old('last_name', $staff->last_name) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Phone Number *</label>
                                                        <input type="text" name="phone" class="form-control form-control-sm" required value="{{ old('phone', $staff->phone) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Email Address</label>
                                                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $staff->email) }}">
                                                    </div>

                                                    <div class="col-12 mt-3"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-briefcase me-1"></i> Role, Department &amp; Status</h6></div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Department *</label>
                                                        <input type="text" name="department" class="form-control form-control-sm" required value="{{ old('department', $staff->department) }}" list="deptList">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Designation / Role *</label>
                                                        <input type="text" name="role" class="form-control form-control-sm" required value="{{ old('role', $staff->role) }}" placeholder="e.g. Warehouse Lead, Technician">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Employment Type *</label>
                                                        <select name="employment_type" class="form-select form-select-sm" required>
                                                            <option value="full_time" {{ old('employment_type', $staff->employment_type) === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                                            <option value="part_time" {{ old('employment_type', $staff->employment_type) === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                                            <option value="contract" {{ old('employment_type', $staff->employment_type) === 'contract' ? 'selected' : '' }}>Contract</option>
                                                            <option value="intern" {{ old('employment_type', $staff->employment_type) === 'intern' ? 'selected' : '' }}>Intern</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Status *</label>
                                                        <select name="status" class="form-select form-select-sm" required>
                                                            <option value="active" {{ old('status', $staff->status) === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="on_leave" {{ old('status', $staff->status) === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                                            <option value="suspended" {{ old('status', $staff->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                            <option value="terminated" {{ old('status', $staff->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Join Date</label>
                                                        <input type="date" name="join_date" class="form-control form-control-sm" value="{{ old('join_date', optional($staff->join_date)->format('Y-m-d')) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Linked System User</label>
                                                        <select name="user_id" class="form-select form-select-sm">
                                                            <option value="">-- No User Account --</option>
                                                            @foreach($users as $u)
                                                                <option value="{{ $u->id }}" {{ old('user_id', $staff->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-12 mt-3"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-cash-stack me-1"></i> Compensation &amp; Bank Details</h6></div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Base Salary ($) *</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" step="0.01" min="0" name="base_salary" class="form-control" required value="{{ old('base_salary', $staff->base_salary) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Salary Type *</label>
                                                        <select name="salary_type" class="form-select form-select-sm" required>
                                                            <option value="monthly" {{ old('salary_type', $staff->salary_type) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                            <option value="hourly" {{ old('salary_type', $staff->salary_type) === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                                            <option value="weekly" {{ old('salary_type', $staff->salary_type) === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Overtime / Hourly Rate ($)</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">$</span>
                                                            <input type="number" step="0.01" min="0" name="hourly_rate" class="form-control" value="{{ old('hourly_rate', $staff->hourly_rate) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Bank Name</label>
                                                        <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name', $staff->bank_name) }}" placeholder="e.g. ABA Bank">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Bank Account #</label>
                                                        <input type="text" name="bank_account_number" class="form-control form-control-sm font-monospace" value="{{ old('bank_account_number', $staff->bank_account_number) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-semibold">Account Holder Name</label>
                                                        <input type="text" name="bank_account_holder" class="form-control form-control-sm" value="{{ old('bank_account_holder', $staff->bank_account_holder) }}">
                                                    </div>

                                                    <div class="col-12 mt-3"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-info-circle me-1"></i> Emergency &amp; Additional Information</h6></div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Emergency Contact Person</label>
                                                        <input type="text" name="emergency_contact_name" class="form-control form-control-sm" value="{{ old('emergency_contact_name', $staff->emergency_contact_name) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold">Emergency Contact Phone</label>
                                                        <input type="text" name="emergency_contact_phone" class="form-control form-control-sm" value="{{ old('emergency_contact_phone', $staff->emergency_contact_phone) }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label small fw-semibold">Address / Notes</label>
                                                        <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Additional notes or address details...">{{ old('notes', $staff->notes) }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-check-circle me-1"></i> Update Staff Profile
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-1 fw-semibold">No staff members found.</p>
                                    <small>Click "Register New Staff" to add team members to the directory.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($staffMembers->hasPages())
                <div class="p-3 border-top">
                    {{ $staffMembers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Staff Modal -->
    <div class="modal fade" id="createStaffModal" tabindex="-1" aria-labelledby="createStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.staff.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="createStaffModalLabel">
                            <i class="bi bi-person-plus-fill me-2 text-primary"></i>Register New Staff Member
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-person me-1"></i> Personal &amp; Identification</h6></div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Staff Code (Leave blank for auto)</label>
                                <input type="text" name="staff_code" class="form-control form-control-sm font-monospace" placeholder="Auto e.g. STF-0001" value="{{ old('staff_code') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">First Name *</label>
                                <input type="text" name="first_name" class="form-control form-control-sm" required value="{{ old('first_name') }}" placeholder="First name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Last Name *</label>
                                <input type="text" name="last_name" class="form-control form-control-sm" required value="{{ old('last_name') }}" placeholder="Last name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Phone Number *</label>
                                <input type="text" name="phone" class="form-control form-control-sm" required value="{{ old('phone') }}" placeholder="+855 12 345 678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Email Address</label>
                                <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" placeholder="staff@jacksparow.com">
                            </div>

                            <div class="col-12 mt-3"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-briefcase me-1"></i> Role, Department &amp; Status</h6></div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Department *</label>
                                <input type="text" name="department" class="form-control form-control-sm" required value="{{ old('department', 'Warehouse') }}" list="deptList" placeholder="e.g. Warehouse, Sales">
                                <datalist id="deptList">
                                    <option value="Warehouse">
                                    <option value="Sales">
                                    <option value="IT & Tech">
                                    <option value="Finance">
                                    <option value="Logistics">
                                    <option value="Customer Support">
                                </datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Designation / Role *</label>
                                <input type="text" name="role" class="form-control form-control-sm" required value="{{ old('role', 'Warehouse Staff') }}" placeholder="e.g. Hardware Lead, Sales Agent">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Employment Type *</label>
                                <select name="employment_type" class="form-select form-select-sm" required>
                                    <option value="full_time" {{ old('employment_type') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                    <option value="part_time" {{ old('employment_type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                    <option value="contract" {{ old('employment_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="intern" {{ old('employment_type') === 'intern' ? 'selected' : '' }}>Intern</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Initial Status *</label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="on_leave" {{ old('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Join Date</label>
                                <input type="date" name="join_date" class="form-control form-control-sm" value="{{ old('join_date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Link to User Account</label>
                                <select name="user_id" class="form-select form-select-sm">
                                    <option value="">-- Optional Login Account --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-3"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-cash-stack me-1"></i> Compensation &amp; Salary Structure</h6></div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Base Salary ($) *</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" name="base_salary" class="form-control" required value="{{ old('base_salary', '1000.00') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Salary Type *</label>
                                <select name="salary_type" class="form-select form-select-sm" required>
                                    <option value="monthly" {{ old('salary_type', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="hourly" {{ old('salary_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="weekly" {{ old('salary_type') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Overtime / Hourly Rate ($)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" name="hourly_rate" class="form-control" value="{{ old('hourly_rate') }}" placeholder="Auto calculated if empty">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ old('bank_name') }}" placeholder="e.g. ABA Bank">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Bank Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control form-control-sm font-monospace" value="{{ old('bank_account_number') }}" placeholder="e.g. 000 123 456">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Account Holder Name</label>
                                <input type="text" name="bank_account_holder" class="form-control form-control-sm" value="{{ old('bank_account_holder') }}" placeholder="Full bank name">
                            </div>

                            <div class="col-12 mt-3"><h6 class="fw-bold text-primary mb-0"><i class="bi bi-info-circle me-1"></i> Emergency &amp; Additional Information</h6></div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Emergency Contact Person</label>
                                <input type="text" name="emergency_contact_name" class="form-control form-control-sm" value="{{ old('emergency_contact_name') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Emergency Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" class="form-control form-control-sm" value="{{ old('emergency_contact_phone') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Address / Notes</label>
                                <textarea name="notes" rows="2" class="form-control form-control-sm" placeholder="Additional notes or address details...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-circle me-1"></i> Register Staff Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

