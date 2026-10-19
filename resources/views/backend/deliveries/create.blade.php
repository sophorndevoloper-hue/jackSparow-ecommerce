<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Sales &amp; Fulfillment &bull; New Shipment</p>
                    <h1 class="h3 mb-1">Dispatch Order Delivery</h1>
                    <p class="text-muted mb-0">Assign a shipping carrier, generate tracking numbers, and initiate fulfillment dispatch.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Deliveries
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.deliveries.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Left Column: Order & Carrier Details -->
                <div class="col-12 col-lg-7">
                    <!-- Order Selection Card -->
                    <div class="panel p-4 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-cart-check text-primary me-2"></i>Target Customer Order
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Order to Fulfill <span class="text-danger">*</span></label>
                            <select name="order_id" id="order_id" class="form-select" required>
                                <option value="">-- Choose an Order --</option>
                                @foreach($availableOrders as $ord)
                                    <option value="{{ $ord->id }}" 
                                            {{ (old('order_id', request('order_id')) == $ord->id) ? 'selected' : '' }}
                                            data-recipient="{{ $ord->customer_name }}"
                                            data-phone="{{ $ord->customer_phone }}"
                                            data-email="{{ $ord->customer_email }}"
                                            data-street="{{ is_array($ord->shipping_address) ? ($ord->shipping_address['street'] ?? '') : '' }}"
                                            data-city="{{ is_array($ord->shipping_address) ? ($ord->shipping_address['city'] ?? '') : '' }}"
                                            data-state="{{ is_array($ord->shipping_address) ? ($ord->shipping_address['state'] ?? '') : '' }}"
                                            data-postal="{{ is_array($ord->shipping_address) ? ($ord->shipping_address['postal_code'] ?? '') : '' }}"
                                            data-country="{{ is_array($ord->shipping_address) ? ($ord->shipping_address['country'] ?? 'United States') : 'United States' }}">
                                        Order #{{ $ord->order_number }} &bull; {{ $ord->customer_name }} &bull; ${{ number_format($ord->total_amount, 2) }} ({{ ucfirst($ord->status) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Selecting an order will auto-fill recipient and address details.</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Shipping Method</label>
                                <select name="shipping_method_id" id="shipping_method_id" class="form-select">
                                    <option value="">-- Standard / Carrier Direct --</option>
                                    @foreach($shippingMethods as $method)
                                        <option value="{{ $method->id }}" data-carrier="{{ $method->carrier }}" {{ old('shipping_method_id') == $method->id ? 'selected' : '' }}>
                                            {{ $method->name }} (${{ number_format($method->base_cost, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Assign Delivery Agent</label>
                                <select name="delivery_agent_id" id="delivery_agent_id" class="form-select">
                                    <option value="">-- External Carrier / No Fleet Agent --</option>
                                    @foreach($deliveryAgents as $agent)
                                        <option value="{{ $agent->id }}" 
                                                data-carrier-name="{{ $agent->name }} ({{ $agent->vehicleLabel() }})" 
                                                {{ old('delivery_agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }} ({{ $agent->code }}) &bull; {{ $agent->vehicleLabel() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Carrier / Courier Name</label>
                                <input type="text" name="carrier" id="carrier_input" class="form-control" value="{{ old('carrier', 'FedEx Ground') }}" placeholder="e.g. FedEx Ground, Internal Fleet, DHL Express">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-7">
                                <label class="form-label fw-semibold">Tracking Number</label>
                                <div class="input-group">
                                    <input type="text" name="tracking_number" id="tracking_number" class="form-control font-monospace" value="{{ old('tracking_number') }}" placeholder="e.g. TRK-FDX-982143">
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateTrackingNumber()">
                                        Generate
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fw-semibold">Initial Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending Dispatch</option>
                                    <option value="preparing" {{ old('status') === 'preparing' ? 'selected' : '' }}>Packing &amp; Preparing</option>
                                    <option value="dispatched" {{ old('status', 'dispatched') === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                    <option value="in_transit" {{ old('status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Estimated Delivery Date</label>
                                <input type="date" name="estimated_delivery_date" class="form-control font-monospace" value="{{ old('estimated_delivery_date', now()->addDays(3)->toDateString()) }}">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-semibold">Handling Notes</label>
                                <textarea name="notes" class="form-control" rows="1" placeholder="Fragile, signature required...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Recipient & Shipping Address -->
                <div class="col-12 col-lg-5">
                    <div class="panel p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>Destination &amp; Contact
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Recipient Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" id="recipient_name" class="form-control" value="{{ old('recipient_name', $selectedOrder?->customer_name) }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="recipient_phone" id="recipient_phone" class="form-control" value="{{ old('recipient_phone', $selectedOrder?->customer_phone) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="recipient_email" id="recipient_email" class="form-control" value="{{ old('recipient_email', $selectedOrder?->customer_email) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Street Address <span class="text-danger">*</span></label>
                            <input type="text" name="street" id="street" class="form-control" value="{{ old('street', is_array($selectedOrder?->shipping_address) ? ($selectedOrder->shipping_address['street'] ?? '') : '') }}" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city', is_array($selectedOrder?->shipping_address) ? ($selectedOrder->shipping_address['city'] ?? '') : '') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">State / Region</label>
                                <input type="text" name="state" id="state" class="form-control" value="{{ old('state', is_array($selectedOrder?->shipping_address) ? ($selectedOrder->shipping_address['state'] ?? '') : '') }}">
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code', is_array($selectedOrder?->shipping_address) ? ($selectedOrder->shipping_address['postal_code'] ?? '') : '') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Country</label>
                                <input type="text" name="country" id="country" class="form-control" value="{{ old('country', is_array($selectedOrder?->shipping_address) ? ($selectedOrder->shipping_address['country'] ?? 'United States') : 'United States') }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-send-fill me-1"></i> Dispatch &amp; Confirm Delivery
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('order_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                if (selected.dataset.recipient) document.getElementById('recipient_name').value = selected.dataset.recipient;
                if (selected.dataset.phone) document.getElementById('recipient_phone').value = selected.dataset.phone;
                if (selected.dataset.email) document.getElementById('recipient_email').value = selected.dataset.email;
                if (selected.dataset.street) document.getElementById('street').value = selected.dataset.street;
                if (selected.dataset.city) document.getElementById('city').value = selected.dataset.city;
                if (selected.dataset.state) document.getElementById('state').value = selected.dataset.state;
                if (selected.dataset.postal) document.getElementById('postal_code').value = selected.dataset.postal;
                if (selected.dataset.country) document.getElementById('country').value = selected.dataset.country;
            }
        });

        document.getElementById('shipping_method_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.dataset.carrier) {
                document.getElementById('carrier_input').value = selected.dataset.carrier;
            }
        });

        document.getElementById('delivery_agent_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            if (selected.dataset.carrierName) {
                document.getElementById('carrier_input').value = selected.dataset.carrierName;
            }
        });

        function generateTrackingNumber() {
            const carrier = document.getElementById('carrier_input').value || 'SHP';
            const prefix = carrier.replace(/[^A-Za-z]/g, '').substring(0, 3).toUpperCase() || 'TRK';
            const random = Math.floor(100000 + Math.random() * 900000);
            document.getElementById('tracking_number').value = 'TRK-' + prefix + '-' + random;
        }
    </script>
</x-app-layout>

