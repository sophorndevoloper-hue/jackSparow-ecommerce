<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Order Details</p>
                    <h1 class="h3 mb-1">Order #{{ $order->order_number }}</h1>
                    <p class="text-muted mb-0">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Orders
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-3 mt-1">
            <!-- Left: Order Items & Customer Notes -->
            <div class="col-12 col-lg-8 space-y-3">
                <!-- Items Table -->
                <div class="panel p-0 overflow-hidden mb-3">
                    <div class="panel-header p-3 border-bottom">
                        <h5 class="mb-0"><i class="bi bi-cpu me-1"></i> Ordered Computer Components ({{ $order->items->count() }})</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Component</th>
                                    <th>Unit Price</th>
                                    <th>Qty</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <strong class="d-block text-dark">{{ $item->product_name }}</strong>
                                            <small class="text-muted font-monospace">SKU: {{ $item->product_sku }}</small>
                                            @if(!empty($item->specifications_snapshot))
                                                <div class="mt-1 d-flex flex-wrap gap-1">
                                                    @foreach($item->specifications_snapshot as $k => $v)
                                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 10px;">
                                                            {{ $k }}: {{ $v }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>${{ number_format($item->unit_price, 2) }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $item->quantity }}</span></td>
                                        <td class="text-end fw-bold">${{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Cost Totals -->
                    <div class="p-3 bg-light border-top">
                        <div class="row justify-content-end">
                            <div class="col-12 col-md-5 space-y-1 text-end small">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Parts Subtotal:</span>
                                    <strong>${{ number_format($order->subtotal, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Tax (8%):</span>
                                    <strong>${{ number_format($order->tax_amount, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Shipping Fee:</span>
                                    <strong>${{ number_format($order->shipping_fee, 2) }}</strong>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between fs-6 fw-bold text-dark">
                                    <span>Grand Total:</span>
                                    <span class="text-primary">${{ number_format($order->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($order->customer_notes)
                    <div class="panel p-3">
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Customer Delivery Instructions</h6>
                        <p class="mb-0 text-dark italic">"{{ $order->customer_notes }}"</p>
                    </div>
                @endif
            </div>

            <!-- Right: Status Update & Shipping Details -->
            <div class="col-12 col-lg-4 space-y-3">
                <!-- Status Updater Form -->
                <div class="panel p-4 mb-3">
                    <h5 class="mb-3"><i class="bi bi-gear me-1"></i> Order Status</h5>

                    @can('edit orders')
                        <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Fulfillment Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Payment Status</label>
                                <select name="payment_status" class="form-select">
                                    <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Admin Private Notes</label>
                                <textarea name="admin_notes" rows="2" class="form-control" placeholder="Internal notes...">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Update Order
                            </button>
                        </form>
                    @else
                        <div class="space-y-2 small">
                            <div><span class="text-muted">Fulfillment:</span> <span class="badge bg-secondary text-uppercase">{{ $order->status }}</span></div>
                            <div><span class="text-muted">Payment:</span> <span class="badge bg-secondary text-uppercase">{{ $order->payment_status }}</span></div>
                            @if($order->admin_notes)
                                <div class="mt-2"><span class="text-muted">Notes:</span> <em>{{ $order->admin_notes }}</em></div>
                            @endif
                            <div class="alert alert-secondary py-2 small mt-3 mb-0">
                                <i class="bi bi-lock me-1"></i> Read-only view (requires <code>edit orders</code> permission).
                            </div>
                        </div>
                    @endcan
                </div>

                <!-- Customer Details Panel -->
                <div class="panel p-4 mb-3">
                    <h5 class="mb-3"><i class="bi bi-person me-1"></i> Customer Information</h5>

                    <div class="small space-y-2">
                        <div>
                            <span class="text-muted d-block">Full Name:</span>
                            <strong class="text-dark">{{ $order->customer_name }}</strong>
                        </div>
                        <div>
                            <span class="text-muted d-block">Email Address:</span>
                            <a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a>
                        </div>
                        <div>
                            <span class="text-muted d-block">Phone:</span>
                            <strong class="text-dark">{{ $order->customer_phone }}</strong>
                        </div>
                        <div>
                            <span class="text-muted d-block">Payment Method:</span>
                            <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address Panel -->
                <div class="panel p-4">
                    <h5 class="mb-3"><i class="bi bi-geo-alt me-1"></i> Shipping Address</h5>

                    <div class="small text-dark">
                        <strong class="d-block">{{ $order->customer_name }}</strong>
                        <div>{{ $order->shipping_address['street'] ?? '' }}</div>
                        <div>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}</div>
                        <div>{{ $order->shipping_address['country'] ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

