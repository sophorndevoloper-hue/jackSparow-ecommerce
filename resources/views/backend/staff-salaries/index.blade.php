<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cash-coin" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People &bull; Compensation &amp; Payroll</p>
                    <h1 class="h3 mb-1">Staff Salaries &amp; Payroll</h1>
                    <p class="text-muted mb-0">Manage staff compensation, generate monthly payroll runs, compute overtime bonuses, and track disbursements.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.staff.monthly-report') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i> Monthly Report
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
                    <i class="bi bi-calculator me-1"></i> Generate Monthly Payroll Run
                </button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-person-vcard me-1"></i> Staff Directory
                </a>
                <a href="{{ route('admin.staff-attendance.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-calendar-check me-1"></i> Attendance Logs
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

        <!-- Month Navigation Bar -->
        <div class="panel p-3 mt-3 mb-3 bg-white border">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted fw-semibold small text-uppercase">Payroll Period:</span>
                    <h5 class="mb-0 fw-bold text-dark">
                        {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                    </h5>
                    <span class="badge bg-light text-muted border font-monospace">
                        {{ $periodStart }} to {{ $periodEnd }}
                    </span>
                </div>

                <form method="GET" action="{{ route('admin.staff-salaries.index') }}" class="d-flex align-items-center gap-2">
                    <label class="small fw-semibold text-muted text-nowrap">Select Month:</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <!-- Payroll Summary KPIs -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Payroll Net</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">${{ number_format($totalPayrollExpense, 2) }}</h3>
                            <small class="text-muted">Total for {{ $selectedMonth }}</small>
                        </div>
                        <div class="fs-1 text-primary opacity-25">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Disbursed (Paid)</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">${{ number_format($paidAmount, 2) }}</h3>
                            <small class="text-muted">{{ $paidCount }} pay slips completed</small>
                        </div>
                        <div class="fs-1 text-success opacity-25">
                            <i class="bi bi-check2-all"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Pending Approval</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning">${{ number_format($pendingAmount, 2) }}</h3>
                            <small class="text-muted">{{ $pendingCount }} pay slips pending</small>
                        </div>
                        <div class="fs-1 text-warning opacity-25">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Unprocessed Staff</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ $unprocessedStaffCount }}</h3>
                            <small class="text-muted">Active staff without pay slip</small>
                        </div>
                        <div class="fs-1 text-info opacity-25">
                            <i class="bi bi-person-exclamation"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="panel mb-3 p-3">
            <form method="GET" action="{{ route('admin.staff-salaries.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">

                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search staff name, code, department..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Payroll Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft (Pending)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'status']))
                        <a href="{{ route('admin.staff-salaries.index', ['month' => $selectedMonth]) }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Payroll Roster Table -->
        <div class="panel overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted text-uppercase small">
                        <tr>
                            <th class="ps-3">Pay Slip #</th>
                            <th>Staff Member</th>
                            <th>Base Salary</th>
                            <th>Days Worked</th>
                            <th>Overtime / Bonus</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $payroll)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-dark">
                                    {{ $payroll->payroll_code }}
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $payroll->staff->full_name }}</div>
                                    <small class="text-muted font-monospace">{{ $payroll->staff->staff_code }} &bull; {{ $payroll->staff->role }}</small>
                                </td>
                                <td>
                                    <span class="font-monospace text-dark fw-medium">${{ number_format($payroll->base_salary, 2) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $payroll->worked_days }} days</span>
                                </td>
                                <td>
                                    @php $totalAdditions = $payroll->overtime_amount + $payroll->bonus_amount; @endphp
                                    @if($totalAdditions > 0)
                                        <span class="text-success fw-bold font-monospace">+${{ number_format($totalAdditions, 2) }}</span>
                                        @if($payroll->overtime_amount > 0)
                                            <small class="d-block text-muted">OT: ${{ number_format($payroll->overtime_amount, 2) }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payroll->deductions_amount > 0)
                                        <span class="text-danger fw-bold font-monospace">-${{ number_format($payroll->deductions_amount, 2) }}</span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 text-primary font-monospace">${{ number_format($payroll->net_salary, 2) }}</div>
                                    <small class="text-muted text-capitalize">{{ str_replace('_', ' ', $payroll->payment_method) }}</small>
                                </td>
                                <td>
                                    <span class="{{ $payroll->status_badge_class }} text-capitalize">
                                        {{ $payroll->status }}
                                    </span>
                                    @if($payroll->status === 'paid' && $payroll->payment_date)
                                        <small class="d-block text-muted mt-0.5">{{ \Carbon\Carbon::parse($payroll->payment_date)->format('M d, Y') }}</small>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editPayrollModal{{ $payroll->id }}" title="Adjust Bonus / Deductions">
                                            <i class="bi bi-sliders"></i>
                                        </button>

                                        @if($payroll->status !== 'paid')
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#payModal{{ $payroll->id }}">
                                                <i class="bi bi-check2-circle me-1"></i> Pay
                                            </button>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bi bi-check-all me-1"></i> Paid
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Edit / Adjust Payroll Modal -->
                                    <div class="modal fade" id="editPayrollModal{{ $payroll->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.staff-salaries.update-status', $payroll) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="bi bi-sliders me-1 text-primary"></i> Adjust Pay Slip: {{ $payroll->payroll_code }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 space-y-3">
                                                        <div class="p-2 bg-light rounded small mb-3">
                                                            <div><strong>Staff:</strong> {{ $payroll->staff->full_name }} ({{ $payroll->staff->role }})</div>
                                                            <div><strong>Base Salary:</strong> ${{ number_format($payroll->base_salary, 2) }}</div>
                                                            <div><strong>Overtime Calculated:</strong> ${{ number_format($payroll->overtime_amount, 2) }}</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Discretionary Bonus ($)</label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" step="0.01" min="0" name="bonus_amount" class="form-control" value="{{ $payroll->bonus_amount }}">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Deductions / Penalties ($)</label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" step="0.01" min="0" name="deductions_amount" class="form-control" value="{{ $payroll->deductions_amount }}">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Payroll Status *</label>
                                                            <select name="status" class="form-select form-select-sm" required>
                                                                <option value="draft" {{ $payroll->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                                <option value="approved" {{ $payroll->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                                                <option value="paid" {{ $payroll->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary btn-sm">Save Adjustments</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pay Modal -->
                                    <div class="modal fade" id="payModal{{ $payroll->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.staff-salaries.record-payment', $payroll) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">
                                                            <i class="bi bi-cash-stack me-1 text-success"></i> Disburse Salary Payment
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4 space-y-3">
                                                        <div class="p-3 bg-light rounded mb-3">
                                                            <div class="text-muted small">Payee: <strong class="text-dark">{{ $payroll->staff->full_name }}</strong></div>
                                                            <div class="text-muted small">Bank: <strong class="text-dark">{{ $payroll->staff->bank_name ?: 'Cash Disbursement' }} ({{ $payroll->staff->bank_account_number ?: 'N/A' }})</strong></div>
                                                            <div class="mt-2 text-dark fs-5 fw-bold font-monospace">Net Amount: ${{ number_format($payroll->net_salary, 2) }}</div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Payment Date *</label>
                                                            <input type="date" name="payment_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Payment Method *</label>
                                                            <select name="payment_method" class="form-select form-select-sm" required>
                                                                <option value="bank_transfer" {{ $payroll->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                                <option value="cash" {{ $payroll->payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                                                <option value="check" {{ $payroll->payment_method === 'check' ? 'selected' : '' }}>Company Check</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Reference # / Notes</label>
                                                            <input type="text" name="reference_note" class="form-control form-control-sm" placeholder="e.g. Wire Ref # 9928120">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success btn-sm">Confirm &amp; Mark Paid</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-wallet2 display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    <p class="mb-1 fw-semibold">No payroll records generated for {{ $selectedMonth }}.</p>
                                    <small>Click "Generate Monthly Payroll Run" above to calculate pay slips for all active staff.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payrolls->hasPages())
                <div class="p-3 border-top">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Generate Monthly Payroll Modal -->
    <div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-labelledby="generatePayrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.staff-salaries.generate') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="generatePayrollModalLabel">
                            <i class="bi bi-calculator me-2 text-primary"></i>Generate Monthly Payroll Run
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted small">
                            This will calculate monthly pay slips for all <strong>active staff</strong> members who have not yet had a payroll record generated for the selected month.
                        </p>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Target Payroll Month *</label>
                            <input type="month" name="month" class="form-control" required value="{{ $selectedMonth }}">
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Attendance records within this month will be scanned to tally worked days and calculate hourly overtime additions automatically.
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-play-circle me-1"></i> Run Payroll Generation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

