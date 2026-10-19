<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management</p>
                    <h1 class="h3 mb-1">Storefront Customers</h1>
                    <p class="text-muted mb-0">Manage customer accounts, contact details, and order history.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-truck me-1"></i> Hardware Suppliers
                </a>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Add Customer
                </button>
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
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please correct the following errors:</div>
                <ul class="mb-0 small ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Customer KPI Summary Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Customers</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($totalCount) }}</h3>
                            <small class="text-muted">
                                Registered accounts
                                @if(isset($blockedCount) && $blockedCount > 0)
                                    &bull; <span class="text-danger fw-semibold">{{ $blockedCount }} blocked</span>
                                @endif
                            </small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active Buyers</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($activeBuyersCount) }}</h3>
                            <small class="text-muted">Customers with orders</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-bag-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Orders</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($totalOrdersCount) }}</h3>
                            <small class="text-muted">Placed across store</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning p-3">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Customer Spend</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">${{ number_format($totalRevenue, 2) }}</h3>
                            <small class="text-muted">Lifetime revenue</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Toolbar -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search customer by name, email, phone, city, company...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Account Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked Only</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear search">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bulk Customer Group Assignment Bar -->
        <form id="bulkAssignCustomerForm" action="{{ route('admin.customers.bulk-assign-group') }}" method="POST">
            @csrf
            <div id="bulkActionBar" class="panel p-3 mb-3 border-start border-4 border-primary bg-primary-subtle d-none">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white fs-6 py-1 px-2" id="selectedCustomerBadge">0 customers selected</span>
                        <span class="fw-semibold text-dark">Assign selected customers to wholesale group:</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <select name="customer_group_id" class="form-select form-select-sm" style="min-width: 220px;">
                            <option value="">-- Remove from Group (Standard Retail) --</option>
                            @foreach($customerGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% off)</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">
                            <i class="bi bi-people-fill me-1"></i> Apply to Selected
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Customers Table -->
        <div class="panel p-3">
            <x-datatable id="customersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="customersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($customers as $customer)
                            <tr class="{{ $customer->is_blocked ? 'table-danger-subtle' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" form="bulkAssignCustomerForm" class="form-check-input cust-checkbox" onchange="updateCustomerBulkBar()" title="Select customer for bulk assignment">
                                        <div class="rounded-3 {{ $customer->is_blocked ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }} fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            {{ substr($customer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#customerModal_{{ $customer->id }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                                    {{ $customer->name }}
                                                </a>
                                                @if($customer->is_blocked)
                                                    <span class="badge bg-danger text-white px-2 py-0" style="font-size: 10px;">
                                                        <i class="bi bi-slash-circle me-1"></i>Blocked
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="font-size: 10px;">
                                                        <i class="bi bi-check-circle me-1"></i>Active
                                                    </span>
                                                @endif
                                            </div>
                                            @if($customer->company_name)
                                                <div class="text-muted small">{{ $customer->company_name }}</div>
                                            @endif
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">
                                                ID: #{{ $customer->id }}
                                                @if($customer->customerGroup)
                                                    &bull; <span class="text-body-secondary">{{ $customer->customerGroup->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <a href="mailto:{{ $customer->email }}" class="text-body-secondary text-decoration-none small">
                                            <i class="bi bi-envelope me-1 text-muted"></i>{{ $customer->email }}
                                        </a>
                                    </div>
                                    @if($customer->phone)
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <i class="bi bi-telephone me-1 text-muted"></i>{{ $customer->phone }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 text-body">
                                        <i class="bi bi-geo-alt text-muted"></i>
                                        <span>{{ $customer->city ? $customer->city . ($customer->country ? ', ' . $customer->country : '') : 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1 font-monospace">
                                        {{ $customer->orders_count }} {{ str('order')->plural($customer->orders_count) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success font-monospace">
                                        ${{ number_format($customer->total_spent, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $customer->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <!-- View Details Modal Trigger -->
                                        <button type="button" class="btn-ghost" data-bs-toggle="modal" data-bs-target="#customerModal_{{ $customer->id }}" title="View Customer Details">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn-ghost text-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal_{{ $customer->id }}" title="Edit Customer">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Block / Unblock Toggle -->
                                        <form action="{{ route('admin.customers.toggle-block', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $customer->is_blocked ? 'Unblock customer account ' . addslashes($customer->name) . '?' : 'Block customer account ' . addslashes($customer->name) . '? They will not be able to log in or make purchases.' }}');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-ghost {{ $customer->is_blocked ? 'text-success' : 'text-warning' }}" title="{{ $customer->is_blocked ? 'Unblock Customer' : 'Block Customer' }}">
                                                <i class="bi {{ $customer->is_blocked ? 'bi-unlock' : 'bi-slash-circle' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Action -->
                                        <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete customer \'{{ addslashes($customer->name) }}\'? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost text-danger" title="Delete Customer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-2 d-block mb-2"></i>
                                    No customers found matching the criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            @if($customers->hasPages())
                <div class="p-3 border-top">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Customer Modal -->
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0">
                <form action="{{ route('admin.customers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-plus-fill fs-5"></i>
                            <h5 class="modal-title fw-bold mb-0" id="createCustomerModalLabel">Register New Customer</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Primary Account Info -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Johnathan Doe">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="e.g. jdoe@example.com">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Password *</label>
                                <input type="password" name="password" required class="form-control" placeholder="Minimum 8 characters">
                                <small class="text-muted">Must be at least 8 characters long.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="e.g. +1 (555) 234-5678">
                            </div>

                            <!-- B2B Wholesale Group -->
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Customer Group (B2B Wholesale)</label>
                                <select name="customer_group_id" class="form-select">
                                    <option value="">None (Standard Retail)</option>
                                    @foreach($customerGroups as $group)
                                        <option value="{{ $group->id }}" {{ old('customer_group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% off)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Company Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-control" placeholder="Optional business or organization name">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Tax / VAT Number</label>
                                <input type="text" name="tax_vat_number" value="{{ old('tax_vat_number') }}" class="form-control" placeholder="Optional Tax ID or VAT code">
                            </div>

                            <!-- Address & Location -->
                            <div class="col-12">
                                <label class="form-label fw-bold small">Street Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="e.g. 742 Evergreen Terrace">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">City</label>
                                <input type="text" name="city" value="{{ old('city') }}" class="form-control" placeholder="e.g. Springfield">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">State / Province</label>
                                <input type="text" name="state" value="{{ old('state') }}" class="form-control" placeholder="e.g. Oregon">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Postal / Zip Code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code') }}" class="form-control" placeholder="e.g. 97477">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Country</label>
                                <input type="text" name="country" value="{{ old('country', 'United States') }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Credit Limit ($)</label>
                                <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', '0.00') }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light-subtle d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check-circle me-1"></i> Register Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals for Each Customer (View Details & Edit) -->
    @foreach($customers as $customer)
        <!-- 1. Edit Customer Modal -->
        <div class="modal fade" id="editCustomerModal_{{ $customer->id }}" tabindex="-1" aria-labelledby="editCustomerModalLabel_{{ $customer->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-secondary-subtle border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-pencil-square fs-5 text-primary"></i>
                                <h5 class="modal-title fw-bold mb-0 text-dark" id="editCustomerModalLabel_{{ $customer->id }}">
                                    Edit Customer: {{ $customer->name }}
                                </h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Full Name *</label>
                                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Email Address *</label>
                                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Reset Password</label>
                                    <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                                    <small class="text-muted">Only fill if you want to reset customer's password.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Phone Number</label>
                                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Customer Group (B2B Wholesale)</label>
                                    <select name="customer_group_id" class="form-select">
                                        <option value="">None (Standard Retail)</option>
                                        @foreach($customerGroups as $group)
                                            <option value="{{ $group->id }}" {{ old('customer_group_id', $customer->customer_group_id) == $group->id ? 'selected' : '' }}>
                                                {{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% off)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Company Name</label>
                                    <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Tax / VAT Number</label>
                                    <input type="text" name="tax_vat_number" value="{{ old('tax_vat_number', $customer->tax_vat_number) }}" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold small">Street Address</label>
                                    <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small">City</label>
                                    <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small">State / Province</label>
                                    <input type="text" name="state" value="{{ old('state', $customer->state) }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-bold small">Postal / Zip Code</label>
                                    <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Country</label>
                                    <input type="text" name="country" value="{{ old('country', $customer->country ?? 'United States') }}" class="form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">Credit Limit ($)</label>
                                    <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light-subtle d-flex justify-content-between">
                            <div>
                                @if($customer->is_blocked)
                                    <span class="badge bg-danger">Currently Blocked</span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Account Active</span>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. View Customer Details Modal -->
        <div class="modal fade" id="customerModal_{{ $customer->id }}" tabindex="-1" aria-labelledby="customerModalLabel_{{ $customer->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header {{ $customer->is_blocked ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-primary-subtle text-primary border-primary-subtle' }} border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle {{ $customer->is_blocked ? 'bg-danger' : 'bg-primary' }} text-white fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                {{ substr($customer->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="modal-title fw-bold mb-0 text-dark" id="customerModalLabel_{{ $customer->id }}">{{ $customer->name }}</h5>
                                    @if($customer->is_blocked)
                                        <span class="badge bg-danger text-white">Blocked</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    @endif
                                </div>
                                <small class="text-muted font-monospace">Customer ID: #{{ $customer->id }} &bull; guard: frontend</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <!-- Profile & Status Summary Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded bg-light-subtle text-center">
                                    <small class="text-muted text-uppercase d-block mb-1">Account Status</small>
                                    @if($customer->is_blocked)
                                        <span class="badge bg-danger fs-6 py-1 px-2">
                                            <i class="bi bi-slash-circle me-1"></i> Suspended / Blocked
                                        </span>
                                        @if($customer->blocked_at)
                                            <small class="d-block text-danger mt-1">Blocked {{ $customer->blocked_at->diffForHumans() }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-success-subtle text-success fs-6 py-1 px-2 border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i> Active Customer
                                        </span>
                                        <small class="d-block text-muted mt-1">
                                            Joined {{ $customer->created_at->diffForHumans() }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded bg-light-subtle text-center">
                                    <small class="text-muted text-uppercase d-block mb-1">Total Orders</small>
                                    <span class="fs-4 fw-bold font-monospace text-dark">{{ $customer->orders_count }}</span>
                                    <small class="d-block text-muted mt-1">Placed across store</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded bg-light-subtle text-center">
                                    <small class="text-muted text-uppercase d-block mb-1">Total Lifetime Spend</small>
                                    <span class="fs-4 fw-bold font-monospace text-success">${{ number_format($customer->total_spent, 2) }}</span>
                                    <small class="d-block text-muted mt-1">Gross merchandise</small>
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Address Details -->
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt-fill text-primary me-1"></i> Contact &amp; Shipping Information
                        </h6>
                        <div class="row g-2 mb-4 small">
                            <div class="col-md-6">
                                <strong class="text-muted">Email:</strong>
                                <span class="text-dark ms-1">{{ $customer->email }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-muted">Phone:</strong>
                                <span class="text-dark ms-1">{{ $customer->phone ?? 'Not provided' }}</span>
                            </div>
                            @if($customer->company_name)
                                <div class="col-md-6">
                                    <strong class="text-muted">Company:</strong>
                                    <span class="text-dark ms-1">{{ $customer->company_name }}</span>
                                </div>
                            @endif
                            @if($customer->tax_vat_number)
                                <div class="col-md-6">
                                    <strong class="text-muted">Tax/VAT ID:</strong>
                                    <span class="text-dark ms-1">{{ $customer->tax_vat_number }}</span>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <strong class="text-muted">Address:</strong>
                                <span class="text-dark ms-1">{{ $customer->address ?? 'Not provided' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-muted">City / Region:</strong>
                                <span class="text-dark ms-1">{{ $customer->city ? $customer->city . ', ' . ($customer->country ?? '') : 'Not provided' }}</span>
                            </div>
                            @if($customer->customerGroup)
                                <div class="col-md-6">
                                    <strong class="text-muted">Customer Group:</strong>
                                    <span class="badge bg-primary-subtle text-primary border-0 ms-1">{{ $customer->customerGroup->name }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Recent Orders Placed -->
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-bag-check-fill text-primary me-1"></i> Recent Orders ({{ $customer->orders->count() }})
                        </h6>
                        @if($customer->orders->isNotEmpty())
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->orders as $order)
                                            <tr>
                                                <td class="font-monospace fw-bold text-primary">{{ $order->order_number }}</td>
                                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                                <td class="fw-bold text-success">${{ number_format($order->total_amount, 2) }}</td>
                                                <td><span class="badge bg-secondary">{{ ucfirst($order->status) }}</span></td>
                                                <td><span class="badge bg-info-subtle text-info border">{{ ucfirst($order->payment_status) }}</span></td>
                                                <td class="text-end">
                                                    @canany(['view orders', 'edit orders'])
                                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-xs btn-outline-primary py-0 px-2">
                                                            Inspect
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">-</span>
                                                    @endcanany
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted small mb-0">No hardware orders recorded yet for this customer account.</p>
                        @endif
                    </div>

                    <div class="modal-footer bg-light-subtle d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <!-- Toggle Block from inside modal -->
                            <form action="{{ route('admin.customers.toggle-block', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $customer->is_blocked ? 'Unblock customer account ' . addslashes($customer->name) . '?' : 'Block customer account ' . addslashes($customer->name) . '? They will not be able to log in or make purchases.' }}');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $customer->is_blocked ? 'btn-outline-success' : 'btn-outline-warning' }}">
                                    <i class="bi {{ $customer->is_blocked ? 'bi-unlock' : 'bi-slash-circle' }} me-1"></i>
                                    {{ $customer->is_blocked ? 'Unblock Account' : 'Block Account' }}
                                </button>
                            </form>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editCustomerModal_{{ $customer->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit Profile
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function updateCustomerBulkBar() {
            const checkboxes = document.querySelectorAll('.cust-checkbox:checked');
            const count = checkboxes.length;
            const bar = document.getElementById('bulkActionBar');
            const badge = document.getElementById('selectedCustomerBadge');
            if (badge) {
                badge.innerText = count + (count === 1 ? ' customer selected' : ' customers selected');
            }
            if (bar) {
                if (count > 0) {
                    bar.classList.remove('d-none');
                } else {
                    bar.classList.add('d-none');
                }
            }
        }
    </script>
</x-app-layout>
