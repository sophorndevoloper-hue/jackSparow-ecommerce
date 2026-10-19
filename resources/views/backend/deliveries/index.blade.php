<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Sales &amp; Fulfillment</p>
                    <h1 class="h3 mb-1">Order Deliveries</h1>
                    <p class="text-muted mb-0">Track hardware dispatches, manage carrier tracking numbers, and fulfill customer shipments.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create deliveries', 'edit orders'])
                    <a href="{{ route('admin.deliveries.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Dispatch Shipment
                    </a>
                @endcanany
                @can('view shipping methods')
                    <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-signpost-split me-1"></i> Shipping Methods
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Deliveries KPI Summary Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Deliveries</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($totalDeliveries) }}</h3>
                            <small class="text-muted">Recorded shipments</small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-boxes fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Pending Dispatch</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning">{{ number_format($pendingDispatch) }}</h3>
                            <small class="text-muted">Packing &amp; preparing</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning p-3">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">In Transit</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ number_format($inTransit) }}</h3>
                            <small class="text-muted">On route with carriers</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-truck fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Delivered</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ number_format($deliveredCount) }}</h3>
                            <small class="text-muted">Completed fulfillment</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.deliveries.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by delivery #, tracking #, recipient, order #...">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Dispatch</option>
                        <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Packing &amp; Preparing</option>
                        <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                        <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <select name="carrier" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Carriers</option>
                        @foreach($carriers as $c)
                            <option value="{{ $c }}" {{ request('carrier') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                    @if(request()->anyFilled(['search', 'status', 'carrier']))
                        <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Deliveries Table -->
        <div class="panel p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Delivery Reference</th>
                            <th>Linked Order</th>
                            <th>Recipient &amp; Destination</th>
                            <th>Carrier &amp; Tracking</th>
                            <th>Fulfillment Status</th>
                            <th>Est. Delivery</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                                            <i class="bi {{ $delivery->statusIcon() }}"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.deliveries.show', $delivery->id) }}" class="fw-bold text-body-emphasis text-decoration-none font-monospace">
                                                {{ $delivery->delivery_number }}
                                            </a>
                                            <div class="text-muted small" style="font-size: 11px;">Created {{ $delivery->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($delivery->order)
                                        <div>
                                            <a href="{{ route('admin.orders.show', $delivery->order->id) }}" class="fw-semibold text-primary text-decoration-none font-monospace small">
                                                {{ $delivery->order->order_number }}
                                            </a>
                                            <div class="text-muted small">${{ number_format($delivery->order->total_amount, 2) }} &bull; {{ $delivery->order->items->count() }} items</div>
                                        </div>
                                    @else
                                        <span class="text-muted small">Order Removed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-body-emphasis small">{{ $delivery->recipient_name }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">
                                        <i class="bi bi-geo-alt me-1 text-muted"></i>
                                        @if(is_array($delivery->shipping_address))
                                            {{ $delivery->shipping_address['city'] ?? '' }}{{ !empty($delivery->shipping_address['state']) ? ', ' . $delivery->shipping_address['state'] : '' }}
                                        @else
                                            Destination on file
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-secondary-subtle text-body border small">
                                            <i class="bi bi-truck me-1"></i>{{ $delivery->carrier ?: 'Standard' }}
                                        </span>
                                    </div>
                                    @if($delivery->tracking_number)
                                        <div class="mt-1 font-monospace text-primary small" style="font-size: 11px;">
                                            <i class="bi bi-upc-scan me-1 text-muted"></i>{{ $delivery->tracking_number }}
                                        </div>
                                    @else
                                        <div class="text-muted small mt-1" style="font-size: 11px;">No tracking yet</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $delivery->statusBadgeClass() }} px-2 py-1">
                                        <i class="bi {{ $delivery->statusIcon() }} me-1"></i>{{ $delivery->statusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($delivery->estimated_delivery_date)
                                        <span class="text-body small font-monospace">{{ $delivery->estimated_delivery_date->format('M d, Y') }}</span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <!-- Inspect / Show View -->
                                        <a href="{{ route('admin.deliveries.show', $delivery->id) }}" class="btn-ghost" title="Track &amp; View Shipment Details">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Quick Status Update Modal Trigger -->
                                        @can('edit deliveries')
                                            <button type="button" class="btn-ghost text-primary" data-bs-toggle="modal" data-bs-target="#quickStatusModal_{{ $delivery->id }}" title="Update Delivery Status">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @endcan

                                        <!-- Delete Delivery -->
                                        @can('delete deliveries')
                                            <form action="{{ route('admin.deliveries.destroy', $delivery->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete delivery record {{ $delivery->delivery_number }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete Delivery Record">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <!-- Quick Status Update Modal -->
                            <div class="modal fade" id="quickStatusModal_{{ $delivery->id }}" tabindex="-1" aria-labelledby="statusModalLabel_{{ $delivery->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-sm">
                                    <div class="modal-content shadow-lg border-0">
                                        <form action="{{ route('admin.deliveries.status', $delivery->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header bg-primary-subtle text-primary border-bottom border-primary-subtle">
                                                <h6 class="modal-title fw-bold mb-0" id="statusModalLabel_{{ $delivery->id }}">
                                                    Update Status: {{ $delivery->delivery_number }}
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-3">
                                                <label class="form-label fw-semibold small">Fulfillment Stage</label>
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="pending" {{ $delivery->status === 'pending' ? 'selected' : '' }}>Pending Dispatch</option>
                                                    <option value="preparing" {{ $delivery->status === 'preparing' ? 'selected' : '' }}>Packing &amp; Preparing</option>
                                                    <option value="dispatched" {{ $delivery->status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                                    <option value="in_transit" {{ $delivery->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                                    <option value="out_for_delivery" {{ $delivery->status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                                    <option value="delivered" {{ $delivery->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                    <option value="cancelled" {{ $delivery->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                <small class="text-muted d-block mt-2" style="font-size: 11px;">
                                                    Changing status will automatically synchronize the associated customer order status.
                                                </small>
                                            </div>
                                            <div class="modal-footer bg-light-subtle d-flex justify-content-between p-2">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-truck fs-2 d-block mb-2"></i>
                                    No delivery shipments found matching the criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deliveries->hasPages())
                <div class="p-3 border-top">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

