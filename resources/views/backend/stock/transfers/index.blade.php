<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Logistics &amp; Movement</p>
                    <h1 class="h3 mb-1">Stock Transfers</h1>
                    <p class="text-muted mb-0">Manage inter-warehouse inventory dispatch, transit status, and destination receipt.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create products', 'create warehouses', 'edit products'])
                    <a href="{{ route('admin.stock.transfers.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> New Transfer
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

        <!-- Filter Bar -->
        <div class="panel p-3 mt-3">
            <form action="{{ route('admin.stock.transfers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <select name="from_warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">From: All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)request('from_warehouse_id') === (string)$wh->id ? 'selected' : '' }}>
                                From: {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="to_warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">To: All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)request('to_warehouse_id') === (string)$wh->id ? 'selected' : '' }}>
                                To: {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                        @if(request()->hasAny(['from_warehouse_id', 'to_warehouse_id', 'status']))
                            <a href="{{ route('admin.stock.transfers.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                                <i class="bi bi-x"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Transfers Table -->
        <div class="panel p-3 mt-4">
            <x-datatable id="transfersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="transfersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($transfers as $trf)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.stock.transfers.show', $trf->id) }}" class="fw-semibold font-monospace text-primary text-decoration-none">
                                        {{ $trf->reference_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-body-emphasis fw-medium">{{ $trf->fromWarehouse?->name ?? 'N/A' }}</span>
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">{{ $trf->fromWarehouse?->code }}</div>
                                </td>
                                <td>
                                    <span class="text-body-emphasis fw-medium">{{ $trf->toWarehouse?->name ?? 'N/A' }}</span>
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">{{ $trf->toWarehouse?->code }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1">
                                        {{ $trf->items->count() }} Part(s) ({{ $trf->items->sum('quantity') }} units)
                                    </span>
                                </td>
                                <td>
                                    @if($trf->status === 'completed')
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Completed
                                        </span>
                                    @elseif($trf->status === 'in_transit')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border-0 px-2 py-1">
                                            <i class="bi bi-truck me-1"></i>In Transit
                                        </span>
                                    @elseif($trf->status === 'pending')
                                        <span class="badge bg-info-subtle text-info-emphasis border-0 px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i>Pending
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-body-emphasis small">{{ $trf->user?->name ?? 'System' }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">{{ $trf->created_at->format('M d, Y') }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('admin.stock.transfers.show', $trf->id) }}" class="btn-ghost" title="View Transfer Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-arrow-left-right text-muted display-4 d-block mb-3"></i>
                                    <h6 class="text-dark fw-bold">No stock transfers found</h6>
                                    <p class="text-muted small mb-0">Use transfers to shift hardware components between different warehouse facilities.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            @if($transfers->hasPages())
                <div class="p-3 border-top">
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

