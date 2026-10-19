<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-file-earmark-text-fill" aria-hidden="true"></i></span>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge {{ $quote->statusBadgeClass() }} px-2 py-1">{{ $quote->statusLabel() }}</span>
                        <span class="badge {{ $quote->tierBadgeClass() }} px-2 py-1">{{ $quote->company_tier }} Tier</span>
                        <span class="text-muted small">&bull; Created {{ $quote->created_at->format('M d, Y') }}</span>
                    </div>
                    <h1 class="h3 mb-1 font-monospace">RFQ Quote #{{ $quote->quote_number }}</h1>
                    <p class="text-muted mb-0">Client: <strong>{{ $quote->company_name }}</strong> (Attn: {{ $quote->client_name }})</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> All Quotes
                </a>

                @if($quote->status !== 'converted_to_order')
                    <button type="button" class="btn btn-info text-white btn-sm" data-bs-toggle="modal" data-bs-target="#sendQuoteModal">
                        <i class="bi bi-send-fill me-1"></i> Send Quote to Client
                    </button>

                    <form action="{{ route('admin.quotes.convert-to-order', $quote->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Convert this quote into a confirmed customer Order ready for warehouse fulfillment?')">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-cart-check-fill me-1"></i> Convert to Order
                        </button>
                    </form>
                @else
                    <a href="{{ route('admin.orders.show', $quote->converted_order_id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> View Converted Order
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

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="row g-3 mt-1 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <small class="text-muted text-uppercase fw-bold">Total Quoted Value</small>
                    <h3 class="mb-0 mt-1 fw-bold text-success">${{ number_format($quote->total_quoted_price, 2) }}</h3>
                    <small class="text-muted">Customer billing amount</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-secondary">
                    <small class="text-muted text-uppercase fw-bold">Total Component Cost</small>
                    <h3 class="mb-0 mt-1 fw-bold text-dark font-monospace">${{ number_format($quote->total_cost, 2) }}</h3>
                    <small class="text-muted">Wholesale inventory cost</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <small class="text-muted text-uppercase fw-bold">Projected Gross Profit</small>
                    @php
                        $profit = max(0, $quote->total_quoted_price - $quote->total_cost);
                        $margin = $quote->total_quoted_price > 0 ? round(($profit / $quote->total_quoted_price) * 100, 1) : 0;
                    @endphp
                    <h3 class="mb-0 mt-1 fw-bold text-primary font-monospace">+${{ number_format($profit, 2) }}</h3>
                    <small class="text-primary fw-semibold">{{ $margin }}% realized margin</small>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <small class="text-muted text-uppercase fw-bold">Target Margin Setting</small>
                    <h3 class="mb-0 mt-1 fw-bold text-warning-emphasis font-monospace">{{ $quote->target_margin_percent }}%</h3>
                    <small class="text-muted">Targeted pricing baseline</small>
                </div>
            </div>
        </div>

        <!-- Custom Margin Pricing Dashboard -->
        <form action="{{ route('admin.quotes.pricing', $quote->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="row g-3">
                <!-- Main Bill of Materials (BOM) & Custom Margin Editor -->
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-3 gap-2">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-tools text-primary me-2"></i>Bill of Materials &amp; Custom Margin Pricing
                                </h5>
                                <small class="text-muted">Input custom profit margins or unit prices per component line</small>
                            </div>

                            <!-- Fast Margin Calculator Toolbar -->
                            <div class="d-flex align-items-center gap-2 bg-light p-2 rounded border">
                                <span class="small fw-semibold text-dark text-nowrap">Bulk Set Margin:</span>
                                <div class="input-group input-group-sm" style="width: 140px;">
                                    <input type="number" step="0.5" id="bulk_margin_input" class="form-control" value="{{ $quote->target_margin_percent }}" min="1" max="90">
                                    <span class="input-group-text">%</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary text-nowrap" onclick="applyBulkMargin()">
                                    Apply All Lines
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="bomPricingTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%;">Component Description</th>
                                        <th class="text-center" style="width: 10%;">Qty</th>
                                        <th class="text-end" style="width: 15%;">Unit Cost</th>
                                        <th class="text-center" style="width: 18%;">Custom Margin %</th>
                                        <th class="text-end" style="width: 22%;">Quoted Unit Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quote->items as $item)
                                        <tr class="pricing-row" data-id="{{ $item->id }}">
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    @if($item->item_sku)
                                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">{{ $item->item_sku }}</span>
                                                    @endif
                                                    @if($item->target_unit_price)
                                                        <small class="text-muted">Client Target: ${{ number_format($item->target_unit_price, 2) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary-subtle text-dark font-monospace fs-6">
                                                    {{ $item->quantity }}
                                                </span>
                                                <input type="hidden" class="row-qty" value="{{ $item->quantity }}">
                                            </td>
                                            <td class="text-end font-monospace text-muted">
                                                ${{ number_format($item->unit_cost, 2) }}
                                                <input type="hidden" class="row-cost" value="{{ $item->unit_cost }}">
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm mx-auto" style="max-width: 110px;">
                                                    <input type="number" step="0.5" 
                                                           name="items[{{ $item->id }}][margin_percent]" 
                                                           class="form-control text-center row-margin" 
                                                           value="{{ $item->margin_percent }}" 
                                                           min="0" max="95"
                                                           oninput="onMarginChange(this)">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="input-group input-group-sm ms-auto" style="max-width: 130px;">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" 
                                                           name="items[{{ $item->id }}][quoted_unit_price]" 
                                                           class="form-control text-end font-monospace fw-bold row-price" 
                                                           value="{{ $item->quoted_unit_price }}" 
                                                           min="0"
                                                           oninput="onPriceChange(this)" required>
                                                </div>
                                                <small class="text-muted d-block mt-1 font-monospace line-total-display">
                                                    Total: ${{ number_format($item->total_quoted_price, 2) }}
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Recalculated Subtotal:</th>
                                        <th colspan="2" class="text-end fs-5 fw-bold text-success font-monospace" id="bomSubtotalDisplay">
                                            ${{ number_format($quote->total_quoted_price, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                            <small class="text-muted">Changes to margins and quoted prices are reflected immediately in the quote total.</small>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Save Custom Pricing &amp; Margins
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: RFQ Specifications & Metadata -->
                <div class="col-12 col-lg-4">
                    <!-- Client Details Panel -->
                    <div class="panel p-4 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-building text-primary me-2"></i>Client Specifications
                        </h6>
                        <dl class="row mb-0 small">
                            <dt class="col-sm-4 text-muted">Company:</dt>
                            <dd class="col-sm-8 fw-semibold text-dark">{{ $quote->company_name }}</dd>

                            <dt class="col-sm-4 text-muted">Contact:</dt>
                            <dd class="col-sm-8 text-dark">{{ $quote->client_name }}</dd>

                            <dt class="col-sm-4 text-muted">Email:</dt>
                            <dd class="col-sm-8">{{ $quote->client_email ?: 'N/A' }}</dd>

                            <dt class="col-sm-4 text-muted">Phone:</dt>
                            <dd class="col-sm-8">{{ $quote->client_phone ?: 'N/A' }}</dd>

                            <dt class="col-sm-4 text-muted">Client Tier:</dt>
                            <dd class="col-sm-8"><span class="badge {{ $quote->tierBadgeClass() }}">{{ $quote->company_tier }} Tier</span></dd>

                            <dt class="col-sm-4 text-muted">Target Date:</dt>
                            <dd class="col-sm-8 font-monospace">{{ $quote->target_delivery_date ? $quote->target_delivery_date->format('M d, Y') : 'Flexible' }}</dd>

                            <dt class="col-sm-4 text-muted">Valid Until:</dt>
                            <dd class="col-sm-8 font-monospace text-danger">{{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'Pending quote send' }}</dd>
                        </dl>

                        @if($quote->client_notes)
                            <div class="border-top pt-2 mt-2">
                                <small class="fw-bold text-dark d-block">Client Project Notes:</small>
                                <p class="text-muted small mb-0">{{ $quote->client_notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Internal Admin Margin Notes -->
                    <div class="panel p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Quote Terms &amp; Margin Notes
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Margin %</label>
                            <input type="number" step="0.5" name="target_margin_percent" class="form-control form-control-sm" value="{{ $quote->target_margin_percent }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Admin Internal Terms</label>
                            <textarea name="admin_notes" class="form-control" rows="4" placeholder="Special vendor rebate terms, payment discount terms, warranty inclusions...">{{ $quote->admin_notes }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-check2 me-1"></i> Update Quote Settings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Send Quote Modal -->
    <div class="modal fade" id="sendQuoteModal" tabindex="-1" aria-labelledby="sendQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.quotes.send', $quote->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="sendQuoteModalLabel">
                            <i class="bi bi-send-check text-info me-2"></i>Send Finalized Quote
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="mb-3">
                            You are about to finalize and transmit <strong>Quote #{{ $quote->quote_number }}</strong> to <strong>{{ $quote->company_name }}</strong> for a total amount of <strong>${{ number_format($quote->total_quoted_price, 2) }}</strong>.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Quote Validity Period</label>
                            <select name="valid_days" class="form-select">
                                <option value="7">7 Days</option>
                                <option value="14" selected>14 Days (Standard)</option>
                                <option value="30">30 Days</option>
                                <option value="60">60 Days</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Additional Terms for Client</label>
                            <textarea name="admin_notes" class="form-control" rows="2" placeholder="FOB Warehouse, 30-day net terms upon credit approval...">{{ $quote->admin_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="bi bi-send-fill me-1"></i> Send Official Quote
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function onMarginChange(input) {
            const row = input.closest('tr');
            const cost = parseFloat(row.querySelector('.row-cost').value) || 0;
            const margin = parseFloat(input.value) || 0;
            const qty = parseInt(row.querySelector('.row-qty').value) || 1;

            if (margin < 100 && cost > 0) {
                const unitPrice = cost / (1 - (margin / 100));
                const priceInput = row.querySelector('.row-price');
                priceInput.value = unitPrice.toFixed(2);
                updateLineTotal(row, unitPrice, qty);
            }
            updateGrandTotal();
        }

        function onPriceChange(input) {
            const row = input.closest('tr');
            const cost = parseFloat(row.querySelector('.row-cost').value) || 0;
            const price = parseFloat(input.value) || 0;
            const qty = parseInt(row.querySelector('.row-qty').value) || 1;

            if (price > 0 && cost > 0) {
                const profit = price - cost;
                const margin = (profit / price) * 100;
                row.querySelector('.row-margin').value = margin.toFixed(1);
                updateLineTotal(row, price, qty);
            }
            updateGrandTotal();
        }

        function updateLineTotal(row, unitPrice, qty) {
            const total = (unitPrice * qty).toFixed(2);
            row.querySelector('.line-total-display').textContent = 'Total: $' + Number(total).toLocaleString('en-US', { minimumFractionDigits: 2 });
        }

        function applyBulkMargin() {
            const margin = parseFloat(document.getElementById('bulk_margin_input').value) || 15;
            document.querySelectorAll('.pricing-row').forEach(row => {
                const marginInput = row.querySelector('.row-margin');
                marginInput.value = margin;
                onMarginChange(marginInput);
            });
        }

        function updateGrandTotal() {
            let sum = 0;
            document.querySelectorAll('.pricing-row').forEach(row => {
                const price = parseFloat(row.querySelector('.row-price').value) || 0;
                const qty = parseInt(row.querySelector('.row-qty').value) || 1;
                sum += (price * qty);
            });
            document.getElementById('bomSubtotalDisplay').textContent = '$' + Number(sum.toFixed(2)).toLocaleString('en-US', { minimumFractionDigits: 2 });
        }
    </script>
</x-app-layout>

