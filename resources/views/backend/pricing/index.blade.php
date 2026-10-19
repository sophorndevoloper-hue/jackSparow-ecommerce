<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-calculator" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manage Sale &bull; B2B Pricing Strategy</p>
                    <h1 class="h3 mb-1">Dynamic Pricing Engine</h1>
                    <p class="text-muted mb-0">Automated multi-tier wholesale discounts (Standard, Gold, OEM) and volume breaks (50+, 200+ units).</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-file-earmark-plus me-1"></i> New RFQ Quote
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-cart-check me-1"></i> Orders
                </a>
            </div>
        </div>

        <!-- Strategy Summary Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-md-4">
                <div class="panel p-3 border-start border-4 border-secondary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary fw-semibold mb-1">Base Tier</span>
                            <h5 class="fw-bold text-dark mb-0">Standard Tier</h5>
                            <small class="text-muted d-block mt-1">0% base tier discount</small>
                            <small class="text-secondary fw-semibold">Retail &amp; introductory wholesale</small>
                        </div>
                        <div class="rounded-circle bg-secondary-subtle text-secondary p-3">
                            <i class="bi bi-person fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-semibold mb-1">Preferred Tier</span>
                            <h5 class="fw-bold text-warning-emphasis mb-0">Gold Tier (10% Off)</h5>
                            <small class="text-muted d-block mt-1">10% off catalog base price</small>
                            <small class="text-warning-emphasis fw-semibold">Regular enterprise partners</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-3">
                            <i class="bi bi-award-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-semibold mb-1">Top Volume Tier</span>
                            <h5 class="fw-bold text-primary mb-0">OEM Tier (20% Off)</h5>
                            <small class="text-muted d-block mt-1">20% off catalog base price</small>
                            <small class="text-primary fw-semibold">Hardware builders &amp; distributors</small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-cpu-fill fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Volume Quantity Breaks Info Banner -->
        <div class="panel p-3 mb-4 bg-light border">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-layers-fill text-primary fs-5"></i>
                        <span>Cumulative Volume Breaks:</span>
                    </div>
                    <small class="text-muted">Applied automatically on top of company tier</small>
                </div>
                <div class="col-md-9">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-white shadow-sm">
                                <span class="d-block text-muted small fw-semibold">1 - 49 Units</span>
                                <strong class="text-dark">Base Tier Rate</strong>
                                <small class="d-block text-muted">+0% volume discount</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-white shadow-sm border-info-subtle">
                                <span class="d-block text-info fw-semibold small">50 - 199 Units</span>
                                <strong class="text-info-emphasis">Case Pack Break</strong>
                                <small class="d-block text-success fw-bold">+5% extra discount</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-white shadow-sm border-success-subtle">
                                <span class="d-block text-success fw-semibold small">200+ Units</span>
                                <strong class="text-success">Pallet / Bulk Break</strong>
                                <small class="d-block text-success fw-bold">+12% extra discount</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Left Column: Interactive Calculator Form -->
            <div class="col-12 col-lg-5">
                <div class="panel p-4 h-100">
                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                        <i class="bi bi-sliders text-primary me-2"></i>Live Dynamic Price Calculator
                    </h6>

                    <form action="{{ route('admin.pricing.index') }}" method="GET" id="pricingCalcForm">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Hardware Product</label>
                            <select name="product_id" id="product_id" class="form-select" onchange="this.form.submit()">
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}" {{ ($selectedProduct?->id === $prod->id) ? 'selected' : '' }}>
                                        {{ $prod->name }} &bull; ${{ number_format($prod->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Catalog Base Price: <strong>${{ number_format($selectedProduct?->price ?? 0, 2) }}</strong> | Cost: <strong>${{ number_format($selectedProduct?->cost_price ?? 0, 2) }}</strong></small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Company Client Tier</label>
                            <div class="btn-group w-100" role="group">
                                @foreach(['Standard', 'Gold', 'OEM'] as $t)
                                    <input type="radio" class="btn-check" name="tier" id="tier_{{ $t }}" value="{{ $t }}" {{ $tier === $t ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="btn btn-outline-primary btn-sm py-2" for="tier_{{ $t }}">
                                        {{ $t }}
                                        <small class="d-block" style="font-size: 10px;">{{ $t === 'Standard' ? 'Base' : ($t === 'Gold' ? '-10%' : '-20%') }}</small>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Order Quantity (Units)</label>
                            <div class="input-group">
                                <input type="number" name="quantity" id="quantity_input" class="form-control" value="{{ $quantity }}" min="1" max="50000" onchange="this.form.submit()">
                                <button type="submit" class="btn btn-secondary">Calculate</button>
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="setQty(10)">10 units</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="setQty(50)">50 units (Break 1)</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="setQty(200)">200 units (Break 2)</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="setQty(500)">500 units</button>
                            </div>
                        </div>

                        <div class="mb-3 border-top pt-3">
                            <label class="form-label fw-semibold d-flex justify-content-between">
                                <span>Custom Margin Override (Optional)</span>
                                <small class="text-muted">For custom RFQ pricing</small>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" name="custom_margin" class="form-control" placeholder="e.g. 18.5" value="{{ $customMargin }}">
                                <span class="input-group-text">%</span>
                                <button type="submit" class="btn btn-outline-primary">Apply Margin</button>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Overrides tier discounts and calculates unit price directly from product cost.</small>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Calculation Output & Matrix -->
            <div class="col-12 col-lg-7">
                @if($calculation)
                    <!-- Live Pricing Result Card -->
                    <div class="panel p-4 mb-3 border border-primary-subtle">
                        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                            <div>
                                <span class="badge bg-primary-subtle text-primary mb-1">{{ $calculation['tier'] }} Tier</span>
                                <h4 class="fw-bold text-dark mb-0">${{ number_format($calculation['unit_price'], 2) }} <span class="fs-6 text-muted fw-normal">/ unit</span></h4>
                                <small class="text-muted">{{ $calculation['product_name'] }} (SKU: {{ $calculation['sku'] }})</small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">Total Extended Price</small>
                                <h3 class="fw-bold text-success mb-0">${{ number_format($calculation['total_price'], 2) }}</h3>
                                <small class="text-muted">for {{ number_format($calculation['quantity']) }} units</small>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-sm-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block" style="font-size: 11px;">Retail Catalog Price</small>
                                    <strong class="text-dark">${{ number_format($calculation['base_price'], 2) }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block" style="font-size: 11px;">Volume Break Applied</small>
                                    <strong class="text-primary">{{ $calculation['volume_bracket'] }}</strong>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block" style="font-size: 11px;">Client Total Savings</small>
                                    <strong class="text-success">${{ number_format($calculation['savings_amount'], 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 border-top pt-3">
                            <div class="col-6 col-sm-3">
                                <small class="text-muted d-block" style="font-size: 11px;">Unit Cost Price</small>
                                <span class="font-monospace text-dark">${{ number_format($calculation['cost_price'], 2) }}</span>
                            </div>
                            <div class="col-6 col-sm-3">
                                <small class="text-muted d-block" style="font-size: 11px;">Total Order Cost</small>
                                <span class="font-monospace text-dark">${{ number_format($calculation['total_cost'], 2) }}</span>
                            </div>
                            <div class="col-6 col-sm-3">
                                <small class="text-muted d-block" style="font-size: 11px;">Gross Profit</small>
                                <span class="font-monospace text-success fw-bold">+${{ number_format($calculation['profit'], 2) }}</span>
                            </div>
                            <div class="col-6 col-sm-3">
                                <small class="text-muted d-block" style="font-size: 11px;">Gross Margin</small>
                                <span class="badge bg-success-subtle text-success fs-6">{{ $calculation['margin_percent'] }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Full Comparison Matrix -->
                    @if($selectedProduct)
                        @php
                            $engine = app(\App\Services\Pricing\DynamicPricingEngine::class);
                        @endphp
                        <div class="panel p-4">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i class="bi bi-grid-3x3 text-primary me-2"></i>Tier &amp; Volume Price Matrix: {{ $selectedProduct->name }}
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered text-center align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start ps-3">Company Tier</th>
                                            <th>1 - 49 Units (Base)</th>
                                            <th class="table-info">50 - 199 Units (-5%)</th>
                                            <th class="table-success">200+ Units (-12%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(['Standard', 'Gold', 'OEM'] as $rowTier)
                                            @php
                                                $p1 = $engine->calculate($selectedProduct, $rowTier, 1);
                                                $p50 = $engine->calculate($selectedProduct, $rowTier, 50);
                                                $p200 = $engine->calculate($selectedProduct, $rowTier, 200);
                                                $isCurrent = ($rowTier === $tier);
                                            @endphp
                                            <tr class="{{ $isCurrent ? 'table-active fw-bold' : '' }}">
                                                <td class="text-start ps-3">
                                                    <span class="badge {{ $rowTier === 'OEM' ? 'bg-primary' : ($rowTier === 'Gold' ? 'bg-warning text-dark' : 'bg-secondary') }} me-1">
                                                        {{ $rowTier }}
                                                    </span>
                                                </td>
                                                <td>${{ number_format($p1['unit_price'], 2) }}</td>
                                                <td class="table-info font-monospace">${{ number_format($p50['unit_price'], 2) }}</td>
                                                <td class="table-success font-monospace">${{ number_format($p200['unit_price'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <script>
        function setQty(val) {
            document.getElementById('quantity_input').value = val;
            document.getElementById('pricingCalcForm').submit();
        }
    </script>
</x-app-layout>

