<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Inventory &amp; Warehousing</p>
                    <h1 class="h3 mb-1">Stock Management</h1>
                    <p class="text-muted mb-0">Monitor hardware inventory levels, track low stock warnings, and quickly adjust warehouse quantities.</p>
                </div>
            </div>
            <div class="heading-actions d-flex flex-wrap align-items-center gap-2">
                @canany(['create products', 'create warehouses', 'edit products'])
                    <a href="{{ route('admin.stock.transfers.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-arrow-left-right me-1"></i> Transfer Stock
                    </a>
                @endcanany
                @canany(['create products', 'edit products'])
                    <a href="{{ route('admin.stock.adjustments.create') }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-sliders me-1"></i> Stock Adjustment
                    </a>
                @endcanany
                @canany(['view warehouses', 'view products'])
                    <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-buildings me-1"></i> Warehouses
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

        <!-- KPI Metric Cards -->
        <div class="row g-3 mt-1">
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.stock.index', ['filter' => 'all'] + request()->except('filter', 'page')) }}" class="text-decoration-none">
                    <div class="panel p-3 h-100 d-flex align-items-center gap-3 metric-tile {{ $filter === 'all' ? 'border-primary shadow-sm' : '' }}">
                        <div class="rounded-3 bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Warehouse Units</span>
                            <h4 class="mb-0 fw-bold text-body-emphasis">{{ number_format($totalUnits) }}</h4>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3 metric-tile">
                    <div class="rounded-3 bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Inventory Asset Value</span>
                        <h4 class="mb-0 fw-bold text-body-emphasis">${{ number_format($totalValuation, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.stock.index', ['filter' => 'low'] + request()->except('filter', 'page')) }}" class="text-decoration-none">
                    <div class="panel p-3 h-100 d-flex align-items-center gap-3 metric-tile {{ $filter === 'low' ? 'border-warning shadow-sm' : '' }}">
                        <div class="rounded-3 bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Low Stock Warnings</span>
                            <h4 class="mb-0 fw-bold text-body-emphasis">
                                {{ $lowStockCount }}
                                @if($lowStockCount > 0)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 11px;">Action Needed</span>
                                @endif
                            </h4>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('admin.stock.index', ['filter' => 'out'] + request()->except('filter', 'page')) }}" class="text-decoration-none">
                    <div class="panel p-3 h-100 d-flex align-items-center gap-3 metric-tile {{ $filter === 'out' ? 'border-danger shadow-sm' : '' }}">
                        <div class="rounded-3 bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            <i class="bi bi-x-octagon-fill fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Out of Stock Items</span>
                            <h4 class="mb-0 fw-bold text-body-emphasis">
                                {{ $outOfStockCount }}
                                @if($outOfStockCount > 0)
                                    <span class="badge bg-danger ms-1" style="font-size: 11px;">Critical</span>
                                @endif
                            </h4>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filter Tabs & Controls -->
        <div class="panel p-3 mt-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom pb-3 mb-3">
                <!-- Status Tabs -->
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('admin.stock.index', ['filter' => 'all'] + request()->except('filter', 'page')) }}">
                            All Hardware ({{ $totalProducts }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ $filter === 'low' ? 'active bg-warning text-dark' : 'text-warning' }}" href="{{ route('admin.stock.index', ['filter' => 'low'] + request()->except('filter', 'page')) }}">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock ({{ $lowStockCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ $filter === 'out' ? 'active bg-danger' : 'text-danger' }}" href="{{ route('admin.stock.index', ['filter' => 'out'] + request()->except('filter', 'page')) }}">
                            <i class="bi bi-x-octagon-fill me-1"></i> Out of Stock ({{ $outOfStockCount }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-sm {{ $filter === 'in' ? 'active' : '' }}" href="{{ route('admin.stock.index', ['filter' => 'in'] + request()->except('filter', 'page')) }}">
                            In Stock
                        </a>
                    </li>
                </ul>

                <!-- Reset Filters -->
                @if(request()->hasAny(['search', 'warehouse_id', 'category_id', 'make_id', 'brand_id']) || $filter !== 'all')
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                    </a>
                @endif
            </div>

            <!-- Search and Select Filters Form -->
            <form action="{{ route('admin.stock.index') }}" method="GET" class="row g-2 align-items-center">
                <input type="hidden" name="filter" value="{{ $filter }}">

                <div class="col-12 col-xl-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name or SKU...">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ (string)request('warehouse_id') === (string)$wh->id ? 'selected' : '' }}>
                                {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)request('category_id') === (string)$cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="make_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Makes</option>
                        @foreach($makes as $mk)
                            <option value="{{ $mk->id }}" {{ (string)request('make_id') === (string)$mk->id ? 'selected' : '' }}>
                                {{ $mk->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <select name="brand_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ (string)request('brand_id') === (string)$brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-xl-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
            </form>
        </div>

        <!-- Stock Management Table -->
        <div class="panel p-3 mt-4">
            <x-datatable id="stockOverviewTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="stockOverviewTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-body-secondary border overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            @if($product->primary_image_url)
                                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-cpu fs-5 text-primary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                                {{ $product->name }}
                                            </a>
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">SKU: {{ $product->sku }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <i class="bi bi-clock-history me-1"></i>Updated {{ $product->updated_at?->diffForHumans() ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1">{{ $product->category->name }}</span>
                                    <div class="small text-muted mt-1 d-flex flex-wrap align-items-center gap-1" style="font-size: 11px;">
                                        @if($product->make)
                                            <span class="badge bg-body-secondary text-body-secondary border-0 px-1 py-0" title="Make">
                                                {{ $product->make->name }}
                                            </span>
                                        @endif
                                        <span>{{ $product->brand?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                <td>
                                    @if($product->stock_quantity <= 0)
                                        <span class="badge bg-danger-subtle text-danger border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Out of Stock (0)
                                        </span>
                                    @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border-0 px-2 py-1">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Low Stock ({{ $product->stock_quantity }})
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>In Stock ({{ $product->stock_quantity }})
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="fw-bold text-body-emphasis">${{ number_format($product->price, 2) }}</div>
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">Val: ${{ number_format($product->stock_quantity * $product->price, 2) }}</div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="fw-bold font-monospace text-body-emphasis fs-6">{{ number_format($product->stock_quantity) }}</span>
                                            <span class="text-muted small">units</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1" style="font-size: 11.5px;">
                                            <span class="text-muted"><i class="bi bi-bell me-1 text-warning"></i>Alert:</span>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border-0 font-monospace px-1.5 py-0.5">
                                                &le; {{ number_format($product->low_stock_threshold) }} units
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @canany(['view products', 'edit products'])
                                            <a href="{{ route('admin.serial-numbers.index', ['product_id' => $product->id]) }}" 
                                               class="btn-ghost" 
                                               title="View Serial Product List (Registry &amp; Warranties)">
                                                <i class="bi bi-upc-scan"></i>
                                            </a>
                                        @endcanany

                                        @can('edit products')
                                            <a href="{{ route('admin.products.edit', $product->id) }}" 
                                               class="btn-ghost" 
                                               title="Edit Product">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted display-4 d-block mb-3"></i>
                                    <h6 class="text-dark fw-bold">No hardware parts found</h6>
                                    <p class="text-muted small mb-0">Try clearing filters or adding new inventory products.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            @if($products->hasPages())
                <div class="p-3 border-top">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>

