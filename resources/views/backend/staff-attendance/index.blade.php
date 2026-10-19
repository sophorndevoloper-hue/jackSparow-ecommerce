<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People &bull; Workforce Operations</p>
                    <h1 class="h3 mb-1">Staff Attendance (Check-in &amp; Check-out)</h1>
                    <p class="text-muted mb-0">Track daily team check-ins, departures, worked hours, and automated overtime calculations.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.staff.monthly-report') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i> Monthly Report
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manualAttendanceModal">
                    <i class="bi bi-clock-history me-1"></i> Manual Attendance Entry
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-vcard me-1"></i> Staff Directory
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

        <!-- Date Selector & Navigation Banner -->
        <div class="panel p-3 mt-3 mb-3 bg-white border">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold small text-uppercase">Viewing Date:</span>
                    <h5 class="mb-0 fw-bold text-dark">
                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                        @if($isToday)
                            <span class="badge bg-primary-subtle text-primary ms-1">Today</span>
                        @endif
                    </h5>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.staff-attendance.index', ['date' => $prevDate]) }}" class="btn btn-outline-secondary btn-sm" title="Previous Day">
                        <i class="bi bi-chevron-left"></i> Prev Day
                    </a>

                    <form method="GET" action="{{ route('admin.staff-attendance.index') }}" class="d-inline-flex align-items-center gap-1">
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}" onchange="this.form.submit()">
                    </form>

                    <a href="{{ route('admin.staff-attendance.index', ['date' => $nextDate]) }}" class="btn btn-outline-secondary btn-sm" title="Next Day">
                        Next Day <i class="bi bi-chevron-right"></i>
                    </a>

                    @if(!$isToday)
                        <a href="{{ route('admin.staff-attendance.index') }}" class="btn btn-primary btn-sm">
                            Jump to Today
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attendance KPIs -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 border-start border-4 border-success">
                    <small class="text-muted text-uppercase fw-bold">Present</small>
                    <h3 class="mb-0 mt-1 fw-bold text-success">{{ $presentCount }}</h3>
                    <small class="text-muted">On time arrivals</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 border-start border-4 border-warning">
                    <small class="text-muted text-uppercase fw-bold">Late</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning">{{ $lateCount }}</h3>
                    <small class="text-muted">Checked in &gt; 9:15 AM</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 border-start border-4 border-info">
                    <small class="text-muted text-uppercase fw-bold">Half Day</small>
                    <h3 class="mb-0 mt-1 fw-bold text-info">{{ $halfDayCount }}</h3>
                    <small class="text-muted">&lt; 4.0 work hours</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 border-start border-4 border-danger">
                    <small class="text-muted text-uppercase fw-bold">Absent</small>
                    <h3 class="mb-0 mt-1 fw-bold text-danger">{{ $absentCount }}</h3>
                    <small class="text-muted">No check-in recorded</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 border-start border-4 border-primary">
                    <small class="text-muted text-uppercase fw-bold">On Duty Now</small>
                    <h3 class="mb-0 mt-1 fw-bold text-primary">{{ $checkedInNowCount }}</h3>
                    <small class="text-muted">Awaiting check-out</small>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="panel p-3 border-start border-4 border-dark">
                    <small class="text-muted text-uppercase fw-bold">Avg Hours</small>
                    <h3 class="mb-0 mt-1 fw-bold text-dark">{{ $avgWorkHours }}h</h3>
                    <small class="text-muted">Completed shifts</small>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="panel mb-3 p-3">
            <form method="GET" action="{{ route('admin.staff-attendance.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="date" value="{{ $selectedDate }}">

                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search staff member, code, role..." value="{{ request('search') }}">
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
                        <option value="">All Attendance Statuses</option>
                        <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>Late</option>
                        <option value="half_day" {{ request('status') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                        <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'department', 'status']))
                        <a href="{{ route('admin.staff-attendance.index', ['date' => $selectedDate]) }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Attendance Roster Table -->
        <div class="panel overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted text-uppercase small">
                        <tr>
                            <th class="ps-3" style="width: 280px;">Staff Member</th>
                            <th>Designation</th>
                            <th>Check-In</th>
                            <th>Check-Out</th>
                            <th>Work Hours</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Check In / Out Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roster as $item)
                            @php
                                $staff = $item['staff'];
                                $att = $item['attendance'];
                                $hasCheckedIn = !empty($item['check_in']);
                                $hasCheckedOut = !empty($item['check_out']);
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ $staff->initials }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $staff->full_name }}</div>
                                            <small class="text-muted font-monospace">{{ $staff->staff_code }} &bull; {{ $staff->department }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark fw-medium">{{ $staff->role }}</span>
                                </td>
                                <td>
                                    @if($hasCheckedIn)
                                        <span class="fw-bold font-monospace text-dark">
                                            <i class="bi bi-box-arrow-in-right text-success me-1"></i>
                                            {{ \Carbon\Carbon::parse($item['check_in'])->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($hasCheckedOut)
                                        <span class="fw-bold font-monospace text-dark">
                                            <i class="bi bi-box-arrow-right text-warning me-1"></i>
                                            {{ \Carbon\Carbon::parse($item['check_out'])->format('h:i A') }}
                                        </span>
                                    @elseif($hasCheckedIn)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle animate-pulse">
                                            <i class="bi bi-clock me-1"></i> In Progress
                                        </span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($hasCheckedOut)
                                        <div class="fw-bold font-monospace text-dark">{{ number_format($item['work_hours'], 2) }} hrs</div>
                                        @if($item['overtime_hours'] > 0)
                                            <small class="text-danger fw-semibold">+{{ number_format($item['overtime_hours'], 2) }}h OT</small>
                                        @endif
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item['status'] === 'present')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                                    @elseif($item['status'] === 'late')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Late</span>
                                    @elseif($item['status'] === 'half_day')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">Half Day</span>
                                    @elseif($item['status'] === 'on_leave')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">On Leave</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                                    @endif

                                    @if(!empty($item['notes']))
                                        <i class="bi bi-chat-left-text ms-1 text-muted small" title="{{ $item['notes'] }}"></i>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        @if(!$hasCheckedIn)
                                            <form action="{{ route('admin.staff-attendance.check-in') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i> Check In
                                                </button>
                                            </form>
                                        @elseif(!$hasCheckedOut)
                                            <form action="{{ route('admin.staff-attendance.check-out') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-box-arrow-right me-1"></i> Check Out
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-light text-muted border me-1">Shift Completed</span>
                                        @endif

                                        @if($att)
                                            <button type="button" class="btn btn-light btn-sm text-secondary" data-bs-toggle="modal" data-bs-target="#editAttendanceModal{{ $att->id }}" title="Edit / Adjust">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <!-- Modal Edit Attendance -->
                                            <div class="modal fade" id="editAttendanceModal{{ $att->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content text-start">
                                                        <form action="{{ route('admin.staff-attendance.record') }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="staff_id" value="{{ $staff->id }}">
                                                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fw-bold">
                                                                    <i class="bi bi-clock-history me-1 text-primary"></i> Adjust Attendance: {{ $staff->full_name }}
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body p-4 space-y-3">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Check-In Time</label>
                                                                    <input type="time" name="check_in" class="form-control" value="{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '' }}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Check-Out Time</label>
                                                                    <input type="time" name="check_out" class="form-control" value="{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '' }}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Status *</label>
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="present" {{ $att->status === 'present' ? 'selected' : '' }}>Present</option>
                                                                        <option value="late" {{ $att->status === 'late' ? 'selected' : '' }}>Late</option>
                                                                        <option value="half_day" {{ $att->status === 'half_day' ? 'selected' : '' }}>Half Day</option>
                                                                        <option value="absent" {{ $att->status === 'absent' ? 'selected' : '' }}>Absent</option>
                                                                        <option value="on_leave" {{ $att->status === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">Notes / Reason</label>
                                                                    <textarea name="notes" rows="2" class="form-control" placeholder="HR remarks...">{{ $att->notes }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary btn-sm">Save Adjustment</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-1 fw-semibold">No attendance records found for this date.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Manual Attendance Entry Modal -->
    <div class="modal fade" id="manualAttendanceModal" tabindex="-1" aria-labelledby="manualAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.staff-attendance.record') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="manualAttendanceModalLabel">
                            <i class="bi bi-clock-history me-2 text-primary"></i>Manual Attendance Entry / Adjustment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Staff Member *</label>
                            <select name="staff_id" class="form-select" required>
                                <option value="">-- Select Staff Member --</option>
                                @foreach($allStaff as $s)
                                    <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->staff_code }} - {{ $s->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Date *</label>
                            <input type="date" name="date" class="form-control" required value="{{ $selectedDate }}">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Check-In Time</label>
                                <input type="time" name="check_in" class="form-control" value="08:30">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold">Check-Out Time</label>
                                <input type="time" name="check_out" class="form-control" value="17:30">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="present">Present (Full Day)</option>
                                <option value="late">Late Arrival</option>
                                <option value="half_day">Half Day</option>
                                <option value="absent">Absent</option>
                                <option value="on_leave">Approved Leave</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes / Justification</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="HR adjustment remarks, leave reason, etc..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-circle me-1"></i> Record Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

