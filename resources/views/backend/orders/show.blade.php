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
            <div class="heading-actions d-flex gap-2">
                @php
                $orderSerialsCount = \App\Models\SerialNumber::where('order_id', $order->id)->count();
                @endphp
                @if($orderSerialsCount > 0)
                <a href="{{ route('admin.serial-numbers.warranties.print', ['order_id' => $order->id]) }}"
                    target="_blank"
                    class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-1 shadow-xs">
                    <i class="bi bi-printer"></i> Print Warranties ({{ $orderSerialsCount }})
                </a>
                @endif
                <a href="{{ route('admin.fulfillment.show', $order->id) }}"
                    class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-xs">
                    <i class="bi bi-upc-scan"></i> Fulfillment Station
                </a>
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
                        <h5 class="mb-0"><i class="bi bi-cpu me-1"></i> Ordered Computer Components ({{
                            $order->items->count() }})</h5>
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
                                            <span class="badge bg-light text-secondary border font-monospace"
                                                style="font-size: 10px;">
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
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : ''
                                    }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped
                                </option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : ''
                                    }}>Delivered</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : ''
                                    }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : ''
                                    }}>Pending</option>
                                <option value="partially_paid" {{ $order->payment_status === 'partially_paid' ?
                                    'selected' : '' }}>Partially Paid (Deposit Paid)</option>
                                <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid
                                </option>
                                <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : ''
                                    }}>Refunded</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Admin Private Notes</label>
                            <textarea name="admin_notes" rows="2" class="form-control"
                                placeholder="Internal notes...">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Update Order
                        </button>
                    </form>
                    @else
                    <div class="space-y-2 small">
                        <div><span class="text-muted">Fulfillment:</span> <span
                                class="badge bg-secondary text-uppercase">{{ $order->status }}</span></div>
                        <div><span class="text-muted">Payment:</span> <span class="badge bg-secondary text-uppercase">{{
                                $order->payment_status }}</span></div>
                        @if($order->admin_notes)
                        <div class="mt-2"><span class="text-muted">Notes:</span> <em>{{ $order->admin_notes }}</em>
                        </div>
                        @endif
                        <div class="alert alert-secondary py-2 small mt-3 mb-0">
                            <i class="bi bi-lock me-1"></i> Read-only view (requires <code>edit orders</code>
                            permission).
                        </div>
                    </div>
                    @endcan
                </div>

                <!-- B2B Payment Terms & Financial Summary Panel -->
                @if($order->isNetTerms() || $order->payment_due_date || (float)$order->deposit_amount > 0)
                <div class="panel p-4 mb-3 border-start border-4 border-indigo">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-indigo"><i class="bi bi-calendar2-check me-1"></i> B2B Payment Terms</h5>
                        @if($order->isOverdue())
                        <span class="badge bg-danger">Overdue</span>
                        @elseif($order->payment_status === 'paid')
                        <span class="badge bg-success">Settled</span>
                        @else
                        <span class="badge bg-primary">Active Term</span>
                        @endif
                    </div>

                    <div class="small space-y-2">
                        <div>
                            <span class="text-muted d-block">Agreed Term:</span>
                            <strong class="text-dark">{{ $order->paymentTerm?->name ?? 'Custom Net Settlement'
                                }}</strong>
                        </div>

                        @if($order->payment_due_date)
                        <div>
                            <span class="text-muted d-block">Due Date:</span>
                            <strong class="{{ $order->isOverdue() ? 'text-danger' : 'text-dark' }}">
                                {{ $order->payment_due_date->format('M d, Y') }}
                                @if($order->isOverdue())
                                <span class="text-danger small">({{ $order->payment_due_date->diffForHumans() }})</span>
                                @endif
                            </strong>
                        </div>
                        @endif

                        @if((float)$order->deposit_amount > 0)
                        <div class="p-2 bg-light rounded border">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted d-block" style="font-size: 11px;">Upfront Deposit:</span>
                                    <strong class="text-dark">${{ number_format($order->deposit_amount, 2) }}</strong>
                                </div>
                                @if($order->deposit_paid)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Deposit
                                    Paid</span>
                                @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Deposit
                                    Pending</span>
                                @endif
                            </div>
                            @if(! $order->deposit_paid && $order->payment_status !== 'paid')
                            @can('edit orders')
                            <form action="{{ route('admin.orders.mark-deposit-paid', $order->id) }}" method="POST"
                                class="mt-2">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-success btn-sm w-100 py-0"
                                    style="font-size: 11px;">
                                    <i class="bi bi-check-lg me-1"></i> Mark Deposit as Paid
                                </button>
                            </form>
                            @endcan
                            @endif
                        </div>
                        @endif

                        @if((float)$order->early_payment_discount_amount > 0 && $order->early_payment_discount_deadline)
                        <div class="p-2 bg-emerald-50 rounded border border-emerald-200 text-emerald-800"
                            style="font-size: 12px;">
                            <i class="bi bi-piggy-bank me-1"></i> Early Discount:
                            <strong>${{ number_format($order->early_payment_discount_amount, 2) }}</strong>
                            if settled by <strong>{{ $order->early_payment_discount_deadline->format('M d, Y')
                                }}</strong>.
                        </div>
                        @endif

                        @if((float)$order->late_fee_amount > 0)
                        <div class="p-2 bg-danger-subtle rounded border border-danger-subtle text-danger"
                            style="font-size: 12px;">
                            <i class="bi bi-exclamation-octagon me-1"></i> Late Penalty Accrued:
                            <strong>+${{ number_format($order->late_fee_amount, 2) }}</strong>
                        </div>
                        @endif

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Remaining Balance Due:</span>
                            <strong class="fs-6 text-primary">${{ number_format($order->remaining_balance, 2)
                                }}</strong>
                        </div>
                    </div>
                </div>
                @endif

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
                            <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ',
                                $order->payment_method) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Deliveries & Fulfillment Panel -->
                <div class="panel p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-truck me-1"></i> Deliveries</h5>
                        @can('create deliveries')
                        <a href="{{ route('admin.deliveries.create', ['order_id' => $order->id]) }}"
                            class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 12px;">
                            <i class="bi bi-plus-circle me-1"></i> Dispatch
                        </a>
                        @endcan
                    </div>

                    @if($order->deliveries->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($order->deliveries as $del)
                        <div class="p-3 bg-light-subtle rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <a href="{{ route('admin.deliveries.show', $del->id) }}"
                                    class="fw-bold text-primary font-monospace small text-decoration-none">
                                    {{ $del->delivery_number }}
                                </a>
                                <span class="badge {{ $del->statusBadgeClass() }} py-1 px-2" style="font-size: 11px;">
                                    <i class="bi {{ $del->statusIcon() }} me-1"></i>{{ $del->statusLabel() }}
                                </span>
                            </div>
                            <div class="small text-muted mb-1">
                                <span class="text-dark fw-semibold">{{ $del->carrier ?: 'Carrier' }}</span>
                                @if($del->tracking_number)
                                &bull; <span class="font-monospace text-primary">{{ $del->tracking_number }}</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                <small class="text-muted" style="font-size: 11px;">
                                    Est: {{ $del->estimated_delivery_date ? $del->estimated_delivery_date->format('M d,
                                    Y') : 'Pending' }}
                                </small>
                                <a href="{{ route('admin.deliveries.show', $del->id) }}"
                                    class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 11px;">
                                    Track Shipment &rarr;
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-3 text-muted small">
                        <i class="bi bi-box-seam d-block fs-4 text-muted mb-1"></i>
                        No shipment dispatched yet for this order.
                        @can('create deliveries')
                        <div class="mt-2">
                            <a href="{{ route('admin.deliveries.create', ['order_id' => $order->id]) }}"
                                class="btn btn-primary btn-sm">
                                <i class="bi bi-send-fill me-1"></i> Dispatch Shipment Now
                            </a>
                        </div>
                        @endcan
                    </div>
                    @endif
                </div>

                <!-- Shipping Address Panel -->
                <div class="panel p-4">
                    <h5 class="mb-3"><i class="bi bi-geo-alt me-1"></i> Shipping Address</h5>

                    <div class="small text-dark">
                        <strong class="d-block">{{ $order->customer_name }}</strong>
                        <div>{{ $order->shipping_address['street'] ?? '' }}</div>
                        <div>{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }}
                            {{ $order->shipping_address['postal_code'] ?? '' }}</div>
                        <div>{{ $order->shipping_address['country'] ?? '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>