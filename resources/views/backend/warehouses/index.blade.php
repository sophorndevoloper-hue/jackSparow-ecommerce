<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Logistics &amp; Fulfillment</p>
                    <h1 class="h3 mb-1">Storage Warehouses</h1>
                    <p class="text-muted mb-0">Manage hardware storage facilities, distribution hubs, and RMA depot locations.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create warehouses', 'create products'])
                    <a href="{{ route('admin.warehouses.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Warehouse
                    </a>
                @endcanany
                @canany(['view products', 'view stock', 'view warehouses'])
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-seam me-1"></i> Stock Overview
                    </a>
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

        <!-- Warehouses Table -->
        <div class="panel mt-3 p-3">
            <x-datatable id="warehousesTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="warehousesTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                            @forelse($warehouses as $wh)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                                 <i class="bi bi-buildings fs-5"></i>
                                            </div>
                                            <div>
                                                @canany(['view warehouses', 'view products'])
                                                    <a href="{{ route('admin.warehouses.show', $wh->id) }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                                        {{ $wh->name }}
                                                    </a>
                                                @else
                                                    <span class="fw-semibold text-body-emphasis">{{ $wh->name }}</span>
                                                @endcanany
                                                @if($wh->is_default)
                                                    <span class="badge bg-primary-subtle text-primary border-0 ms-1" style="font-size: 10px;">Default</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $wh->code }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-body">{{ $wh->city ?? 'N/A' }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 200px; font-size: 11px;">{{ $wh->address ?? '-' }}</div>
                                    </td>
                                    <td>
                                        @if($wh->phone)
                                            <div class="small text-body"><i class="bi bi-telephone text-muted me-1"></i> {{ $wh->phone }}</div>
                                        @endif
                                        @if($wh->email)
                                            <div class="small text-muted" style="font-size: 11px;"><i class="bi bi-envelope text-muted me-1"></i> {{ $wh->email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-body-emphasis">{{ $wh->total_stock }} Units</div>
                                        <div class="text-muted small" style="font-size: 11px;">{{ $wh->products_count }} Parts</div>
                                    </td>
                                    <td>
                                        @if($wh->is_active)
                                            <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                                <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            @canany(['view warehouses', 'view products'])
                                                <a href="{{ route('admin.warehouses.show', $wh->id) }}" class="btn-ghost" title="View Inventory">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcanany
                                            @canany(['edit warehouses', 'edit products'])
                                                <a href="{{ route('admin.warehouses.edit', $wh->id) }}" class="btn-ghost text-primary" title="Edit Warehouse">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcanany
                                            @canany(['delete warehouses', 'delete products'])
                                                @if(!$wh->is_default)
                                                    <form action="{{ route('admin.warehouses.destroy', $wh->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this warehouse facility?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-ghost text-danger" title="Delete Warehouse">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcanany
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bi bi-buildings text-muted display-4 d-block mb-3"></i>
                                        <h6 class="text-body-emphasis fw-bold">No warehouses created yet</h6>
                                        @canany(['create warehouses', 'create products'])
                                            <p class="text-muted small mb-0">Click "Add Warehouse" above to create your first facility.</p>
                                        @endcanany
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
            </x-datatable>

            @if($warehouses->hasPages())
                <div class="p-3 border-top">
                    {{ $warehouses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

