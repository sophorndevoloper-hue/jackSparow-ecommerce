<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management</p>
                    <h1 class="h3 mb-1">Hardware Suppliers &amp; Vendors</h1>
                    <p class="text-muted mb-0">Manage computer components suppliers, distributors, and procurement partners.</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('create suppliers')
                    <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add Supplier
                    </a>
                @endcan
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i> Customers
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <div class="panel p-3 mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <a href="{{ route('admin.suppliers.index') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All ({{ $activeCount + $inactiveCount }})
                        </a>
                        <a href="{{ route('admin.suppliers.index', ['status' => 'active']) }}" class="btn {{ request('status') === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Active ({{ $activeCount }})
                        </a>
                        <a href="{{ route('admin.suppliers.index', ['status' => 'inactive']) }}" class="btn {{ request('status') === 'inactive' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Inactive ({{ $inactiveCount }})
                        </a>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <form action="{{ route('admin.suppliers.index') }}" method="GET" class="d-flex gap-2">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search supplier by company, contact, categories...">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm px-3">Search</button>
                    </form>
                </div>

                <div class="col-12 col-md-2 text-end">
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="panel p-3">
            <x-datatable id="suppliersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="suppliersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-body-secondary border text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            <i class="bi bi-building fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $supplier->company_name }}</div>
                                            @if($supplier->address)
                                                <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;"><i class="bi bi-geo-alt me-1"></i>{{ $supplier->address }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-body-emphasis fw-medium">{{ $supplier->contact_name ?? '—' }}</span>
                                </td>
                                <td>
                                    @if($supplier->email)
                                        <div>
                                            <a href="mailto:{{ $supplier->email }}" class="text-body-secondary text-decoration-none small">
                                                <i class="bi bi-envelope me-1 text-muted"></i>{{ $supplier->email }}
                                            </a>
                                        </div>
                                    @endif
                                    @if($supplier->phone)
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <i class="bi bi-telephone me-1 text-muted"></i>{{ $supplier->phone }}
                                        </div>
                                    @endif
                                    @if(!$supplier->email && !$supplier->phone)
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->supply_categories)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(explode(',', $supplier->supply_categories) as $cat)
                                                <span class="badge bg-secondary-subtle text-body-secondary border-0" style="font-size: 11px;">
                                                    {{ trim($cat) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">All Components</span>
                                    @endif
                                </td>
                                <td>
                                    @if($supplier->status === 'active')
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @can('edit suppliers')
                                            <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn-ghost text-primary" title="Edit Supplier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan

                                        @can('delete suppliers')
                                            <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove supplier \'{{ $supplier->company_name }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete Supplier">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-truck fs-2 d-block mb-2"></i>
                                    No hardware suppliers found.
                                    @can('manage suppliers')
                                        <div class="mt-2">
                                            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle me-1"></i> Add First Supplier
                                            </a>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            @if($suppliers->hasPages())
                <div class="p-3 border-top">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

