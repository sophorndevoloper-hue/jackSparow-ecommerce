<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-file-earmark-plus" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manage Sale &bull; Request for Quote (RFQ)</p>
                    <h1 class="h3 mb-1">Submit Custom Bill of Materials (BOM)</h1>
                    <p class="text-muted mb-0">Create a wholesale quote request with custom hardware components, target unit pricing, and tier assignments.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Quotes
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

        <form action="{{ route('admin.quotes.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Client & Company Details Card -->
                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-building text-primary me-2"></i>Wholesale Client Profile
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Company / Organization <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}" placeholder="e.g. CyberTech Systems Ltd." required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="client_name" class="form-control" value="{{ old('client_name') }}" placeholder="e.g. Alex Morgan (Procurement)" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" name="client_email" class="form-control" value="{{ old('client_email') }}" placeholder="e.g. procurement@cybertech.local">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Phone Contact</label>
                                <input type="text" name="client_phone" class="form-control" value="{{ old('client_phone') }}" placeholder="e.g. +855 12 345 678">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Company Client Tier <span class="text-danger">*</span></label>
                            <select name="company_tier" class="form-select" required>
                                <option value="Standard" {{ old('company_tier') === 'Standard' ? 'selected' : '' }}>Standard Tier (Base Catalog)</option>
                                <option value="Gold" {{ old('company_tier', 'Gold') === 'Gold' ? 'selected' : '' }}>Gold Tier (10% Preferred Discount)</option>
                                <option value="OEM" {{ old('company_tier') === 'OEM' ? 'selected' : '' }}>OEM Tier (20% High-Volume Builder)</option>
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Determines default baseline discount and volume break curve.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Delivery Date</label>
                            <input type="date" name="target_delivery_date" class="form-control" value="{{ old('target_delivery_date', date('Y-m-d', strtotime('+14 days'))) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Initial Target Margin %</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" name="target_margin_percent" class="form-control" value="{{ old('target_margin_percent', 15.0) }}" min="1" max="90">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Target profit margin used by dynamic pricing engine for this BOM.</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Project / Client Notes</label>
                            <textarea name="client_notes" class="form-control" rows="3" placeholder="Server rack build, bulk delivery terms, special packaging requirements...">{{ old('client_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Custom Bill of Materials (BOM) Line Items Card -->
                <div class="col-12 col-lg-8">
                    <div class="panel p-4">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-list-check text-primary me-2"></i>Custom Bill of Materials (BOM)
                            </h6>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addBomRow()">
                                <i class="bi bi-plus-lg me-1"></i> Add BOM Line Item
                            </button>
                        </div>

                        <p class="text-muted small mb-3">
                            Select components directly from your active catalog or enter custom uncataloged OEM parts. The dynamic pricing engine will calculate preliminary cost and suggested volume-discounted quotes.
                        </p>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle mb-0" id="bomTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45%;">Hardware Component / Description</th>
                                        <th style="width: 20%;">SKU / MPN</th>
                                        <th style="width: 15%;">Qty</th>
                                        <th style="width: 15%;">Target Unit $</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="bomItemsContainer">
                                    <tr class="bom-row" data-index="0">
                                        <td>
                                            <select name="items[0][product_id]" class="form-select form-select-sm mb-1 product-selector" onchange="onProductSelect(this, 0)">
                                                <option value="">-- Or Select Catalog Product --</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->id }}" data-name="{{ $prod->name }}" data-sku="{{ $prod->sku }}" data-price="{{ $prod->price }}">
                                                        {{ $prod->name }} (${{ number_format($prod->price, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="items[0][item_name]" class="form-control form-control-sm item-name-input" placeholder="Component Name (e.g. DDR5 32GB 6000MHz)" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][item_sku]" class="form-control form-control-sm font-monospace item-sku-input" placeholder="e.g. RAM-DDR5-32G">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm text-center" value="10" min="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="items[0][target_unit_price]" class="form-control" placeholder="Target">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeBomRow(this)" title="Remove item">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addBomRow()">
                                <i class="bi bi-plus-circle me-1"></i> Add Another Component
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check2-circle me-1"></i> Submit RFQ &amp; Review Margins
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let bomIndex = 1;

        function addBomRow() {
            const container = document.getElementById('bomItemsContainer');
            const row = document.createElement('tr');
            row.className = 'bom-row';
            row.dataset.index = bomIndex;

            row.innerHTML = `
                <td>
                    <select name="items[${bomIndex}][product_id]" class="form-select form-select-sm mb-1 product-selector" onchange="onProductSelect(this, ${bomIndex})">
                        <option value="">-- Or Select Catalog Product --</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->id }}" data-name="{{ $prod->name }}" data-sku="{{ $prod->sku }}" data-price="{{ $prod->price }}">
                                {{ $prod->name }} (${{ number_format($prod->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                    <input type="text" name="items[${bomIndex}][item_name]" class="form-control form-control-sm item-name-input" placeholder="Component Name" required>
                </td>
                <td>
                    <input type="text" name="items[${bomIndex}][item_sku]" class="form-control form-control-sm font-monospace item-sku-input" placeholder="SKU / Part #">
                </td>
                <td>
                    <input type="number" name="items[${bomIndex}][quantity]" class="form-control form-control-sm text-center" value="10" min="1" required>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" name="items[${bomIndex}][target_unit_price]" class="form-control" placeholder="Target">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeBomRow(this)">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            `;

            container.appendChild(row);
            bomIndex++;
        }

        function removeBomRow(btn) {
            const rows = document.querySelectorAll('.bom-row');
            if (rows.length > 1) {
                btn.closest('tr').remove();
            } else {
                alert('A Bill of Materials must contain at least 1 line item.');
            }
        }

        function onProductSelect(select, idx) {
            const opt = select.options[select.selectedIndex];
            const row = select.closest('tr');
            if (opt.value) {
                row.querySelector('.item-name-input').value = opt.dataset.name || '';
                row.querySelector('.item-sku-input').value = opt.dataset.sku || '';
            }
        }
    </script>
</x-app-layout>

