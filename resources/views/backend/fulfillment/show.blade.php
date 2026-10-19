<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge {{ $order->status === 'shipped' ? 'bg-success' : 'bg-warning-subtle text-warning-emphasis border' }} px-2 py-1">
                            {{ ucfirst($order->status) }}
                        </span>
                        <span class="text-muted small">&bull; Ordered {{ $order->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <h1 class="h3 mb-1 font-monospace">Fulfillment Workstation: Order #{{ $order->order_number }}</h1>
                    <p class="text-muted mb-0">Customer: <strong>{{ $order->customer_name }}</strong> ({{ $order->customer_phone ?: $order->customer_email }})</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.fulfillment.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Fulfillment Queue
                </a>
                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-eye me-1"></i> Order Details
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

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $requiredCount = $order->totalSerialTrackingRequiredCount();
            $attachedCount = $order->totalSerialsAttachedCount();
            $isReady = $order->isReadyForShipment();
            $progressPct = $requiredCount > 0 ? min(100, round(($attachedCount / $requiredCount) * 100)) : 100;
        @endphp

        <!-- Barcode Scanner Workstation Station -->
        @if($order->status !== 'shipped')
            <div class="panel p-4 mb-4 border border-primary bg-primary-subtle bg-opacity-10 shadow-sm">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-md-4">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-upc-scan fs-1 text-primary"></i>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Hardware Serial Scanner</h5>
                                <small class="text-muted">Scan physical serial &bull; Verifies reserved item, sets to <strong>'sold'</strong> &bull; Activates 36-mo warranty</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-8">
                        <form action="{{ route('admin.fulfillment.attach-serial', $order->id) }}" method="POST" id="serialScanForm" class="row g-2">
                            @csrf
                            <div class="col-sm-5">
                                <select name="order_item_id" id="scan_order_item_id" class="form-select">
                                    <option value="">-- Auto-Match Line Item --</option>
                                    @foreach($order->items as $item)
                                        @if($item->requiresSerialTracking())
                                            <option value="{{ $item->id }}">
                                                {{ $item->product_name }} ({{ $item->serialNumbers->count() }}/{{ $item->quantity }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-barcode"></i></span>
                                    <input type="text" name="serial_number" id="serial_scanner_input" class="form-control font-monospace fw-bold" placeholder="Scan or type Serial #..." autofocus required>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                    <i class="bi bi-plus-lg me-1"></i> Attach
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- RMA Verification Progress Banner -->
        <div class="panel p-4 mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                <div>
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="bi bi-shield-check text-primary me-2"></i>RMA Warranty &amp; Serial Verification Progress
                    </h6>
                    <small class="text-muted">Serial numbers attached here are permanently recorded for RMA warranty claims upon shipment</small>
                </div>
                <div class="text-end">
                    <span class="fs-5 fw-bold font-monospace {{ $isReady ? 'text-success' : 'text-primary' }}">
                        {{ $attachedCount }} / {{ $requiredCount }} Serials Verified
                    </span>
                    <span class="badge {{ $isReady ? 'bg-success' : 'bg-warning text-dark' }} ms-2">
                        {{ $isReady ? 'Ready to Ship' : 'Scanning in Progress' }}
                    </span>
                </div>
            </div>

            <div class="progress" style="height: 12px;">
                <div class="progress-bar {{ $progressPct >= 100 ? 'bg-success' : 'bg-primary' }} progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: {{ $progressPct }}%;" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>

        <!-- Line Items & Serial Allocation Table -->
        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <div class="panel p-4 mb-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-box-seam text-primary me-2"></i>Order Line Items &amp; Attached Hardware
                    </h6>

                    @foreach($order->items as $item)
                        @php
                            $requiresSerial = $item->requiresSerialTracking();
                            $attached = $item->serialNumbers;
                            $needed = $item->quantity;
                            $isItemComplete = ! $requiresSerial || $attached->count() >= $needed;
                        @endphp
                        <div class="border rounded p-3 mb-3 {{ $isItemComplete ? 'bg-light' : 'border-warning' }}">
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $item->product_name }}</h6>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-light text-dark border font-monospace">{{ $item->product_sku }}</span>
                                        <span class="text-muted small">Qty Ordered: <strong>{{ $needed }}</strong></span>
                                        @if($requiresSerial)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size: 11px;">
                                                <i class="bi bi-upc me-1"></i>Serial Tracking Required
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 11px;">
                                                General SKU (No Serial Required)
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-end">
                                    @if($requiresSerial)
                                        <span class="badge {{ $isItemComplete ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
                                            {{ $attached->count() }} of {{ $needed }} Attached
                                        </span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Non-Serial Item</span>
                                    @endif
                                </div>
                            </div>

                            @if($requiresSerial)
                                <!-- Attached Serials List -->
                                <div class="mt-3">
                                    <small class="fw-semibold text-muted text-uppercase d-block mb-1" style="font-size: 11px;">Attached RMA Serial Numbers:</small>
                                    @if($attached->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            @foreach($attached as $sn)
                                                <div class="badge bg-white border text-dark p-2 d-flex align-items-center gap-2 shadow-sm">
                                                    <i class="bi bi-upc-scan text-primary"></i>
                                                    <span class="font-monospace fw-bold">{{ $sn->serial_number }}</span>
                                                    <span class="badge {{ in_array(strtolower($sn->status), ['sold', 'shipped']) ? 'bg-success' : 'bg-primary-subtle text-primary' }}" style="font-size: 10px;">
                                                        {{ strtoupper($sn->status) }}
                                                    </span>
                                                    @if($sn->warranty_end_date)
                                                        <small class="text-muted" style="font-size: 10px;" title="Hardware Warranty Active (36 Months)">
                                                            Exp: {{ $sn->warranty_end_date->format('M Y') }}
                                                        </small>
                                                    @endif

                                                    @if($order->status !== 'shipped')
                                                        <form action="{{ route('admin.fulfillment.detach-serial', [$order->id, $sn->id]) }}" method="POST" class="d-inline ms-1">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Detach serial">
                                                                <i class="bi bi-x-circle-fill"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2 px-3 small mb-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i> No hardware serial numbers scanned yet for this component.
                                        </div>
                                    @endif

                                    <!-- Quick Pick from Warehouse Inventory -->
                                    @if($order->status !== 'shipped' && $item->product_id && isset($availableSerials[$item->product_id]) && $availableSerials[$item->product_id]->isNotEmpty())
                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <small class="text-muted text-nowrap">Or pick from in-stock:</small>
                                            <form action="{{ route('admin.fulfillment.attach-serial', $order->id) }}" method="POST" class="d-flex gap-2 flex-grow-1">
                                                @csrf
                                                <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                                <select name="serial_number" class="form-select form-select-sm font-monospace">
                                                    <option value="">-- Available Warehouse Serials ({{ $availableSerials[$item->product_id]->count() }}) --</option>
                                                    @foreach($availableSerials[$item->product_id] as $inStockSn)
                                                        <option value="{{ $inStockSn->serial_number }}">
                                                            {{ $inStockSn->serial_number }} (Warehouse ID: {{ $inStockSn->warehouse_id }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap">Attach Picked</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right Column: Shipping Details & Complete Fulfillment -->
            <div class="col-12 col-lg-4">
                <!-- Shipping Address Card -->
                <div class="panel p-4 mb-3">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>Destination &amp; Shipping
                    </h6>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4 text-muted">Recipient:</dt>
                        <dd class="col-sm-8 fw-semibold text-dark">{{ $order->customer_name }}</dd>

                        <dt class="col-sm-4 text-muted">Phone:</dt>
                        <dd class="col-sm-8">{{ $order->customer_phone ?: 'N/A' }}</dd>

                        <dt class="col-sm-4 text-muted">Address:</dt>
                        <dd class="col-sm-8">
                            @if(is_array($order->shipping_address))
                                {{ $order->shipping_address['street'] ?? '' }}, {{ $order->shipping_address['city'] ?? '' }}
                            @else
                                {{ $order->shipping_address ?: 'Commercial Delivery' }}
                            @endif
                        </dd>

                        <dt class="col-sm-4 text-muted">Method:</dt>
                        <dd class="col-sm-8">{{ $order->shippingMethod?->name ?: 'Standard Carrier' }}</dd>
                    </dl>
                </div>

                <!-- Complete Dispatch Confirmation Card -->
                <div class="panel p-4 text-center">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>Fulfillment Dispatch
                    </h6>

                    @if($order->status === 'shipped')
                        <div class="alert alert-success py-3">
                            <i class="bi bi-check2-all display-6 text-success d-block mb-2"></i>
                            <h5 class="fw-bold text-success">Order Shipped</h5>
                            <p class="small mb-0">All hardware serial numbers are active for RMA warranty tracking.</p>
                        </div>
                    @else
                        @if(! $isReady)
                            <div class="alert alert-warning text-start small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>Serials Pending:</strong> You must attach all {{ $requiredCount }} hardware serial numbers before releasing shipment.
                            </div>
                            <button type="button" class="btn btn-secondary w-100 py-2 fw-bold" disabled>
                                <i class="bi bi-lock-fill me-1"></i> Scan All Serials to Ship
                            </button>
                        @else
                            <div class="alert alert-success text-start small mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                <strong>Ready for Dispatch:</strong> All required serial numbers have been verified and assigned.
                            </div>
                            <form action="{{ route('admin.fulfillment.complete', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm">
                                    <i class="bi bi-send-check-fill me-1"></i> Complete Fulfillment &amp; Mark Shipped
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

