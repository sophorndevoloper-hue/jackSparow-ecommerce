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
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.customer-group-payment-terms.index') }}" class="btn btn-outline-secondary btn-sm" title="Manage Customer Group Payment Terms">
                    <i class="bi bi-calendar2-check me-1"></i> Payment Terms
                </a>
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
                                <td>
                                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#assignCustomersModal{{ $group->id }}" class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1 text-decoration-none" title="Click to view and manage assigned users">
                                        <i class="bi bi-people me-1"></i>{{ $group->frontend_users_count }} {{ str('user')->plural($group->frontend_users_count) }}
                                    </a>
                                </td>
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
                                            <button type="button" class="btn-ghost text-info" data-bs-toggle="modal" data-bs-target="#assignCustomersModal{{ $group->id }}" title="Assign / Manage Customers">
                                                <i class="bi bi-people"></i>
                                            </button>
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

    @canany(['edit customer groups', 'edit customers'])
        @foreach($groups as $group)
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

            <!-- Manage / Assign Customers Modal -->
            <div class="modal fade" id="assignCustomersModal{{ $group->id }}" tabindex="-1" aria-labelledby="assignCustomersModalLabel{{ $group->id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content shadow-lg border-0">
                        <div class="modal-header bg-primary text-white">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-people-fill fs-5"></i>
                                <div>
                                    <h5 class="modal-title fw-bold mb-0" id="assignCustomersModalLabel{{ $group->id }}">Manage Customers: {{ $group->name }}</h5>
                                    <small class="text-white-50 font-monospace">Group Code: {{ $group->code }} &bull; {{ number_format($group->default_discount_percentage, 1) }}% Discount</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <!-- Nav tabs -->
                            <ul class="nav nav-pills nav-fill mb-3 bg-light rounded p-1" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active btn-sm" id="assigned-tab-{{ $group->id }}" data-bs-toggle="pill" data-bs-target="#assigned-pane-{{ $group->id }}" type="button" role="tab">
                                        <i class="bi bi-person-check me-1"></i> Assigned Customers ({{ $group->frontendUsers->count() }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link btn-sm" id="add-tab-{{ $group->id }}" data-bs-toggle="pill" data-bs-target="#add-pane-{{ $group->id }}" type="button" role="tab">
                                        <i class="bi bi-person-plus me-1"></i> Add Customers to Group
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <!-- Tab 1: Currently Assigned Customers -->
                                <div class="tab-pane fade show active" id="assigned-pane-{{ $group->id }}" role="tabpanel">
                                    @if($group->frontendUsers->isNotEmpty())
                                        <div class="table-responsive border rounded" style="max-height: 340px; overflow-y: auto;">
                                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th>Customer</th>
                                                        <th>Email</th>
                                                        <th>Orders</th>
                                                        <th>Total Spend</th>
                                                        <th class="text-end">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($group->frontendUsers as $assignedCustomer)
                                                        <tr>
                                                            <td>
                                                                <span class="fw-semibold text-dark">{{ $assignedCustomer->name }}</span>
                                                                @if($assignedCustomer->company_name)
                                                                    <small class="text-muted d-block">{{ $assignedCustomer->company_name }}</small>
                                                                @endif
                                                            </td>
                                                            <td><span class="text-muted small">{{ $assignedCustomer->email }}</span></td>
                                                            <td><span class="badge bg-secondary-subtle text-body">{{ $assignedCustomer->orders_count }}</span></td>
                                                            <td class="font-monospace text-success fw-bold">${{ number_format($assignedCustomer->total_spent, 2) }}</td>
                                                            <td class="text-end">
                                                                <form action="{{ route('admin.customer-groups.remove-user', [$group->id, $assignedCustomer->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove \'{{ addslashes($assignedCustomer->name) }}\' from this customer group?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2" title="Remove from group">
                                                                        <i class="bi bi-x-circle me-1"></i> Remove
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4 text-muted">
                                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                                            No customers are currently assigned to this group.
                                            <p class="small mt-1 mb-0">Switch to the "Add Customers to Group" tab to add users.</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Tab 2: Add Customers to Group -->
                                <div class="tab-pane fade" id="add-pane-{{ $group->id }}" role="tabpanel">
                                    <form action="{{ route('admin.customer-groups.assign-users', $group->id) }}" method="POST">
                                        @csrf
                                        <p class="text-muted small mb-2">
                                            Select one or more customers to add to <strong>{{ $group->name }}</strong>.
                                        </p>

                                        @php
                                            $availableCustomers = $allCustomers->filter(fn($c) => $c->customer_group_id !== $group->id);
                                        @endphp

                                        @if($availableCustomers->isNotEmpty())
                                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAllGroup{{ $group->id }}" onchange="toggleSelectAllGroup('{{ $group->id }}', this)">
                                                    <label class="form-check-label small fw-semibold" for="selectAllGroup{{ $group->id }}">
                                                        Select All ({{ $availableCustomers->count() }} available)
                                                    </label>
                                                </div>
                                                <input type="text" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Filter customers..." onkeyup="filterCustomerList('{{ $group->id }}', this.value)">
                                            </div>

                                            <div class="border rounded p-2" id="customerListGroup{{ $group->id }}" style="max-height: 280px; overflow-y: auto;">
                                                @foreach($availableCustomers as $cust)
                                                    <div class="form-check py-1 px-3 border-bottom customer-item-{{ $group->id }}" data-search="{{ strtolower($cust->name . ' ' . $cust->email . ' ' . ($cust->company_name ?? '')) }}">
                                                        <input class="form-check-input group-cust-checkbox-{{ $group->id }}" type="checkbox" name="customer_ids[]" value="{{ $cust->id }}" id="cust_{{ $group->id }}_{{ $cust->id }}">
                                                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="cust_{{ $group->id }}_{{ $cust->id }}">
                                                            <div>
                                                                <span class="fw-semibold text-dark">{{ $cust->name }}</span>
                                                                <small class="text-muted ms-1">({{ $cust->email }})</small>
                                                                @if($cust->company_name)
                                                                    <small class="text-muted d-block">{{ $cust->company_name }}</small>
                                                                @endif
                                                            </div>
                                                            <div class="text-end">
                                                                @if($cust->customer_group_id)
                                                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">In other group</span>
                                                                @else
                                                                    <span class="badge bg-light text-muted border" style="font-size: 10px;">Unassigned</span>
                                                                @endif
                                                            </div>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="d-flex justify-content-end gap-2 mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-person-plus-fill me-1"></i> Add Selected to {{ $group->name }}
                                                </button>
                                            </div>
                                        @else
                                            <div class="text-center py-4 text-muted">
                                                <i class="bi bi-check-circle-fill fs-2 text-success d-block mb-2"></i>
                                                All registered storefront customers are already in this group!
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endcanany

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

    <script>
        function toggleSelectAllGroup(groupId, selectAllEl) {
            const checkboxes = document.querySelectorAll('.group-cust-checkbox-' + groupId);
            checkboxes.forEach(cb => {
                const item = cb.closest('.customer-item-' + groupId);
                if (item && item.style.display !== 'none') {
                    cb.checked = selectAllEl.checked;
                }
            });
        }

        function filterCustomerList(groupId, query) {
            const filter = query.toLowerCase().trim();
            const items = document.querySelectorAll('.customer-item-' + groupId);
            items.forEach(item => {
                const searchData = item.getAttribute('data-search') || '';
                if (searchData.includes(filter)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</x-app-layout>

