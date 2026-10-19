<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Heading -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">B2B Account Management</p>
                    <h1 class="h3 mb-1">Customer Group Payment Terms</h1>
                    <p class="text-muted mb-0">Configure credit settlement windows, early payment incentives, late penalty fees, and deposit rules for customer groups.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.customer-groups.index') }}" class="btn btn-outline-secondary btn-sm" title="Manage Customer Groups">
                    <i class="bi bi-tags-fill me-1"></i> Customer Groups
                </a>
                @canany(['create customer group payment terms', 'create customer groups'])
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createPaymentTermModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Payment Term
                    </button>
                @endcanany
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
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
            <div class="col-6 col-md-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Total Payment Terms</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ $totalTerms }}</h3>
                            <small class="text-muted">Defined in system</small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-calendar2-week fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Active Terms</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ $activeTerms }}</h3>
                            <small class="text-muted">Available for checkout</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Group Specific</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ $groupSpecificTerms }}</h3>
                            <small class="text-muted">Dedicated to wholesale</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 11px;">Discount Incentives</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning">{{ $discountTerms }}</h3>
                            <small class="text-muted">Early settlement bonus</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning p-3">
                            <i class="bi bi-percent fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="panel p-3 mb-3">
            <form method="GET" action="{{ route('admin.customer-group-payment-terms.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by term name, code, description...">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="customer_group_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Customer Groups</option>
                        <option value="global" {{ request('customer_group_id') === 'global' ? 'selected' : '' }}>Global / Unassigned</option>
                        @foreach($customerGroups as $group)
                            <option value="{{ $group->id }}" {{ request('customer_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                    @if(request('search') || request('customer_group_id') || request('status'))
                        <a href="{{ route('admin.customer-group-payment-terms.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Payment Terms Table -->
        <div class="panel p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Term Name &amp; Code</th>
                            <th>Customer Group</th>
                            <th>Payment Window</th>
                            <th>Early Settlement Discount</th>
                            <th>Late Penalty</th>
                            <th>Deposit Req.</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentTerms as $term)
                            <tr>
                                <td>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis d-inline-block">
                                            {{ $term->name }}
                                        </div>
                                        @if($term->is_default)
                                            <span class="badge bg-primary-subtle text-primary border-0 px-2 py-0 ms-1" style="font-size: 10px;">Default</span>
                                        @endif
                                        <div class="text-muted small font-monospace" style="font-size: 11px;">
                                            Code: {{ $term->code }}
                                        </div>
                                        @if($term->description)
                                            <div class="text-muted small text-truncate" style="max-width: 260px; font-size: 11px;">
                                                {{ $term->description }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($term->customerGroup)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                                            <i class="bi bi-people-fill me-1"></i>{{ $term->customerGroup->name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1">
                                            <i class="bi bi-globe me-1"></i>All Customer Groups
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($term->days === 0)
                                        <span class="badge bg-secondary-subtle text-body border-0 px-2 py-1">
                                            Immediate / COD
                                        </span>
                                    @else
                                        <span class="fw-bold text-dark font-monospace">Net {{ $term->days }}</span>
                                        <small class="text-muted d-block" style="font-size: 11px;">{{ $term->days }} days from invoice</small>
                                    @endif
                                </td>
                                <td>
                                    @if($term->early_payment_discount_percentage > 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            {{ number_format($term->early_payment_discount_percentage, 1) }}% within {{ $term->early_payment_days }}d
                                        </span>
                                    @else
                                        <span class="text-muted small">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($term->late_fee_percentage > 0)
                                        <span class="text-danger fw-semibold small">
                                            {{ number_format($term->late_fee_percentage, 1) }}% / month
                                        </span>
                                    @else
                                        <span class="text-muted small">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($term->require_deposit)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            {{ number_format($term->deposit_percentage, 1) }}% Upfront
                                        </span>
                                    @else
                                        <span class="text-muted small">No deposit</span>
                                    @endif
                                </td>
                                <td>
                                    @if($term->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @canany(['edit customer group payment terms', 'edit customer groups'])
                                            <form action="{{ route('admin.customer-group-payment-terms.toggle-status', $term->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-ghost {{ $term->is_active ? 'text-warning' : 'text-success' }}" title="{{ $term->is_active ? 'Deactivate Term' : 'Activate Term' }}">
                                                    <i class="bi {{ $term->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn-ghost text-primary" data-bs-toggle="modal" data-bs-target="#editPaymentTermModal{{ $term->id }}" title="Edit Payment Term">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcanany
                                        @canany(['delete customer group payment terms', 'delete customer groups'])
                                            <form action="{{ route('admin.customer-group-payment-terms.destroy', $term->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete payment term \'{{ addslashes($term->name) }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete Payment Term">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar2-x fs-2 d-block mb-2"></i>
                                    No customer group payment terms found matching the filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($paymentTerms->hasPages())
                <div class="p-3 border-top">
                    {{ $paymentTerms->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Payment Term Modal -->
    @canany(['create customer group payment terms', 'create customer groups'])
        <div class="modal fade" id="createPaymentTermModal" tabindex="-1" aria-labelledby="createPaymentTermModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <form action="{{ route('admin.customer-group-payment-terms.store') }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar2-plus fs-5"></i>
                                <h5 class="modal-title fw-bold mb-0" id="createPaymentTermModalLabel">Add Customer Group Payment Term</h5>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Term Name *</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Net 30 Days or 2/10 Net 30">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Term Code *</label>
                                    <input type="text" name="code" value="{{ old('code') }}" required class="form-control font-monospace" placeholder="e.g. NET_30">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Applicable Customer Group</label>
                                    <select name="customer_group_id" class="form-select">
                                        <option value="">All Customer Groups (Global)</option>
                                        @foreach($customerGroups as $group)
                                            <option value="{{ $group->id }}" {{ old('customer_group_id') == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% discount)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select a specific wholesale tier or leave open for all.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Payment Period (Days) *</label>
                                    <input type="number" name="days" value="{{ old('days', 30) }}" required min="0" max="365" class="form-control" placeholder="0 for COD, 30 for Net 30">
                                    <small class="text-muted">Number of days before payment is considered past due.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold small">Description / Customer Invoice Note</label>
                                    <textarea name="description" rows="2" class="form-control" placeholder="Brief note printed on customer statements and orders...">{{ old('description') }}</textarea>
                                </div>

                                <div class="col-12">
                                    <hr class="my-1">
                                    <h6 class="fw-bold text-dark mt-2 mb-2"><i class="bi bi-percent me-1 text-primary"></i> Early Settlement &amp; Penalties</h6>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small">Early Discount %</label>
                                    <input type="number" step="0.1" name="early_payment_discount_percentage" value="{{ old('early_payment_discount_percentage', 0.0) }}" min="0" max="100" class="form-control">
                                    <small class="text-muted">e.g. 2.0% for 2/10 Net 30</small>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small">Early Discount Window (Days)</label>
                                    <input type="number" name="early_payment_days" value="{{ old('early_payment_days', 0) }}" min="0" max="365" class="form-control">
                                    <small class="text-muted">Qualifying payment days</small>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small">Late Penalty Fee (% / Mo)</label>
                                    <input type="number" step="0.1" name="late_fee_percentage" value="{{ old('late_fee_percentage', 0.0) }}" min="0" max="100" class="form-control">
                                    <small class="text-muted">Past-due interest</small>
                                </div>

                                <div class="col-12">
                                    <hr class="my-1">
                                    <h6 class="fw-bold text-dark mt-2 mb-2"><i class="bi bi-shield-check me-1 text-primary"></i> Deposit &amp; Options</h6>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="require_deposit" value="1" id="createRequireDeposit" {{ old('require_deposit') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="createRequireDeposit">Require Upfront Deposit</label>
                                    </div>
                                    <div class="mt-2">
                                        <label class="form-label small text-muted">Deposit Required (%)</label>
                                        <input type="number" step="0.1" name="deposit_percentage" value="{{ old('deposit_percentage', 25.0) }}" min="0" max="100" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch mt-2 mb-3">
                                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="createIsDefault" {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="createIsDefault">Default Term for Group</label>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                                        <label class="form-check-label fw-bold small" for="createIsActive">Active (Available for orders)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Payment Term</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcanany

    <!-- Edit Payment Term Modals (Rendered safely outside table) -->
    @canany(['edit customer group payment terms', 'edit customer groups'])
        @foreach($paymentTerms as $term)
            <div class="modal fade" id="editPaymentTermModal{{ $term->id }}" tabindex="-1" aria-labelledby="editPaymentTermModalLabel{{ $term->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow-lg border-0">
                        <form action="{{ route('admin.customer-group-payment-terms.update', $term->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header bg-secondary-subtle border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-pencil-square fs-5 text-primary"></i>
                                    <h5 class="modal-title fw-bold mb-0 text-dark" id="editPaymentTermModalLabel{{ $term->id }}">
                                        Edit Payment Term: {{ $term->name }}
                                    </h5>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small">Term Name *</label>
                                        <input type="text" name="name" value="{{ old('name', $term->name) }}" required class="form-control">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small">Term Code *</label>
                                        <input type="text" name="code" value="{{ old('code', $term->code) }}" required class="form-control font-monospace">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small">Applicable Customer Group</label>
                                        <select name="customer_group_id" class="form-select">
                                            <option value="">All Customer Groups (Global)</option>
                                            @foreach($customerGroups as $group)
                                                <option value="{{ $group->id }}" {{ old('customer_group_id', $term->customer_group_id) == $group->id ? 'selected' : '' }}>
                                                    {{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% discount)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small">Payment Period (Days) *</label>
                                        <input type="number" name="days" value="{{ old('days', $term->days) }}" required min="0" max="365" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Description / Customer Invoice Note</label>
                                        <textarea name="description" rows="2" class="form-control">{{ old('description', $term->description) }}</textarea>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-1">
                                        <h6 class="fw-bold text-dark mt-2 mb-2"><i class="bi bi-percent me-1 text-primary"></i> Early Settlement &amp; Penalties</h6>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-bold small">Early Discount %</label>
                                        <input type="number" step="0.1" name="early_payment_discount_percentage" value="{{ old('early_payment_discount_percentage', $term->early_payment_discount_percentage) }}" min="0" max="100" class="form-control">
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-bold small">Early Discount Window (Days)</label>
                                        <input type="number" name="early_payment_days" value="{{ old('early_payment_days', $term->early_payment_days) }}" min="0" max="365" class="form-control">
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label fw-bold small">Late Penalty Fee (% / Mo)</label>
                                        <input type="number" step="0.1" name="late_fee_percentage" value="{{ old('late_fee_percentage', $term->late_fee_percentage) }}" min="0" max="100" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-1">
                                        <h6 class="fw-bold text-dark mt-2 mb-2"><i class="bi bi-shield-check me-1 text-primary"></i> Deposit &amp; Options</h6>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" name="require_deposit" value="1" id="editRequireDeposit{{ $term->id }}" {{ old('require_deposit', $term->require_deposit) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold small" for="editRequireDeposit{{ $term->id }}">Require Upfront Deposit</label>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label small text-muted">Deposit Required (%)</label>
                                            <input type="number" step="0.1" name="deposit_percentage" value="{{ old('deposit_percentage', $term->deposit_percentage) }}" min="0" max="100" class="form-control form-control-sm">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="form-check form-switch mt-2 mb-3">
                                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editIsDefault{{ $term->id }}" {{ old('is_default', $term->is_default) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold small" for="editIsDefault{{ $term->id }}">Default Term for Group</label>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive{{ $term->id }}" {{ old('is_active', $term->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold small" for="editIsActive{{ $term->id }}">Active (Available for orders)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">Update Payment Term</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endcanany
</x-app-layout>

