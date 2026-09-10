<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Sales & Fulfillment</p>
                    <h1 class="h3 mb-1">Customer Hardware Orders</h1>
                    <p class="text-muted mb-0">Track customer purchases, update order dispatch status, and manage payments.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Bar -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search order number or customer name/email...">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Fulfillment Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Payment Statuses</option>
                        <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Payment Pending</option>
                        <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>

                <div class="col-12 col-md-1">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <div class="panel p-3">
            <x-datatable id="ordersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="ordersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="fw-semibold text-primary font-monospace text-decoration-none">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-body-emphasis">{{ $order->customer_name }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">{{ $order->customer_email }}@if($order->customer_phone) • {{ $order->customer_phone }}@endif</div>
                                </td>
                                <td>
                                    <div class="text-body-emphasis small">{{ $order->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">{{ $order->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-body-emphasis">${{ number_format($order->total_amount, 2) }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">{{ $order->items->count() }} items</div>
                                </td>
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
                                <td colspan="7" class="text-center py-5 text-muted">
                                    No hardware orders found matching filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

