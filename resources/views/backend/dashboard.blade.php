<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cpu-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">JackSparow TECH</p>
                    <h1 class="h3 mb-1">Hardware Admin Dashboard</h1>
                    <p class="text-muted mb-0">Real-time inventory monitor, sales overview, component orders, and low-stock alerts.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1" aria-hidden="true"></i> Add Product
                </a>
                <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i> Live Store
                </a>
            </div>
        </div>

        <!-- Global Flash Alerts -->
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

        <!-- Metric KPI Cards -->
        <section class="row g-3 mt-1" aria-label="Dashboard metrics">
            <!-- Revenue -->
            <div class="col-12 col-sm-6 col-xl-3">
                <article class="metric-card metric-primary">
                    <div class="metric-top">
                        <span class="metric-label">Paid Revenue</span>
                        <span class="metric-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></span>
                    </div>
                    <div class="metric-value">${{ number_format($totalRevenue, 2) }}</div>
                    <div class="metric-meta">
                        <span class="text-success"><i class="bi bi-arrow-up-right"></i> Active</span>
                        <span>fulfilled orders</span>
                    </div>
                </article>
            </div>

            <!-- Total Orders -->
            <div class="col-12 col-sm-6 col-xl-3">
                <article class="metric-card metric-success">
                    <div class="metric-top">
                        <span class="metric-label">Total Orders</span>
                        <span class="metric-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
                    </div>
                    <div class="metric-value">{{ $totalOrders }}</div>
                    <div class="metric-meta">
                        <span class="text-primary"><a href="{{ route('admin.orders.index') }}" class="text-decoration-none">View all orders &rarr;</a></span>
                    </div>
                </article>
            </div>

            <!-- Total Products -->
            <div class="col-12 col-sm-6 col-xl-3">
                <article class="metric-card metric-warning">
                    <div class="metric-top">
                        <span class="metric-label">Hardware Products</span>
                        <span class="metric-icon"><i class="bi bi-cpu" aria-hidden="true"></i></span>
                    </div>
                    <div class="metric-value">{{ $totalProducts }}</div>
                    <div class="metric-meta">
                        <span class="text-muted">In stock catalog</span>
                    </div>
                </article>
            </div>

            <!-- Low Stock Alerts -->
            <div class="col-12 col-sm-6 col-xl-3">
                <article class="metric-card {{ ($lowStockCount + $outOfStockCount) > 0 ? 'metric-danger' : 'metric-success' }}">
                    <div class="metric-top">
                        <span class="metric-label">Stock Warnings</span>
                        <span class="metric-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                    </div>
                    <div class="metric-value">{{ $lowStockCount + $outOfStockCount }}</div>
                    <div class="metric-meta">
                        @if($outOfStockCount > 0)
                            <span class="text-danger fw-bold">{{ $outOfStockCount }} out of stock</span>
                        @elseif($lowStockCount > 0)
                            <span class="text-warning fw-bold">{{ $lowStockCount }} low inventory</span>
                        @else
                            <span class="text-success">Healthy stock levels</span>
                        @endif
                    </div>
                </article>
            </div>
        </section>

        <!-- Main Dashboard Content Grid -->
        <div class="row g-3 mt-2">
            <!-- Left: Recent Orders Table -->
            <div class="col-12 col-xl-8">
                <div class="panel">
                    <div class="panel-header d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h5 mb-1 section-title">
                                <i class="bi bi-clock-history me-1" aria-hidden="true"></i>
                                <span>Recent Orders</span>
                            </h2>
                            <p class="text-muted mb-0">Latest customer hardware purchases and fulfillment status.</p>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm">View All Orders</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-bold text-primary font-monospace">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="d-block fw-semibold">{{ $order->customer_name }}</span>
                                            <small class="text-muted">{{ $order->customer_email }}</small>
                                        </td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td class="fw-bold">${{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @if($order->payment_status === 'paid')
                                                <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                                    <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Paid
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis border-0 px-2 py-1">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge border-0 px-2 py-1 
                                                @if($order->status === 'delivered') bg-success-subtle text-success
                                                @elseif($order->status === 'shipped') bg-info-subtle text-info-emphasis
                                                @elseif($order->status === 'processing') bg-primary-subtle text-primary
                                                @elseif($order->status === 'cancelled') bg-danger-subtle text-danger
                                                @else bg-secondary-subtle text-secondary @endif
                                            ">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn-ghost text-primary" title="Manage Order">
                                                    <i class="bi bi-receipt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No customer orders placed yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Stock Alerts & Quick Management -->
            <div class="col-12 col-xl-4 space-y-3">
                <!-- Critical Inventory Alert Panel -->
                <div class="panel mb-3">
                    <div class="panel-header d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h5 mb-1 section-title text-danger">
                                <i class="bi bi-shield-exclamation me-1" aria-hidden="true"></i>
                                <span>Low Stock Inventory</span>
                            </h2>
                            <p class="text-muted mb-0">Components requiring warehouse restock.</p>
                        </div>
                        <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="btn btn-light btn-sm">Filter</a>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse($lowStockProducts as $prod)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2.5">
                                <div>
                                    <strong class="d-block text-truncate" style="max-width: 200px;">{{ $prod->name }}</strong>
                                    <small class="text-muted font-monospace">{{ $prod->sku }} • {{ $prod->category->name }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $prod->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} mb-1">
                                        {{ $prod->stock_quantity }} in stock
                                    </span>
                                    <div>
                                        <a href="{{ route('admin.products.edit', $prod->id) }}" class="small text-primary text-decoration-none">
                                            Edit Stock &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="bi bi-check-circle text-success fs-4 d-block mb-1"></i>
                                All computer parts are well stocked!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Hardware Actions -->
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="h5 mb-1 section-title">
                            <i class="bi bi-lightning-charge me-1" aria-hidden="true"></i>
                            <span>Quick Actions</span>
                        </h2>
                    </div>

                    <div class="p-3 d-grid gap-2">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between text-start">
                            <span><i class="bi bi-plus-circle me-2"></i> Add Computer Part</span>
                            <i class="bi bi-chevron-right small"></i>
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-between text-start">
                            <span><i class="bi bi-folder-plus me-2"></i> Add Category</span>
                            <i class="bi bi-chevron-right small"></i>
                        </a>
                        <a href="{{ route('admin.brands.create') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-between text-start">
                            <span><i class="bi bi-patch-plus me-2"></i> Add Brand</span>
                            <i class="bi bi-chevron-right small"></i>
                        </a>
                        <a href="{{ route('shop') }}" target="_blank" class="btn btn-outline-success d-flex align-items-center justify-content-between text-start">
                            <span><i class="bi bi-shop me-2"></i> Open Customer Storefront</span>
                            <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

