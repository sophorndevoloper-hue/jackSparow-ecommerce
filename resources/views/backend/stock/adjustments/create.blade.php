<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Inventory Control</p>
                    <h1 class="h3 mb-1">New Stock Adjustment</h1>
                    <p class="text-muted mb-0">Record physical stock counts, write off damages, or correct inventory counts.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.stock.adjustments.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Adjustments
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.stock.adjustments.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Left: Configuration -->
                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 text-dark fw-bold">Adjustment Details</h5>

                        <!-- Warehouse Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Target Warehouse *</label>
                            <select name="warehouse_id" id="warehouseSelect" required class="form-select">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)$selectedWarehouseId === (string)$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Adjustment Type *</label>
                            <select name="type" id="adjustmentType" required class="form-select" onchange="updateQtyLabels(this.value)">
                                <option value="addition" {{ old('type') === 'addition' ? 'selected' : '' }}>Stock Addition (+) — Add stock units</option>
                                <option value="subtraction" {{ old('type') === 'subtraction' ? 'selected' : '' }}>Stock Subtraction (-) — Deduct units</option>
                                <option value="correction" {{ old('type') === 'correction' ? 'selected' : '' }}>Count Correction (=) — Exact counted quantity</option>
                            </select>
                        </div>

                        <!-- Reason -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Reason *</label>
                            <select name="reason" required class="form-select">
                                <option value="physical_count">Physical Inventory Count</option>
                                <option value="received">Direct Stock Received</option>
                                <option value="damaged">Damaged Hardware</option>
                                <option value="loss">Loss / Shrinkage</option>
                                <option value="correction">Data Correction</option>
                                <option value="other">Other / Manual Adjustment</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Notes / Audit Remarks</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional audit memo or explanation...">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Post Stock Adjustment
                        </button>
                    </div>
                </div>

                <!-- Right: Product Line Items -->
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <div>
                                <h5 class="mb-0 text-dark fw-bold">Hardware Components to Adjust</h5>
                                <small class="text-muted">Search or select components, enter quantities, or scan serial numbers to add stock.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addProductRow()">
                                <i class="bi bi-plus-lg me-1"></i> Add Product
                            </button>
                        </div>

                        <div id="productRowsContainer">
                            <!-- Populated dynamically via JS -->
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2 py-2" onclick="addProductRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Component
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const productsList = @json($productsData ?? []);

        let rowIndex = 0;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function createProductRowHtml(index, selectedProductId = '') {
            const type = document.getElementById('adjustmentType').value;
            let qtyLabel = 'Quantity (+ units) *';
            if (type === 'subtraction') qtyLabel = 'Quantity to Deduct (-) *';
            else if (type === 'correction') qtyLabel = 'New Count Quantity (=) *';

            return `
                <div class="product-row border p-3 rounded bg-light-subtle mb-3 shadow-xs" data-row-index="${index}">
                    <div class="row g-2 align-items-start">
                        <!-- Product Component: Single Search/Select Input Box -->
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-bold text-dark mb-1">Product Component *</label>
                            <div class="product-picker-wrapper position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" 
                                           class="form-control form-control-sm product-search-input" 
                                           placeholder="Click to select or type to search..." 
                                           autocomplete="off" 
                                           required>
                                    <button type="button" class="btn btn-outline-secondary btn-sm product-picker-toggle" tabindex="-1">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="products[${index}][product_id]" class="product-id-input" value="${selectedProductId}" required>
                                
                                <!-- Floating Search & Select Dropdown Menu -->
                                <div class="product-dropdown-menu shadow rounded-2 border position-absolute w-100 mt-1 d-none" 
                                     style="max-height: 240px; overflow-y: auto; z-index: 1050;">
                                </div>
                            </div>
                            <div class="selected-product-badge mt-1 d-none"></div>
                        </div>

                        <!-- Quantity -->
                        <div class="col-10 col-md-4">
                            <label class="form-label small fw-bold text-dark mb-1 qty-label">${qtyLabel}</label>
                            <input type="number" 
                                   name="products[${index}][quantity]" 
                                   value="1" 
                                   min="0" 
                                   required 
                                   class="form-control form-control-sm font-monospace text-center qty-input">
                        </div>

                        <!-- Delete Button -->
                        <div class="col-2 col-md-1 text-end pt-4">
                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeProductRow(this)" title="Remove Component">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Add Stock by Entering Serial Number -->
                        <div class="col-12 mt-2 pt-2 border-top border-light-subtle serial-box">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small fw-semibold text-dark mb-0">
                                    <i class="bi bi-upc-scan text-primary me-1"></i> Serial Numbers 
                                    <span class="text-muted fw-normal font-monospace" style="font-size: 11px;">(Scan barcode or enter 1 per line to add stock)</span>
                                </label>
                                <span class="badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge">0 serials</span>
                            </div>
                            <textarea name="products[${index}][serials]" 
                                      rows="2" 
                                      class="form-control form-control-sm font-monospace serials-textarea" 
                                      placeholder="Scan barcode or enter serial numbers (1 per line or comma-separated)...&#10;e.g. SN-AMD-10001 (auto-updates quantity)"></textarea>
                        </div>
                    </div>
                </div>
            `;
        }

        function bindRowEvents(rowEl) {
            const wrapper = rowEl.querySelector('.product-picker-wrapper');
            const searchInput = wrapper.querySelector('.product-search-input');
            const toggleBtn = wrapper.querySelector('.product-picker-toggle');
            const hiddenIdInput = wrapper.querySelector('.product-id-input');
            const dropdown = wrapper.querySelector('.product-dropdown-menu');
            const badgeContainer = rowEl.querySelector('.selected-product-badge');
            const serialsTextarea = rowEl.querySelector('.serials-textarea');
            const serialsCountBadge = rowEl.querySelector('.serial-count-badge');
            const qtyInput = rowEl.querySelector('.qty-input');

            function renderDropdown(filterText = '') {
                const query = (filterText || '').toLowerCase().trim();
                const matched = productsList.filter(p => {
                    if (!query) return true;
                    return p.name.toLowerCase().includes(query) || 
                           p.sku.toLowerCase().includes(query) || 
                           p.category.toLowerCase().includes(query);
                });

                dropdown.innerHTML = '';
                if (matched.length === 0) {
                    dropdown.innerHTML = '<div class="p-3 text-center text-muted small">No components match your search</div>';
                    return;
                }

                matched.forEach(p => {
                    const item = document.createElement('div');
                    item.className = 'product-dropdown-item px-3 py-2 border-bottom';
                    item.dataset.id = p.id;
                    item.innerHTML = `
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-body-emphasis">${escapeHtml(p.name)}</span>
                            ${p.serial_tracking ? '<span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-size:10px;">S/N Tracked</span>' : ''}
                        </div>
                        <div class="text-muted font-monospace small" style="font-size: 11px;">
                            SKU: ${escapeHtml(p.sku)} &bull; ${escapeHtml(p.category)}
                        </div>
                    `;
                    item.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        selectProduct(p);
                    });
                    dropdown.appendChild(item);
                });
            }

            function selectProduct(p) {
                searchInput.value = `${p.name} (SKU: ${p.sku})`;
                hiddenIdInput.value = p.id;
                dropdown.classList.add('d-none');

                if (p.serial_tracking) {
                    badgeContainer.innerHTML = '<span class="badge bg-info-subtle text-info border border-info-subtle font-monospace"><i class="bi bi-qr-code me-1"></i>Serial Number Tracking Active — Scan or enter barcodes below</span>';
                    badgeContainer.classList.remove('d-none');
                } else {
                    badgeContainer.innerHTML = '';
                    badgeContainer.classList.add('d-none');
                }
            }

            searchInput.addEventListener('focus', function () {
                renderDropdown(this.value);
                dropdown.classList.remove('d-none');
            });

            searchInput.addEventListener('click', function () {
                renderDropdown(this.value);
                dropdown.classList.remove('d-none');
            });

            searchInput.addEventListener('input', function () {
                hiddenIdInput.value = '';
                renderDropdown(this.value);
                dropdown.classList.remove('d-none');
            });

            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (dropdown.classList.contains('d-none')) {
                    searchInput.focus();
                    renderDropdown('');
                    dropdown.classList.remove('d-none');
                } else {
                    dropdown.classList.add('d-none');
                }
            });

            searchInput.addEventListener('blur', function () {
                setTimeout(() => {
                    dropdown.classList.add('d-none');
                    if (!hiddenIdInput.value) {
                        const exact = productsList.find(p => 
                            p.sku.toLowerCase() === searchInput.value.toLowerCase().trim() ||
                            p.name.toLowerCase() === searchInput.value.toLowerCase().trim()
                        );
                        if (exact) {
                            selectProduct(exact);
                        } else if (searchInput.value.trim() !== '') {
                            searchInput.value = '';
                        }
                    }
                }, 200);
            });

            // Serial numbers textarea listener: auto-syncs quantity
            serialsTextarea.addEventListener('input', function () {
                const text = this.value.trim();
                if (!text) {
                    serialsCountBadge.textContent = '0 serials';
                    serialsCountBadge.className = 'badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge';
                    return;
                }

                const serials = text.split(/[\r\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
                const count = serials.length;

                serialsCountBadge.textContent = `${count} serial${count === 1 ? '' : 's'}`;
                serialsCountBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle font-monospace serial-count-badge';

                if (count > 0) {
                    qtyInput.value = count;
                }
            });
        }

        function addProductRow() {
            const container = document.getElementById('productRowsContainer');
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = createProductRowHtml(rowIndex);
            const newRow = tempDiv.firstElementChild;
            container.appendChild(newRow);
            bindRowEvents(newRow);
            rowIndex++;
        }

        function removeProductRow(btn) {
            const rows = document.querySelectorAll('.product-row');
            if (rows.length <= 1) {
                const row = rows[0];
                row.querySelector('.product-search-input').value = '';
                row.querySelector('.product-id-input').value = '';
                row.querySelector('.qty-input').value = '1';
                row.querySelector('.serials-textarea').value = '';
                row.querySelector('.serial-count-badge').textContent = '0 serials';
                row.querySelector('.selected-product-badge').classList.add('d-none');
                return;
            }
            btn.closest('.product-row').remove();
        }

        function updateQtyLabels(type) {
            let labelText = 'Quantity (+ units) *';
            if (type === 'subtraction') labelText = 'Quantity to Deduct (-) *';
            else if (type === 'correction') labelText = 'New Count Quantity (=) *';

            document.querySelectorAll('.qty-label').forEach(el => {
                el.innerText = labelText;
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            addProductRow();
        });
    </script>
</x-app-layout>

