<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Inventory Control</p>
                    <h1 class="h3 mb-1">Stock Adjustments</h1>
                    <p class="text-muted mb-0">Audit history of physical count corrections, damage write-offs, and stock entries.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create products', 'edit products'])
                    <a href="{{ route('admin.stock.adjustments.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> New Adjustment
                    </a>
                @endcanany
                @canany(['view products', 'view stock'])
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

        <!-- Filter Bar -->
        <div class="panel p-3 mt-3">
            <form action="{{ route('admin.stock.adjustments.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)request('warehouse_id') === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Adjustment Types</option>
                        <option value="addition" {{ request('type') === 'addition' ? 'selected' : '' }}>Stock Addition (+)</option>
                        <option value="subtraction" {{ request('type') === 'subtraction' ? 'selected' : '' }}>Stock Subtraction (-)</option>
                        <option value="correction" {{ request('type') === 'correction' ? 'selected' : '' }}>Count Correction</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="reason" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Reasons</option>
                        <option value="physical_count" {{ request('reason') === 'physical_count' ? 'selected' : '' }}>Physical Audit Count</option>
                        <option value="received" {{ request('reason') === 'received' ? 'selected' : '' }}>Stock Received</option>
                        <option value="damaged" {{ request('reason') === 'damaged' ? 'selected' : '' }}>Damaged / Broken</option>
                        <option value="loss" {{ request('reason') === 'loss' ? 'selected' : '' }}>Lost / Stolen</option>
                        <option value="other" {{ request('reason') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                        @if(request()->hasAny(['warehouse_id', 'type', 'reason']))
                            <a href="{{ route('admin.stock.adjustments.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                                <i class="bi bi-x"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Adjustments Table -->
        <div class="panel p-3 mt-4">
            <x-datatable id="adjustmentsTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="adjustmentsTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($adjustments as $adj)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.stock.adjustments.show', $adj->id) }}" class="fw-semibold font-monospace text-primary text-decoration-none">
                                        {{ $adj->reference_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="text-body-emphasis fw-medium">{{ $adj->warehouse?->name ?? 'N/A' }}</span>
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">{{ $adj->warehouse?->code }}</div>
                                </td>
                                <td>
                                    @if($adj->type === 'addition')
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-plus-lg me-1"></i>Addition
                                        </span>
                                    @elseif($adj->type === 'subtraction')
                                        <span class="badge bg-danger-subtle text-danger border-0 px-2 py-1">
                                            <i class="bi bi-dash-lg me-1"></i>Subtraction
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info-emphasis border-0 px-2 py-1">
                                            <i class="bi bi-arrow-repeat me-1"></i>Correction
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-body-emphasis text-capitalize small">{{ str_replace('_', ' ', $adj->reason) }}</span>
                                    @if($adj->notes)
                                        <div class="text-muted small text-truncate" style="max-width: 180px; font-size: 11px;" title="{{ $adj->notes }}">{{ $adj->notes }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1">
                                        {{ $adj->items->count() }} Product(s)
                                    </span>
                                </td>
                                <td>
                                    <div class="text-body-emphasis small">{{ $adj->user?->name ?? 'System' }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">{{ $adj->created_at->format('M d, Y H:i') }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('admin.stock.adjustments.show', $adj->id) }}" class="btn-ghost" title="View Adjustment Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-sliders text-muted display-4 d-block mb-3"></i>
                                    <h6 class="text-dark fw-bold">No stock adjustments found</h6>
                                    <p class="text-muted small mb-0">Record adjustments when physical stock differs from the system inventory.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            @if($adjustments->hasPages())
                <div class="p-3 border-top">
                    {{ $adjustments->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

