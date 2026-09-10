<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Transfer Manifest</p>
                    <h1 class="h3 mb-1">Transfer: {{ $transfer->reference_number }}</h1>
                    <p class="text-muted mb-0">Dispatched: {{ $transfer->created_at->format('M d, Y \a\t H:i') }}</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create products', 'create warehouses', 'edit products'])
                    <a href="{{ route('admin.stock.transfers.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> New Transfer
                    </a>
                @endcanany
                <a href="{{ route('admin.stock.transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Transfers
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

        <div class="row g-3 mt-1">
            <!-- Route & Actions Card -->
            <div class="col-12 col-md-5">
                <div class="panel p-4 h-100">
                    <h5 class="mb-3 text-dark fw-bold">Transfer Route &amp; Status</h5>

                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted small">Origin / Source:</span>
                            <strong>{{ $transfer->fromWarehouse?->name }} ({{ $transfer->fromWarehouse?->code }})</strong>
                        </div>
                        <div class="text-center my-1 text-primary">
                            <i class="bi bi-arrow-down fs-5"></i>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small">Destination:</span>
                            <strong>{{ $transfer->toWarehouse?->name }} ({{ $transfer->toWarehouse?->code }})</strong>
                        </div>
                    </div>

                    <ul class="list-unstyled space-y-2 mb-3">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Status:</span>
                            @if($transfer->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="bi bi-check-circle-fill me-1"></i> Completed
                                </span>
                            @elseif($transfer->status === 'in_transit')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="bi bi-truck me-1"></i> In Transit
                                </span>
                            @elseif($transfer->status === 'pending')
                                <span class="badge bg-info-subtle text-info border border-info-subtle">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
                                </span>
                            @else
                                <span class="badge bg-secondary">Cancelled</span>
                            @endif
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Initiator / User:</span>
                            <span>{{ $transfer->user?->name ?? 'System' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Scheduled Date:</span>
                            <span>{{ $transfer->transfer_date ? $transfer->transfer_date->format('M d, Y') : 'N/A' }}</span>
                        </li>
                        @if($transfer->notes)
                            <li class="pt-2">
                                <span class="text-muted small d-block">Waybill / Notes:</span>
                                <p class="small text-dark mb-0 bg-light p-2 rounded border mt-1">{{ $transfer->notes }}</p>
                            </li>
                        @endif
                    </ul>

                    <!-- Status Update Actions if not finished -->
                    @canany(['create products', 'create warehouses', 'edit products'])
                        @if($transfer->status === 'pending')
                            <div class="d-flex gap-2 mt-3">
                                <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="in_transit">
                                    <button type="submit" class="btn btn-warning w-100 btn-sm">
                                        <i class="bi bi-truck me-1"></i> Mark In Transit
                                    </button>
                                </form>
                                <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success w-100 btn-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Complete
                                    </button>
                                </form>
                            </div>
                        @elseif($transfer->status === 'in_transit')
                            <div class="d-flex gap-2 mt-3">
                                <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-success w-100 btn-sm">
                                        <i class="bi bi-check2-circle me-1"></i> Receive Stock (Complete)
                                    </button>
                                </form>
                                <form action="{{ route('admin.stock.transfers.status', $transfer->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel transfer and return items to source warehouse?')">
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endcanany
                </div>
            </div>

            <!-- Items Table -->
            <div class="col-12 col-md-7">
                <div class="panel p-4 h-100">
                    <h5 class="mb-3 text-dark fw-bold">Transferred Components ({{ $transfer->items->count() }})</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hardware Part</th>
                                    <th>Category</th>
                                    <th class="text-end">Transfer Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->items as $item)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.products.edit', $item->product_id) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $item->product?->name }}
                                            </a>
                                            <div class="text-muted small font-monospace">SKU: {{ $item->product?->sku }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $item->product?->category?->name ?? '-' }}</span>
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-primary fs-6">
                                            {{ $item->quantity }} units
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total Units in Manifest:</th>
                                    <th class="text-end font-monospace fs-6">{{ $transfer->items->sum('quantity') }} units</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

