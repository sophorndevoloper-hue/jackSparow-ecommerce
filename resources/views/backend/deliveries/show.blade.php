<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Sales &amp; Fulfillment &bull; Shipment Tracking</p>
                    <h1 class="h3 mb-1">Delivery {{ $delivery->delivery_number }}</h1>
                    <p class="text-muted mb-0">
                        Dispatched for order 
                        @if($delivery->order)
                            <a href="{{ route('admin.orders.show', $delivery->order->id) }}" class="fw-bold text-primary text-decoration-none">
                                #{{ $delivery->order->order_number }}
                            </a>
                        @else
                            <span class="text-muted">#N/A</span>
                        @endif
                        &bull; Created {{ $delivery->created_at->format('M d, Y H:i') }}
                    </p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Deliveries
                </a>
                @if($delivery->order)
                    <a href="{{ route('admin.orders.show', $delivery->order->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-cart-check me-1"></i> Inspect Order
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $stages = [
                'pending' => ['label' => 'Order Received', 'icon' => 'bi-bag-check'],
                'preparing' => ['label' => 'Packing & Preparing', 'icon' => 'bi-box-seam'],
                'dispatched' => ['label' => 'Dispatched', 'icon' => 'bi-send'],
                'in_transit' => ['label' => 'In Transit', 'icon' => 'bi-truck'],
                'out_for_delivery' => ['label' => 'Out for Delivery', 'icon' => 'bi-geo-alt-fill'],
                'delivered' => ['label' => 'Delivered', 'icon' => 'bi-check-circle-fill'],
            ];
            $stageKeys = array_keys($stages);
            $currentStatus = $delivery->status === 'cancelled' ? 'pending' : $delivery->status;
            $currentIndex = array_search($currentStatus, $stageKeys);
            if ($currentIndex === false) {
                $currentIndex = 0;
            }
        @endphp

        <!-- Delivery Progress Stepper -->
        <div class="panel p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-activity text-primary me-2"></i>Fulfillment Progress
                </h6>
                <span class="badge {{ $delivery->statusBadgeClass() }} px-3 py-1 fs-6">
                    <i class="bi {{ $delivery->statusIcon() }} me-1"></i>{{ $delivery->statusLabel() }}
                </span>
            </div>

            <div class="row g-2 text-center position-relative">
                @foreach($stages as $key => $stage)
                    @php
                        $idx = array_search($key, $stageKeys);
                        $isPassed = $idx <= $currentIndex && $delivery->status !== 'cancelled';
                        $isCurrent = $idx === $currentIndex && $delivery->status !== 'cancelled';
                    @endphp
                    <div class="col">
                        <div class="d-flex flex-column align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 {{ $isPassed ? 'bg-primary text-white shadow-sm' : 'bg-secondary-subtle text-muted' }}" style="width: 44px; height: 44px; font-size: 18px;">
                                <i class="bi {{ $stage['icon'] }}"></i>
                            </div>
                            <small class="fw-bold {{ $isCurrent ? 'text-primary' : ($isPassed ? 'text-dark' : 'text-muted') }}" style="font-size: 12px;">
                                {{ $stage['label'] }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="row g-3">
            <!-- Left Column: Details & Recipient -->
            <div class="col-12 col-lg-7">
                <!-- Carrier & Shipment Info Card -->
                <div class="panel p-4 mb-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-truck text-primary me-2"></i>Carrier &amp; Dispatch Information
                    </h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Shipping Carrier</small>
                            <span class="fw-bold text-dark fs-6">{{ $delivery->carrier ?: 'Standard Delivery' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Tracking Number</small>
                            @if($delivery->tracking_number)
                                <span class="badge bg-primary-subtle text-primary font-monospace fs-6 px-2 py-1">
                                    <i class="bi bi-upc-scan me-1"></i>{{ $delivery->tracking_number }}
                                </span>
                            @else
                                <span class="text-muted small">Not assigned yet</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Shipping Method</small>
                            <span class="text-body fw-semibold">{{ $delivery->shippingMethod?->name ?: 'Standard Service' }}</span>
                        </div>
                        @if($delivery->deliveryAgent)
                            <div class="col-sm-6">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Assigned Delivery Agent</small>
                                <span class="fw-bold text-dark">
                                    <i class="bi {{ $delivery->deliveryAgent->vehicleIcon() }} text-primary me-1"></i>
                                    {{ $delivery->deliveryAgent->name }}
                                </span>
                                <span class="badge bg-light text-dark border font-monospace ms-1">{{ $delivery->deliveryAgent->code }}</span>
                            </div>
                        @endif
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Estimated Arrival</small>
                            <span class="text-body font-monospace">{{ $delivery->estimated_delivery_date ? $delivery->estimated_delivery_date->format('M d, Y') : 'Pending estimate' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Dispatched At</small>
                            <span class="text-body">{{ $delivery->dispatched_at ? $delivery->dispatched_at->format('M d, Y H:i') : 'Not yet dispatched' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Delivered At</small>
                            <span class="text-body">{{ $delivery->delivered_at ? $delivery->delivered_at->format('M d, Y H:i') : 'In progress' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recipient & Shipping Address Card -->
                <div class="panel p-4 mb-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>Destination &amp; Contact Details
                    </h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Recipient Name</small>
                            <strong class="text-dark">{{ $delivery->recipient_name }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Phone Contact</small>
                            <span class="text-dark">{{ $delivery->recipient_phone ?: 'Not provided' }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Destination Address</small>
                            <div class="p-3 bg-light-subtle rounded border mt-1">
                                @if(is_array($delivery->shipping_address))
                                    <div>{{ $delivery->shipping_address['street'] ?? '' }}</div>
                                    <div>
                                        {{ $delivery->shipping_address['city'] ?? '' }}
                                        {{ !empty($delivery->shipping_address['state']) ? ', ' . $delivery->shipping_address['state'] : '' }}
                                        {{ $delivery->shipping_address['postal_code'] ?? '' }}
                                    </div>
                                    <div class="text-muted small">{{ $delivery->shipping_address['country'] ?? '' }}</div>
                                @else
                                    <span class="text-muted">Standard address on file</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                @if($delivery->notes)
                    <div class="panel p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                            <i class="bi bi-chat-left-text text-primary me-2"></i>Delivery Notes &amp; Instructions
                        </h6>
                        <p class="text-muted mb-0 small">{{ $delivery->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Right Column: Update Form & Order Items -->
            <div class="col-12 col-lg-5">
                <!-- Update Status & Tracking Form -->
                @can('edit deliveries')
                    <div class="panel p-4 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Update Shipment Status
                        </h6>
                        <form action="{{ route('admin.deliveries.update', $delivery->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Fulfillment Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select form-select-sm" required>
                                    <option value="pending" {{ $delivery->status === 'pending' ? 'selected' : '' }}>Pending Dispatch</option>
                                    <option value="preparing" {{ $delivery->status === 'preparing' ? 'selected' : '' }}>Packing &amp; Preparing</option>
                                    <option value="dispatched" {{ $delivery->status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="in_transit" {{ $delivery->status === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                    <option value="out_for_delivery" {{ $delivery->status === 'out_for_delivery' ? 'selected' : '' }}>Out for Delivery</option>
                                    <option value="delivered" {{ $delivery->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $delivery->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Carrier Name</label>
                                <input type="text" name="carrier" class="form-control form-control-sm" value="{{ old('carrier', $delivery->carrier) }}" placeholder="e.g. FedEx Ground, DHL Express">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Tracking Number</label>
                                <input type="text" name="tracking_number" class="form-control form-control-sm font-monospace" value="{{ old('tracking_number', $delivery->tracking_number) }}" placeholder="e.g. TRK-FDX-123456">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Estimated Delivery Date</label>
                                <input type="date" name="estimated_delivery_date" class="form-control form-control-sm font-monospace" value="{{ old('estimated_delivery_date', $delivery->estimated_delivery_date?->toDateString()) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Handling Notes</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Internal or courier instructions...">{{ old('notes', $delivery->notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                        </form>
                    </div>
                @endcan

                <!-- Items in This Order -->
                <div class="panel p-4">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-box text-primary me-2"></i>Order Package Items
                    </h6>
                    @if($delivery->order && $delivery->order->items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Component</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($delivery->order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-body-emphasis">{{ $item->product_name ?: $item->product?->name }}</div>
                                                <small class="text-muted font-monospace" style="font-size: 11px;">SKU: {{ $item->product?->sku ?? 'N/A' }}</small>
                                            </td>
                                            <td class="text-center font-monospace">x{{ $item->quantity }}</td>
                                            <td class="text-end fw-bold font-monospace">${{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted small mb-0">No order item details available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

