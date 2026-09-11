<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">B2B Account Management</p>
                    <h1 class="h3 mb-1">Customer Groups &amp; Wholesale Tiers</h1>
                    <p class="text-muted mb-0">Configure B2B account groups, default discount percentages, payment terms, and credit limits.</p>
                </div>
            </div>
            <div class="heading-actions">
                @canany(['create customer groups', 'create customers'])
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Customer Group
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="panel p-3 mt-3">
            <x-datatable id="customerGroupsTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="customerGroupsTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($groups as $group)
                            <tr>
                                <td>
                                    <div>
                                        <div class="fw-semibold text-body-emphasis d-inline-block">{{ $group->name }}</div>
                                        @if($group->tax_exempt)
                                            <span class="badge bg-info-subtle text-info border-0 px-2 py-0 ms-1" style="font-size: 10px;">Tax Exempt</span>
                                        @endif
                                        @if($group->description)
                                            <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;">{{ $group->description }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $group->code }}</span></td>
                                <td><span class="fw-semibold text-success">{{ number_format($group->default_discount_percentage, 1) }}%</span></td>
                                <td><span class="text-body-emphasis small">{{ $group->payment_terms_days > 0 ? 'Net '.$group->payment_terms_days.' Days' : 'Immediate / Prepaid' }}</span></td>
                                <td><span class="text-body-emphasis small">${{ number_format($group->credit_limit, 2) }}</span></td>
                                <td><span class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1">{{ $group->frontend_users_count }} users</span></td>
                                <td><span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">{{ $group->price_tiers_count }} tiers</span></td>
                                <td>
                                    @if($group->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @canany(['edit customer groups', 'edit customers'])
                                            <button type="button" class="btn-ghost text-primary" data-bs-toggle="modal" data-bs-target="#editGroupModal{{ $group->id }}" title="Edit Customer Group">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcanany
                                        @canany(['delete customer groups', 'delete customers'])
                                            <form action="{{ route('admin.customer-groups.destroy', $group->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete customer group?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete Customer Group">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>

                            @canany(['edit customer groups', 'edit customers'])
                                <!-- Edit Modal -->
                                <div class="modal fade" id="editGroupModal{{ $group->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.customer-groups.update', $group->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Group: {{ $group->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Group Name *</label>
                                                        <input type="text" name="name" value="{{ $group->name }}" required class="form-control">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small">Group Code *</label>
                                                        <input type="text" name="code" value="{{ $group->code }}" required class="form-control font-monospace">
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small">Default Discount % *</label>
                                                            <input type="number" step="0.1" name="default_discount_percentage" value="{{ $group->default_discount_percentage }}" required class="form-control">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small">Payment Terms (Days)</label>
                                                            <input type="number" name="payment_terms_days" value="{{ $group->payment_terms_days }}" class="form-control" placeholder="e.g. 30">
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small">Min Order Amount ($)</label>
                                                            <input type="number" step="0.01" name="min_order_amount" value="{{ $group->min_order_amount }}" class="form-control">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-bold small">Credit Limit ($)</label>
                                                            <input type="number" step="0.01" name="credit_limit" value="{{ $group->credit_limit }}" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch mb-2">
                                                        <input class="form-check-input" type="checkbox" name="tax_exempt" value="1" id="editTaxExempt{{ $group->id }}" {{ $group->tax_exempt ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold small" for="editTaxExempt{{ $group->id }}">Tax Exempt</label>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive{{ $group->id }}" {{ $group->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold small" for="editIsActive{{ $group->id }}">Active</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm fw-bold">Update Group</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcanany
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No customer groups configured yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>
        </div>
    </div>

    @canany(['create customer groups', 'create customers'])
        <!-- Create Modal -->
        <div class="modal fade" id="addGroupModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('admin.customer-groups.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Add Customer Group</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Group Name *</label>
                                <input type="text" name="name" required class="form-control" placeholder="e.g. Enterprise Tier A">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Group Code *</label>
                                <input type="text" name="code" required class="form-control font-monospace" placeholder="e.g. ENTERPRISE_A">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Default Discount % *</label>
                                    <input type="number" step="0.1" name="default_discount_percentage" value="15.0" required class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Payment Terms (Days)</label>
                                    <input type="number" name="payment_terms_days" value="30" class="form-control" placeholder="30">
                                </div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Min Order Amount ($)</label>
                                    <input type="number" step="0.01" name="min_order_amount" value="500.00" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Credit Limit ($)</label>
                                    <input type="number" step="0.01" name="credit_limit" value="25000.00" class="form-control">
                                </div>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="tax_exempt" value="1" id="createTaxExempt">
                                <label class="form-check-label fw-bold small" for="createTaxExempt">Tax Exempt</label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                                <label class="form-check-label fw-bold small" for="createIsActive">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm fw-bold">Create Customer Group</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcanany
</x-app-layout>

