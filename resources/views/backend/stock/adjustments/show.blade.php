<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Adjustment Audit Record</p>
                    <h1 class="h3 mb-1">Reference: {{ $adjustment->reference_number }}</h1>
                    <p class="text-muted mb-0">Recorded on {{ $adjustment->created_at->format('M d, Y \a\t H:i:s') }}</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.stock.adjustments.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> New Adjustment
                </a>
                <a href="{{ route('admin.stock.adjustments.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to History
                </a>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <!-- Metadata Info -->
            <div class="col-12 col-md-4">
                <div class="panel p-4 h-100">
                    <h5 class="mb-3 text-dark fw-bold">Adjustment Overview</h5>

                    <ul class="list-unstyled mb-0 space-y-2">
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Facility / Depot:</span>
                            <strong>{{ $adjustment->warehouse?->name ?? 'N/A' }} ({{ $adjustment->warehouse?->code }})</strong>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Type:</span>
                            @if($adjustment->type === 'addition')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Stock Addition (+)</span>
                            @elseif($adjustment->type === 'subtraction')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Stock Subtraction (-)</span>
                            @else
                                <span class="badge bg-info-subtle text-info border border-info-subtle">Count Correction (=)</span>
                            @endif
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Reason:</span>
                            <span class="text-capitalize fw-medium">{{ str_replace('_', ' ', $adjustment->reason) }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Staff Operator:</span>
                            <span>{{ $adjustment->user?->name ?? 'System' }}</span>
                        </li>
                        @if($adjustment->notes)
                            <li class="pt-2">
                                <span class="text-muted small d-block">Notes / Memo:</span>
                                <p class="small text-dark mb-0 bg-light p-2 rounded border mt-1">{{ $adjustment->notes }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="col-12 col-md-8">
                <div class="panel p-4 h-100">
                    <h5 class="mb-3 text-dark fw-bold">Adjusted Components ({{ $adjustment->items->count() }})</h5>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Hardware Part</th>
                                    <th>Category</th>
                                    <th class="text-center">Previous Stock</th>
                                    <th class="text-center">Adjustment</th>
                                    <th class="text-center">New Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adjustment->items as $item)
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
                                        <td class="text-center font-monospace text-muted">{{ $item->old_quantity }}</td>
                                        <td class="text-center font-monospace fw-bold">
                                            @if($item->adjusted_quantity > 0)
                                                <span class="text-success">+{{ $item->adjusted_quantity }}</span>
                                            @elseif($item->adjusted_quantity < 0)
                                                <span class="text-danger">{{ $item->adjusted_quantity }}</span>
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center font-monospace fw-bold text-dark bg-light-subtle">{{ $item->new_quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

