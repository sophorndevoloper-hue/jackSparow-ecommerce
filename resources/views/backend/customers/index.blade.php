<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management</p>
                    <h1 class="h3 mb-1">Storefront Customers</h1>
                    <p class="text-muted mb-0">Manage customer accounts, track spending, and review <strong>Simple</strong> &amp; <strong>Special (VIP)</strong> customer tiers.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-truck me-1"></i> Hardware Suppliers
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Customer Tier KPI Summary Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Customers</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($totalCount) }}</h3>
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
                            <small class="text-muted text-uppercase fw-bold">Simple Customers</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($simpleCount) }}</h3>
                            <small class="text-muted">Standard buyers</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-person fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Special (VIP) Customers</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning">{{ number_format($specialCount) }}</h3>
                            <small class="text-muted">3+ orders or promoted</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning p-3">
                            <i class="bi bi-star-fill fs-4"></i>
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
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-cash-stack fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="panel p-3 mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <!-- Tier Filter Tabs -->
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <a href="{{ route('admin.customers.index', array_merge(request()->except('type', 'page'))) }}" 
                           class="btn {{ !request('type') ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All ({{ $totalCount }})
                        </a>
                        <a href="{{ route('admin.customers.index', array_merge(request()->except('page'), ['type' => 'simple'])) }}" 
                           class="btn {{ request('type') === 'simple' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Simple ({{ $simpleCount }})
                        </a>
                        <a href="{{ route('admin.customers.index', array_merge(request()->except('page'), ['type' => 'special'])) }}" 
                           class="btn {{ request('type') === 'special' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                            <i class="bi bi-star-fill text-warning me-1"></i> Special ({{ $specialCount }})
                        </a>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <form action="{{ route('admin.customers.index') }}" method="GET" class="d-flex gap-2">
                        @if(request('type'))
                            <input type="hidden" name="type" value="{{ request('type') }}">
                        @endif
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search customer by name, email, phone, city...">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm px-3">Search</button>
                    </form>
                </div>

                <div class="col-12 col-md-2 text-end">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm w-100">Clear Filters</a>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <!-- Customers Table -->
        <div class="panel p-3">
            <x-datatable id="customersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="customersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 {{ $customer->customer_type === 'special' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-primary-subtle text-primary' }} fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            {{ substr($customer->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#customerModal_{{ $customer->id }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                                {{ $customer->name }}
                                            </a>
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">ID: #{{ $customer->id }} &bull; frontend</div>
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
                                    @if($customer->customer_type === 'special')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border-0 px-2 py-1">
                                            <i class="bi bi-star-fill me-1 text-warning"></i>Special Customer (VIP)
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info-emphasis border-0 px-2 py-1">
                                            Simple Customer
                                        </span>
                                        @if($customer->orders_count < 3)
                                            <div class="text-muted small mt-1" style="font-size: 11px;">
                                                <i class="bi bi-arrow-up-circle me-1"></i>{{ 3 - $customer->orders_count }} more orders &rarr; Special
                                            </div>
                                        @endif
                                    @endif
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
                                        <button type="button" class="btn-ghost" data-bs-toggle="modal" data-bs-target="#customerModal_{{ $customer->id }}" title="View Customer Details & Orders">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Toggle Special / Simple Tier Form -->
                                        @can('edit customers')
                                            <form action="{{ route('admin.customers.toggle-special', $customer->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                @if($customer->customer_type === 'special')
                                                    <button type="submit" class="btn-ghost text-warning" title="Downgrade to Simple Customer">
                                                        <i class="bi bi-star"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn-ghost text-warning" title="Promote to Special VIP Customer">
                                                        <i class="bi bi-star-fill"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        @endcan
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

    <!-- Customer Detail Modals (Strictly rendered outside of table for DOM safety) -->
    @foreach($customers as $customer)
        <div class="modal fade" id="customerModal_{{ $customer->id }}" tabindex="-1" aria-labelledby="customerModalLabel_{{ $customer->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header {{ $customer->customer_type === 'special' ? 'bg-warning-subtle text-dark border-bottom border-warning-subtle' : 'bg-primary-subtle text-primary border-bottom border-primary-subtle' }}">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle {{ $customer->customer_type === 'special' ? 'bg-warning text-dark' : 'bg-primary text-white' }} fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                {{ substr($customer->name, 0, 1) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0 text-dark" id="customerModalLabel_{{ $customer->id }}">{{ $customer->name }}</h5>
                                <small class="text-muted font-monospace">Customer ID: #{{ $customer->id }} &bull; guard: frontend</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <!-- Profile & Tier Summary Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded bg-light-subtle text-center">
                                    <small class="text-muted text-uppercase d-block mb-1">Customer Tier</small>
                                    @if($customer->customer_type === 'special')
                                        <span class="badge bg-warning text-dark fs-6 py-1 px-2">
                                            <i class="bi bi-star-fill me-1"></i> Special Customer (VIP)
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info fs-6 py-1 px-2 border">
                                            Simple Customer
                                        </span>
                                        <small class="d-block text-muted mt-2">
                                            {{ 3 - $customer->orders_count > 0 ? (3 - $customer->orders_count) . ' orders until Special' : 'Eligible for Special' }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded bg-light-subtle text-center">
                                    <small class="text-muted text-uppercase d-block mb-1">Total Orders</small>
                                    <span class="fs-4 fw-bold font-monospace text-dark">{{ $customer->orders_count }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded bg-light-subtle text-center">
                                    <small class="text-muted text-uppercase d-block mb-1">Total Lifetime Spend</small>
                                    <span class="fs-4 fw-bold font-monospace text-success">${{ number_format($customer->total_spent, 2) }}</span>
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
                            <div class="col-md-6">
                                <strong class="text-muted">Address:</strong>
                                <span class="text-dark ms-1">{{ $customer->address ?? 'Not provided' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong class="text-muted">City / Region:</strong>
                                <span class="text-dark ms-1">{{ $customer->city ? $customer->city . ', ' . $customer->country : 'Not provided' }}</span>
                            </div>
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
                        @can('edit customers')
                            <form action="{{ route('admin.customers.toggle-special', $customer->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                @if($customer->customer_type === 'special')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-down-circle me-1"></i> Demote to Simple Customer
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-warning text-dark btn-sm fw-bold">
                                        <i class="bi bi-star-fill me-1"></i> Upgrade to Special Customer (VIP)
                                    </button>
                                @endif
                            </form>
                        @else
                            <div></div>
                        @endcan
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-app-layout>
