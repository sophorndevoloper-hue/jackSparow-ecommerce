<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-building" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Warehouse Facility</p>
                    <h1 class="h3 mb-1">{{ $warehouse->name }} ({{ $warehouse->code }})</h1>
                    <p class="text-muted mb-0">Location: {{ $warehouse->address ? $warehouse->address . ', ' : '' }}{{ $warehouse->city ?? 'Unspecified' }}</p>
                </div>
            </div>
            <div class="heading-actions d-flex flex-wrap align-items-center gap-2">
                @canany(['create products', 'create warehouses', 'edit products'])
                    <a href="{{ route('admin.stock.transfers.create', ['from_warehouse_id' => $warehouse->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left-right me-1"></i> Transfer Stock
                    </a>
                @endcanany
                @canany(['create products', 'edit products'])
                    <a href="{{ route('admin.stock.adjustments.create', ['warehouse_id' => $warehouse->id]) }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-sliders me-1"></i> Stock Adjustment
                    </a>
                @endcanany
                @canany(['edit warehouses', 'edit products'])
                    <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit Facility
                    </a>
                @endcanany
            </div>
        </div>

        <!-- Metric KPI row -->
        <div class="row g-3 mt-1">
            <div class="col-12 col-md-4">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-boxes fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Stored Units</span>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($totalUnits) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Facility Asset Valuation</span>
                        <h4 class="mb-0 fw-bold text-dark">${{ number_format($totalValuation, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-cpu fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Distinct Hardware SKUs</span>
                        <h4 class="mb-0 fw-bold text-dark">{{ $warehouse->products->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stored Inventory List -->
        <div class="panel mt-4">
            <div class="panel-header p-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Inventory Stored at {{ $warehouse->name }}</h5>
                @canany(['view products', 'view stock', 'view warehouses'])
                    <a href="{{ route('admin.stock.index', ['warehouse_id' => $warehouse->id]) }}" class="btn btn-outline-secondary btn-sm">
                        Open in Stock Manager &rarr;
                    </a>
                @endcanany
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Hardware Product</th>
                            <th>Category</th>
                            <th>Unit Price</th>
                            <th>Warehouse Quantity</th>
                            <th>Line Value</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouse->products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded bg-light border overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            @if($product->primary_image_url)
                                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-cpu fs-5 text-primary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            @can('edit products')
                                                <a href="{{ route('admin.products.edit', $product->id) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $product->name }}
                                                </a>
                                            @else
                                                <span class="fw-bold text-dark">{{ $product->name }}</span>
                                            @endcan
                                            <div class="text-muted small font-monospace">SKU: {{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                                </td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->pivot->quantity <= 0)
                                        <span class="badge bg-danger">0 (Out of Stock)</span>
                                    @elseif($product->pivot->quantity <= $product->low_stock_threshold)
                                        <span class="badge bg-warning text-dark">{{ $product->pivot->quantity }} (Low)</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                                            {{ $product->pivot->quantity }} Units
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong>${{ number_format($product->pivot->quantity * $product->price, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    @can('edit products')
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;">
                                            Edit
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted display-4 d-block mb-3"></i>
                                    <h6 class="text-dark fw-bold">No inventory stored in this warehouse</h6>
                                    <p class="text-muted small mb-0">Use Stock Adjustment or Stock Transfer to allocate products to this facility.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

