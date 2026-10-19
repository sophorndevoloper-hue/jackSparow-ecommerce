<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manage Sale &bull; Warehouse Operations</p>
                    <h1 class="h3 mb-1">Order Fulfillment &amp; RMA Tracking</h1>
                    <p class="text-muted mb-0">Scan and attach hardware serial numbers to order line items before confirming dispatch for RMA warranty tracking.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-truck me-1"></i> Deliveries
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-cart-check me-1"></i> All Orders
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

        <!-- Summary KPI Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <small class="text-muted text-uppercase fw-bold">Awaiting Fulfillment</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning-emphasis">{{ $totalAwaitingFulfillment }}</h3>
                    <small class="text-muted">Orders queued for packing</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <small class="text-muted text-uppercase fw-bold">Ready to Dispatch</small>
                    <h3 class="mb-0 mt-1 fw-bold text-success">{{ $readyToShipCount }}</h3>
                    <small class="text-muted">100% serials verified</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <small class="text-muted text-uppercase fw-bold">Shipped Today</small>
                    <h3 class="mb-0 mt-1 fw-bold text-primary">{{ $shippedTodayCount }}</h3>
                    <small class="text-muted">Fulfilled customer shipments</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <small class="text-muted text-uppercase fw-bold">RMA Serials Tracked</small>
                    <h3 class="mb-0 mt-1 fw-bold text-info">{{ $totalSerialsScannedToday }}</h3>
                    <small class="text-muted">Units active for warranty</small>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.fulfillment.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by Order #, Customer Name, Email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Awaiting Fulfillment (Active)</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped / Completed</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.fulfillment.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Fulfillment Queue Table -->
        <div class="panel p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Order Number</th>
                            <th>Customer Info</th>
                            <th>Date Ordered</th>
                            <th class="text-center">Line Items</th>
                            <th>Hardware Serial Progress</th>
                            <th class="text-center">Order Status</th>
                            <th class="text-end pe-3">Workstation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $requiredSerials = $order->totalSerialTrackingRequiredCount();
                                $attachedSerials = $order->totalSerialsAttachedCount();
                                $isReady = $order->isReadyForShipment();
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.fulfillment.show', $order->id) }}" class="fw-bold text-decoration-none font-monospace">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                                    <small class="text-muted">{{ $order->customer_phone ?: $order->customer_email }}</small>
                                </td>
                                <td>
                                    <span class="small">{{ $order->created_at->format('M d, Y H:i') }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $order->items->count() }} line(s)
                                    </span>
                                </td>
                                <td>
                                    @if($requiredSerials > 0)
                                        <div class="d-flex align-items-center gap-2" style="min-width: 180px;">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                @php $pct = min(100, round(($attachedSerials / $requiredSerials) * 100)); @endphp
                                                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span class="font-monospace small fw-bold {{ $attachedSerials >= $requiredSerials ? 'text-success' : 'text-warning-emphasis' }}">
                                                {{ $attachedSerials }}/{{ $requiredSerials }}
                                            </span>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Serials scanned for RMA</small>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 11px;">No serial tracking required</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $order->status === 'shipped' ? 'bg-success' : ($isReady ? 'bg-info text-dark' : 'bg-warning-subtle text-warning-emphasis border') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.fulfillment.show', $order->id) }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upc-scan me-1"></i> Open Workstation
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                                    <h5>No orders in fulfillment queue</h5>
                                    <p class="mb-0">All current orders are either shipped or no orders match your search filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($orders->hasPages())
                <div class="p-3 border-top">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

