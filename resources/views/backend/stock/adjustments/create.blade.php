<x-app-layout>
    <style>
        .serial-chip {
            background-color: var(--bs-tertiary-bg, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
            font-size: 11.5px;
        }
        .serial-chip:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .serial-chip.is-selected {
            background-color: rgba(220, 53, 69, 0.1) !important;
            border-color: #dc3545 !important;
            color: #b02a37 !important;
            font-weight: 600;
            box-shadow: 0 0 0 1px rgba(220, 53, 69, 0.25);
        }
        .correction-chip.is-selected {
            background-color: rgba(25, 135, 84, 0.1) !important;
            border-color: #198754 !important;
            color: #0f5132 !important;
            font-weight: 600;
            box-shadow: 0 0 0 1px rgba(25, 135, 84, 0.25);
        }
        .correction-chip:not(.is-selected) {
            background-color: rgba(220, 53, 69, 0.06) !important;
            border-color: rgba(220, 53, 69, 0.4) !important;
            color: #dc3545 !important;
        }
        .serial-chips-grid {
            max-height: 160px;
            overflow-y: auto;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 6px;
            padding: 8px;
            background-color: var(--bs-body-bg, #ffffff);
        }
        .serial-dropdown-menu {
            background-color: var(--bs-body-bg, #ffffff);
            border-color: var(--bs-border-color, #dee2e6);
        }
        .product-dropdown-item,
        .serial-dropdown-item {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .product-dropdown-item:hover,
        .serial-dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.08);
        }
        .serial-dropdown-item.is-selected {
            background-color: rgba(220, 53, 69, 0.06);
        }
        .cursor-pointer {
            cursor: pointer;
        }
        html[data-theme="dark"] .serial-dropdown-menu,
        html[data-bs-theme="dark"] .serial-dropdown-menu,
        html[data-theme="dark"] .product-dropdown-menu,
        html[data-bs-theme="dark"] .product-dropdown-menu {
            background-color: #0f172a;
            border-color: #334155;
        }
        html[data-theme="dark"] .serial-dropdown-item:hover,
        html[data-bs-theme="dark"] .serial-dropdown-item:hover,
        html[data-theme="dark"] .product-dropdown-item:hover,
        html[data-bs-theme="dark"] .product-dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.08);
        }
    </style>

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
                                <small class="text-muted">Select components to scan, write, upload files, or choose existing serial numbers to adjust stock.</small>
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
        const warehousesList = @json($warehouses->map(fn($w) => ['id' => $w->id, 'name' => $w->name, 'code' => $w->code]));
        const serialsFetchUrl = "{{ route('admin.stock.adjustments.product-serials') }}";
        const parseFileUrl = "{{ route('admin.serial-numbers.parse-preview') }}";
        const csrfToken = "{{ csrf_token() }}";

        let rowIndex = 0;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function createProductRowHtml(index, selectedProductId = '') {
            return `
                <div class="product-row border p-3 rounded bg-light-subtle mb-3 shadow-xs" data-row-index="${index}">
                    <div class="row g-2 align-items-center">
                        <!-- Product Component: Full Width Selection Combobox -->
                        <div class="col-11">
                            <label class="form-label small fw-bold text-dark mb-1">Product Component *</label>
                            <div class="product-picker-wrapper position-relative">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" 
                                           class="form-control form-control-sm product-search-input" 
                                           placeholder="Click to select or type to search component..." 
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

                        <!-- Delete Button -->
                        <div class="col-1 text-end pt-3">
                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeProductRow(this)" title="Remove Component">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                        <!-- Hidden Quantity Input (automatically managed by serial numbers) -->
                        <input type="hidden" name="products[${index}][quantity]" class="qty-input" value="0">

                        <!-- Dynamic Serial Numbers Box (Addition scanner/uploader or Subtraction picker) -->
                        <div class="col-12 mt-2 pt-2 border-top border-light-subtle serial-box">
                        </div>
                    </div>
                </div>
            `;
        }

        function handleFileUpload(file, statusSpan, onComplete) {
            if (!file) return;
            const fileName = file.name.toLowerCase();
            const ext = fileName.split('.').pop();

            statusSpan.innerHTML = `<span class="text-primary small"><span class="spinner-border spinner-border-sm me-1" role="status"></span>Reading ${escapeHtml(file.name)}...</span>`;

            if (ext === 'csv' || ext === 'txt') {
                const reader = new FileReader();
                reader.onload = function (evt) {
                    const raw = evt.target.result;
                    const lines = raw.split(/\r?\n/);
                    const serials = [];
                    const headerAliases = ['serial_number', 'serial', 'serial number', 'sn', 'barcode', 'product_sku', 'sku'];

                    for (let i = 0; i < lines.length; i++) {
                        let line = lines[i].trim();
                        if (!line) continue;
                        if (i === 0 && line.charCodeAt(0) === 0xFEFF) {
                            line = line.slice(1).trim();
                        }

                        let col0 = line;
                        if (ext === 'csv') {
                            if (line.startsWith('"')) {
                                const endIdx = line.indexOf('"', 1);
                                col0 = endIdx !== -1 ? line.substring(1, endIdx) : line;
                            } else if (line.includes(',')) {
                                col0 = line.split(',')[0];
                            } else if (line.includes('\t')) {
                                col0 = line.split('\t')[0];
                            } else if (line.includes(';')) {
                                col0 = line.split(';')[0];
                            }
                        }
                        col0 = col0.replace(/["']/g, '').trim();

                        if (i === 0 && headerAliases.includes(col0.toLowerCase().replace(/[\s\-_]/g, '_'))) {
                            continue;
                        }

                        if (col0) {
                            serials.push(col0);
                        }
                    }

                    if (serials.length > 0) {
                        onComplete(serials, `Loaded ${serials.length} serials from ${file.name}`);
                    } else {
                        uploadFileToServer(file, statusSpan, onComplete);
                    }
                };
                reader.onerror = function () {
                    uploadFileToServer(file, statusSpan, onComplete);
                };
                reader.readAsText(file);
            } else if (ext === 'json') {
                const reader = new FileReader();
                reader.onload = function (evt) {
                    try {
                        const parsed = JSON.parse(evt.target.result);
                        const list = Array.isArray(parsed) ? parsed : (parsed.serials || parsed.items || []);
                        const serials = [];
                        list.forEach(item => {
                            const s = typeof item === 'string' ? item : (item.serial_number || item.serial || item.sn || '');
                            if (s) serials.push(String(s).trim());
                        });
                        onComplete(serials, `Loaded ${serials.length} serials from ${file.name}`);
                    } catch (e) {
                        uploadFileToServer(file, statusSpan, onComplete);
                    }
                };
                reader.onerror = function () {
                    uploadFileToServer(file, statusSpan, onComplete);
                };
                reader.readAsText(file);
            } else {
                uploadFileToServer(file, statusSpan, onComplete);
            }
        }

        function uploadFileToServer(file, statusSpan, onComplete) {
            statusSpan.innerHTML = `<span class="text-primary small"><span class="spinner-border spinner-border-sm me-1" role="status"></span>Parsing ${escapeHtml(file.name)} on server...</span>`;

            const formData = new FormData();
            formData.append('file', file);

            fetch(parseFileUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.serials && data.serials.length > 0) {
                    onComplete(data.serials, `Loaded ${data.serials.length} serials from ${file.name}`);
                } else {
                    statusSpan.innerHTML = `<span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>${escapeHtml(data.message || 'No serial numbers found in file.')}</span>`;
                }
            })
            .catch(err => {
                console.error(err);
                statusSpan.innerHTML = `<span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>Failed to parse file on server.</span>`;
            });
        }

        function renderSerialBox(rowEl) {
            const index = rowEl.dataset.rowIndex;
            const type = document.getElementById('adjustmentType').value;
            const warehouseSelect = document.getElementById('warehouseSelect');
            const defaultWarehouseId = warehouseSelect ? warehouseSelect.value : '';
            const currentWarehouseId = rowEl._currentWarehouseId || defaultWarehouseId;
            const hiddenIdInput = rowEl.querySelector('.product-id-input');
            const productId = hiddenIdInput ? hiddenIdInput.value : '';
            const serialBox = rowEl.querySelector('.serial-box');
            const qtyInput = rowEl.querySelector('.qty-input');

            if (!serialBox) return;

            if (type === 'subtraction') {
                if (!productId) {
                    serialBox.innerHTML = `
                        <div class="p-2 border rounded bg-body-tertiary text-muted small fst-italic">
                            <i class="bi bi-info-circle me-1 text-primary"></i> Select a product component above to view and select available serial numbers in this warehouse to deduct.
                        </div>
                        <textarea name="products[${index}][serials]" class="serials-textarea d-none"></textarea>
                    `;
                    qtyInput.value = 0;
                    return;
                }

                // If serials haven't been fetched yet for this (productId, currentWarehouseId)
                if (!rowEl._loadedFor || rowEl._loadedFor.productId !== productId || rowEl._loadedFor.warehouseId !== currentWarehouseId) {
                    serialBox.innerHTML = `
                        <div class="py-2 px-3 border rounded bg-body-tertiary text-primary small d-flex align-items-center">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span>Loading available serial numbers from warehouse...</span>
                        </div>
                        <textarea name="products[${index}][serials]" class="serials-textarea d-none"></textarea>
                    `;
                    qtyInput.value = 0;

                    const url = `${serialsFetchUrl}?product_id=${encodeURIComponent(productId)}&warehouse_id=${encodeURIComponent(currentWarehouseId)}`;
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            rowEl._loadedSerials = (data && data.serials) ? data.serials : [];
                            rowEl._loadedSummary = (data && data.warehouses_summary) ? data.warehouses_summary : [];
                            rowEl._loadedTotalAll = data.total_in_stock_all || 0;
                            rowEl._loadedWarehouseName = (data && data.warehouse_name) ? data.warehouse_name : 'Warehouse';
                            rowEl._loadedFor = { productId, warehouseId: currentWarehouseId };
                            renderSerialBox(rowEl);
                        })
                        .catch(err => {
                            console.error('Error fetching serials:', err);
                            rowEl._loadedSerials = [];
                            rowEl._loadedSummary = [];
                            rowEl._loadedTotalAll = 0;
                            rowEl._loadedWarehouseName = 'Warehouse';
                            rowEl._loadedFor = { productId, warehouseId: currentWarehouseId };
                            renderSerialBox(rowEl);
                        });
                    return;
                }

                // Serials have been loaded
                const serials = rowEl._loadedSerials || [];
                const summary = rowEl._loadedSummary || [];
                const totalAll = rowEl._loadedTotalAll || 0;
                const currentWarehouseName = rowEl._loadedWarehouseName || 'Warehouse';

                if (serials.length === 0) {
                    serialBox.innerHTML = `
                        <div class="serial-selector-wrapper">
                            <div class="alert alert-warning py-2 px-3 mb-0 small d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>
                                    <span>No in-stock serial numbers found for this product in <strong>${escapeHtml(currentWarehouseName)}</strong>.</span>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 btn-refresh-serials" title="Refresh serials">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <textarea name="products[${index}][serials]" class="serials-textarea d-none"></textarea>
                        </div>
                    `;
                    qtyInput.value = 0;

                    const refreshBtn = serialBox.querySelector('.btn-refresh-serials');
                    if (refreshBtn) {
                        refreshBtn.addEventListener('click', () => {
                            rowEl._loadedFor = null;
                            renderSerialBox(rowEl);
                        });
                    }
                    return;
                }

                // Render serial search & textarea list for subtraction
                serialBox.innerHTML = `
                    <div class="serial-selector-wrapper">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-danger-subtle text-danger font-monospace small">
                                    <i class="bi bi-check2-square me-1"></i>Serial Numbers to Deduct
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-link btn-sm p-0 text-danger select-all-serials-btn text-decoration-none" style="font-size: 11.5px;">Add All (${serials.length})</button>
                                <span class="text-muted">&bull;</span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-secondary deselect-all-serials-btn text-decoration-none" style="font-size: 11.5px;">Clear</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 btn-refresh-serials ms-1" title="Refresh serials from warehouse">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Search or Select Serial Number Input Box -->
                        <div class="serial-picker-wrapper position-relative mb-2">
                            <label class="form-label small fw-semibold text-body mb-1">Search or Select Serial Number to Deduct:</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" 
                                       class="form-control form-control-sm serial-search-input" 
                                       placeholder="Click to select or type to search serial numbers in warehouse..." 
                                       autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary btn-sm serial-picker-toggle" tabindex="-1" title="Browse available serial numbers">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>

                            <!-- Floating Dropdown Menu for Serial Numbers -->
                            <div class="serial-dropdown-menu shadow rounded-2 border position-absolute w-100 mt-1 d-none" 
                                 style="max-height: 220px; overflow-y: auto; z-index: 1040;">
                            </div>
                        </div>

                        <!-- Selected Serials Textarea (Line by Line) -->
                        <div class="pt-2 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small text-muted mb-0 font-monospace" style="font-size: 11.5px;">Selected Serials to Deduct (List line by line):</label>
                                <span class="badge bg-secondary font-monospace serial-count-badge py-1 px-2" style="font-size: 11.5px;">
                                    <i class="bi bi-dash-circle me-1"></i>0 units (-0)
                                </span>
                            </div>
                            <textarea name="products[${index}][serials]" rows="4" class="form-control form-control-sm font-monospace serials-textarea" placeholder="Selected serial numbers to deduct will appear here line by line...&#10;You can also type, paste, or scan barcodes directly."></textarea>
                            <div class="form-text small text-muted" style="font-size: 11px;">
                                Selecting serials from the input box above adds them here line by line. Deduction quantity auto-updates.
                            </div>
                        </div>
                    </div>
                `;

                const serialSearchInput = serialBox.querySelector('.serial-search-input');
                const serialPickerToggle = serialBox.querySelector('.serial-picker-toggle');
                const serialDropdown = serialBox.querySelector('.serial-dropdown-menu');
                const serialsTextarea = serialBox.querySelector('.serials-textarea');
                const selectAllBtn = serialBox.querySelector('.select-all-serials-btn');
                const deselectAllBtn = serialBox.querySelector('.deselect-all-serials-btn');
                const refreshBtn = serialBox.querySelector('.btn-refresh-serials');
                const countBadge = serialBox.querySelector('.serial-count-badge');
                const counterSpan = serialBox.querySelector('.selected-serials-counter');

                function getTextareaSerials() {
                    if (!serialsTextarea) return [];
                    const text = (serialsTextarea.value || '').trim();
                    if (!text) return [];
                    return text.split(/[\r\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
                }

                function syncCountsFromTextarea() {
                    const list = getTextareaSerials();
                    const unique = Array.from(new Set(list));
                    const count = unique.length;
                    qtyInput.value = count;
                    if (counterSpan) {
                        counterSpan.textContent = `(${count} / ${serials.length} units to deduct)`;
                    }
                    if (countBadge) {
                        if (count > 0) {
                            countBadge.className = 'badge bg-danger font-monospace serial-count-badge py-1 px-2';
                            countBadge.innerHTML = `<i class="bi bi-dash-circle me-1"></i>${count} unit${count === 1 ? '' : 's'} (-${count})`;
                        } else {
                            countBadge.className = 'badge bg-secondary font-monospace serial-count-badge py-1 px-2';
                            countBadge.innerHTML = `<i class="bi bi-dash-circle me-1"></i>0 units (-0)`;
                        }
                    }
                }

                function toggleSerialInTextarea(sn) {
                    if (!serialsTextarea) return;
                    const current = getTextareaSerials();
                    const snUpper = sn.trim().toUpperCase();
                    let updated;
                    const exists = current.some(s => s.toUpperCase() === snUpper);
                    if (exists) {
                        updated = current.filter(s => s.toUpperCase() !== snUpper);
                    } else {
                        updated = [...current, sn.trim()];
                    }
                    serialsTextarea.value = updated.join('\n');
                    syncCountsFromTextarea();
                }

                function renderSerialDropdown(filterText = '') {
                    if (!serialDropdown) return;
                    const query = (filterText || '').toLowerCase().trim();
                    const currentList = getTextareaSerials().map(s => s.toUpperCase());

                    const matched = serials.filter(s => {
                        if (!query) return true;
                        return s.serial_number && s.serial_number.toLowerCase().includes(query);
                    });

                    serialDropdown.innerHTML = '';
                    if (serials.length === 0) {
                        serialDropdown.innerHTML = '<div class="p-3 text-center text-muted small"><i class="bi bi-info-circle me-1"></i>No serial numbers available in this warehouse</div>';
                        return;
                    }

                    if (matched.length === 0) {
                        serialDropdown.innerHTML = `<div class="p-3 text-center text-muted small">No serials match "${escapeHtml(filterText)}"</div>`;
                        return;
                    }

                    matched.forEach(s => {
                        const sn = s.serial_number;
                        const isAdded = currentList.includes(sn.toUpperCase());

                        const item = document.createElement('div');
                        item.className = `serial-dropdown-item px-3 py-2 border-bottom d-flex align-items-center justify-content-between ${isAdded ? 'is-selected bg-danger-subtle' : ''}`;
                        item.innerHTML = `
                            <span class="font-monospace ${isAdded ? 'fw-bold text-danger' : 'text-body-emphasis'}">
                                <i class="bi bi-upc me-2 text-muted"></i>${escapeHtml(sn)}
                            </span>
                            ${isAdded 
                                ? '<span class="badge bg-danger-subtle text-danger small font-monospace"><i class="bi bi-check-lg me-1"></i>Selected (click to remove)</span>' 
                                : '<span class="badge bg-secondary-subtle text-body-secondary small font-monospace"><i class="bi bi-plus me-1"></i>Select</span>'
                            }
                        `;

                        item.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            toggleSerialInTextarea(sn);
                            renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                        });

                        serialDropdown.appendChild(item);
                    });
                }

                if (serialSearchInput) {
                    serialSearchInput.addEventListener('focus', function () {
                        renderSerialDropdown(this.value);
                        if (serialDropdown) serialDropdown.classList.remove('d-none');
                    });

                    serialSearchInput.addEventListener('click', function () {
                        renderSerialDropdown(this.value);
                        if (serialDropdown) serialDropdown.classList.remove('d-none');
                    });

                    serialSearchInput.addEventListener('input', function () {
                        renderSerialDropdown(this.value);
                        if (serialDropdown) serialDropdown.classList.remove('d-none');
                    });

                    serialSearchInput.addEventListener('keydown', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const query = (this.value || '').trim().toLowerCase();
                            if (!query) return;
                            const match = serials.find(s => s.serial_number.toLowerCase() === query) ||
                                          serials.find(s => s.serial_number.toLowerCase().includes(query));
                            if (match) {
                                toggleSerialInTextarea(match.serial_number);
                                this.value = '';
                                renderSerialDropdown('');
                            }
                        }
                    });

                    serialSearchInput.addEventListener('blur', function () {
                        setTimeout(() => {
                            if (serialDropdown) serialDropdown.classList.add('d-none');
                        }, 200);
                    });
                }

                if (serialPickerToggle) {
                    serialPickerToggle.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (serialDropdown && serialDropdown.classList.contains('d-none')) {
                            if (serialSearchInput) serialSearchInput.focus();
                            renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                            serialDropdown.classList.remove('d-none');
                        } else if (serialDropdown) {
                            serialDropdown.classList.add('d-none');
                        }
                    });
                }

                if (selectAllBtn) {
                    selectAllBtn.addEventListener('click', function () {
                        const allSerials = serials.map(s => s.serial_number);
                        if (serialsTextarea) {
                            serialsTextarea.value = allSerials.join('\n');
                        }
                        syncCountsFromTextarea();
                        if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                            renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                        }
                    });
                }

                if (deselectAllBtn) {
                    deselectAllBtn.addEventListener('click', function () {
                        if (serialsTextarea) {
                            serialsTextarea.value = '';
                        }
                        syncCountsFromTextarea();
                        if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                            renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                        }
                    });
                }

                if (refreshBtn) {
                    refreshBtn.addEventListener('click', function () {
                        rowEl._loadedFor = null;
                        renderSerialBox(rowEl);
                    });
                }

                if (serialsTextarea) {
                    serialsTextarea.addEventListener('input', function () {
                        syncCountsFromTextarea();
                        if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                            renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                        }
                    });
                }

                // Initial sync
                syncCountsFromTextarea();
            } else if (type === 'correction') {
                if (!productId) {
                    serialBox.innerHTML = `
                        <div class="p-2 border rounded bg-body-tertiary text-muted small fst-italic">
                            <i class="bi bi-info-circle me-1 text-warning"></i> Select a product component above to view and reconcile serial numbers for count correction in this warehouse.
                        </div>
                        <textarea name="products[${index}][serials]" class="serials-textarea d-none"></textarea>
                        <textarea name="products[${index}][remove_serials]" class="remove-serials-textarea d-none"></textarea>
                        <textarea name="products[${index}][new_serials]" class="new-serials-textarea d-none"></textarea>
                    `;
                    qtyInput.value = 0;
                    return;
                }

                // If serials haven't been fetched yet for this (productId, currentWarehouseId)
                if (!rowEl._loadedFor || rowEl._loadedFor.productId !== productId || rowEl._loadedFor.warehouseId !== currentWarehouseId) {
                    serialBox.innerHTML = `
                        <div class="py-2 px-3 border rounded bg-body-tertiary text-primary small d-flex align-items-center">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            <span>Loading available serial numbers from warehouse...</span>
                        </div>
                        <textarea name="products[${index}][serials]" class="serials-textarea d-none"></textarea>
                        <textarea name="products[${index}][remove_serials]" class="remove-serials-textarea d-none"></textarea>
                        <textarea name="products[${index}][new_serials]" class="new-serials-textarea d-none"></textarea>
                    `;
                    qtyInput.value = 0;

                    const url = `${serialsFetchUrl}?product_id=${encodeURIComponent(productId)}&warehouse_id=${encodeURIComponent(currentWarehouseId)}`;
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            rowEl._loadedSerials = (data && data.serials) ? data.serials : [];
                            rowEl._loadedSummary = (data && data.warehouses_summary) ? data.warehouses_summary : [];
                            rowEl._loadedTotalAll = data.total_in_stock_all || 0;
                            rowEl._loadedWarehouseName = (data && data.warehouse_name) ? data.warehouse_name : 'Warehouse';
                            rowEl._loadedFor = { productId, warehouseId: currentWarehouseId };
                            renderSerialBox(rowEl);
                        })
                        .catch(err => {
                            console.error('Error fetching serials:', err);
                            rowEl._loadedSerials = [];
                            rowEl._loadedSummary = [];
                            rowEl._loadedTotalAll = 0;
                            rowEl._loadedWarehouseName = 'Warehouse';
                            rowEl._loadedFor = { productId, warehouseId: currentWarehouseId };
                            renderSerialBox(rowEl);
                        });
                    return;
                }

                const serials = rowEl._loadedSerials || [];
                const summary = rowEl._loadedSummary || [];
                const totalAll = rowEl._loadedTotalAll || 0;
                const currentWarehouseName = rowEl._loadedWarehouseName || 'Warehouse';

                if (serials.length === 0) {
                    serialBox.innerHTML = `
                        <div class="serial-correction-wrapper">
                            <div class="alert alert-warning py-2 px-3 mb-2 small d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>
                                    <span>No in-stock serial numbers recorded for this product in <strong>${escapeHtml(currentWarehouseName)}</strong>. Scan or enter counted serial numbers below to establish inventory.</span>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 btn-refresh-serials" title="Refresh serials">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                <div>
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="bi bi-upc-scan text-primary me-1"></i> Counted Serial Numbers
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge py-1 px-2" style="font-size: 12px;">
                                    <i class="bi bi-box-seam me-1"></i>Count: = 0 units
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <label class="btn btn-outline-primary btn-sm py-1 px-2 cursor-pointer mb-0 file-upload-btn" style="font-size: 12px;">
                                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload File (.csv, .xlsx, .txt, .json)
                                    <input type="file" class="d-none serial-file-input" accept=".csv,.txt,.xlsx,.xls,.json">
                                </label>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 btn-clear-serials" style="font-size: 12px;">
                                    <i class="bi bi-x-circle me-1"></i> Clear Text
                                </button>
                                <span class="file-upload-status text-muted small font-monospace" style="font-size: 11px;"></span>
                            </div>

                            <textarea name="products[${index}][serials]" rows="3" class="form-control form-control-sm font-monospace serials-textarea" placeholder="Enter counted serial numbers (1 per line or comma-separated)..."></textarea>
                            <textarea name="products[${index}][remove_serials]" class="remove-serials-textarea d-none"></textarea>
                            <textarea name="products[${index}][new_serials]" class="new-serials-textarea d-none"></textarea>
                        </div>
                    `;

                    const refreshBtn = serialBox.querySelector('.btn-refresh-serials');
                    if (refreshBtn) {
                        refreshBtn.addEventListener('click', () => {
                            rowEl._loadedFor = null;
                            renderSerialBox(rowEl);
                        });
                    }

                    const textarea = serialBox.querySelector('.serials-textarea');
                    const badge = serialBox.querySelector('.serial-count-badge');
                    const fileInput = serialBox.querySelector('.serial-file-input');
                    const btnClear = serialBox.querySelector('.btn-clear-serials');
                    const statusSpan = serialBox.querySelector('.file-upload-status');

                    function syncEmptyCorrectionCount() {
                        const text = textarea.value.trim();
                        const list = text ? text.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean) : [];
                        const count = list.length;
                        qtyInput.value = count;
                        if (count > 0) {
                            badge.innerHTML = `<i class="bi bi-box-seam me-1"></i>Count: = ${count} units`;
                            badge.className = 'badge bg-primary font-monospace serial-count-badge py-1 px-2';
                        } else {
                            badge.innerHTML = `<i class="bi bi-box-seam me-1"></i>Count: = 0 units`;
                            badge.className = 'badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge py-1 px-2';
                        }
                    }

                    textarea.addEventListener('input', syncEmptyCorrectionCount);

                    fileInput.addEventListener('change', function (e) {
                        const file = e.target.files && e.target.files[0];
                        if (!file) return;
                        handleFileUpload(file, statusSpan, (extractedSerials, message) => {
                            const existingText = textarea.value.trim();
                            const existing = existingText ? existingText.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean) : [];
                            const combined = [...new Set([...existing, ...extractedSerials])];
                            textarea.value = combined.join('\n');
                            syncEmptyCorrectionCount();
                            statusSpan.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>${escapeHtml(message)}</span>`;
                            fileInput.value = '';
                        });
                    });

                    btnClear.addEventListener('click', function () {
                        textarea.value = '';
                        statusSpan.innerHTML = '';
                        syncEmptyCorrectionCount();
                    });

                    syncEmptyCorrectionCount();
                    return;
                }

                // Warehouse has existing serials -> render Count Correction Reconciler
                serialBox.innerHTML = `
                    <div class="serial-correction-wrapper">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div>
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="bi bi-sliders text-warning me-1"></i> Physical Count Reconciliation
                                </label>
                                <span class="text-muted small ms-1" style="font-size: 11px;">(Checked = Present on shelf; Unchecked = Removed as missing)</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-primary font-monospace serial-count-badge py-1 px-2" style="font-size: 12px;">
                                    Count: = ${serials.length} units
                                </span>
                                <span class="badge bg-secondary font-monospace serial-remove-badge py-1 px-2" style="font-size: 12px;">
                                    Removing: 0 units
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <span class="input-group-text py-0"><i class="bi bi-funnel" style="font-size: 11px;"></i></span>
                                <input type="text" class="form-control form-control-sm serial-filter-input" placeholder="Filter serials..." autocomplete="off">
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 btn-keep-all" style="font-size: 12px;" title="Keep all units in stock">
                                <i class="bi bi-check-all me-1"></i> Keep All (${serials.length})
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove-all" style="font-size: 12px;" title="Uncheck all (remove all)">
                                <i class="bi bi-x-lg me-1"></i> Remove All
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1 btn-refresh-serials ms-auto" title="Refresh serials from warehouse">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>

                        <div class="serial-chips-grid d-flex flex-wrap gap-1 mb-2">
                            ${serials.map(s => `
                                <label class="serial-chip correction-chip is-selected d-inline-flex align-items-center gap-1 px-2 py-1 rounded text-body font-monospace user-select-none mb-0 cursor-pointer" 
                                       title="Warehouse: ${escapeHtml(s.warehouse_name)} (${escapeHtml(s.warehouse_code)}) | Inbound: ${escapeHtml(s.inbound_date || 'N/A')} | Cost: $${escapeHtml(s.cost_price || '0.00')}">
                                    <input type="checkbox" class="form-check-input mt-0 serial-checkbox" value="${escapeHtml(s.serial_number)}" data-warehouse-id="${s.warehouse_id}" checked>
                                    <span class="badge bg-secondary-subtle text-body-secondary border px-1 py-0" style="font-size: 9.5px; letter-spacing: 0.5px;">${escapeHtml(s.warehouse_code)}</span>
                                    <span class="serial-label-text">${escapeHtml(s.serial_number)}</span>
                                </label>
                            `).join('')}
                        </div>

                        <!-- Optional: Add newly discovered serials during physical audit -->
                        <div class="p-2 border rounded bg-body-tertiary mb-1">
                            <div class="d-flex align-items-center justify-content-between mb-1 flex-wrap gap-1">
                                <span class="small fw-semibold text-dark" style="font-size: 11.5px;">
                                    <i class="bi bi-plus-circle text-primary me-1"></i> Found Untracked Units? Add New Serials (Optional)
                                </span>
                                <div class="d-flex align-items-center gap-1">
                                    <label class="btn btn-outline-primary btn-sm py-0 px-2 cursor-pointer mb-0 file-upload-btn" style="font-size: 11px;" title="Upload file">
                                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload
                                        <input type="file" class="d-none serial-file-input" accept=".csv,.txt,.xlsx,.xls,.json">
                                    </label>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 btn-clear-new-serials" style="font-size: 11px;">Clear</button>
                                </div>
                            </div>
                            <textarea class="form-control form-control-sm font-monospace new-serials-textarea" rows="2" placeholder="Scan or enter any newly discovered serials found during audit (1 per line)..."></textarea>
                            <span class="file-upload-status text-muted small font-monospace d-block mt-1" style="font-size: 11px;"></span>
                        </div>

                        <textarea name="products[${index}][serials]" class="serials-textarea d-none"></textarea>
                        <textarea name="products[${index}][remove_serials]" class="remove-serials-textarea d-none"></textarea>
                        <textarea name="products[${index}][new_serials]" class="new-serials-textarea-hidden d-none"></textarea>
                    </div>
                `;

                const filterInput = serialBox.querySelector('.serial-filter-input');
                const keepAllBtn = serialBox.querySelector('.btn-keep-all');
                const removeAllBtn = serialBox.querySelector('.btn-remove-all');
                const refreshBtn = serialBox.querySelector('.btn-refresh-serials');
                const checkboxes = serialBox.querySelectorAll('.serial-checkbox');
                const serialsTextarea = serialBox.querySelector('.serials-textarea');
                const removeSerialsTextarea = serialBox.querySelector('.remove-serials-textarea');
                const newSerialsHidden = serialBox.querySelector('.new-serials-textarea-hidden');
                const newSerialsTextarea = serialBox.querySelector('.new-serials-textarea');
                const countBadge = serialBox.querySelector('.serial-count-badge');
                const removeBadge = serialBox.querySelector('.serial-remove-badge');
                const fileInput = serialBox.querySelector('.serial-file-input');
                const clearNewBtn = serialBox.querySelector('.btn-clear-new-serials');
                const statusSpan = serialBox.querySelector('.file-upload-status');

                function updateCorrectionSelection() {
                    const keptList = [];
                    const removeList = [];

                    checkboxes.forEach(cb => {
                        const chip = cb.closest('.serial-chip');
                        const textSpan = chip.querySelector('.serial-label-text');
                        if (cb.checked) {
                            chip.classList.add('is-selected');
                            if (textSpan) textSpan.classList.remove('text-decoration-line-through', 'opacity-50');
                            keptList.push(cb.value);
                            if (currentWarehouseId === 'all' && cb.dataset.warehouseId) {
                                const mainWh = document.getElementById('warehouseSelect');
                                if (mainWh) mainWh.value = cb.dataset.warehouseId;
                            }
                        } else {
                            chip.classList.remove('is-selected');
                            if (textSpan) textSpan.classList.add('text-decoration-line-through', 'opacity-50');
                            removeList.push(cb.value);
                        }
                    });

                    // Parse new serials
                    const newText = newSerialsTextarea.value.trim();
                    const newSerialsList = newText ? newText.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean) : [];

                    const totalCount = keptList.length + newSerialsList.length;
                    qtyInput.value = totalCount;

                    serialsTextarea.value = keptList.join('\n');
                    removeSerialsTextarea.value = removeList.join('\n');
                    newSerialsHidden.value = newSerialsList.join('\n');

                    countBadge.innerHTML = `<i class="bi bi-box-seam me-1"></i>Count: = ${totalCount} units`;
                    if (removeList.length > 0) {
                        removeBadge.className = 'badge bg-danger font-monospace serial-remove-badge py-1 px-2';
                        removeBadge.innerHTML = `<i class="bi bi-dash-circle me-1"></i>Removing: ${removeList.length} units`;
                    } else {
                        removeBadge.className = 'badge bg-secondary-subtle text-body-secondary border font-monospace serial-remove-badge py-1 px-2';
                        removeBadge.innerHTML = `Removing: 0 units`;
                    }
                }

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updateCorrectionSelection);
                });

                filterInput.addEventListener('input', function () {
                    const query = this.value.toLowerCase().trim();
                    serialBox.querySelectorAll('.serial-chip').forEach(chip => {
                        const text = chip.textContent.toLowerCase();
                        if (!query || text.includes(query)) {
                            chip.classList.remove('d-none');
                        } else {
                            chip.classList.add('d-none');
                        }
                    });
                });

                keepAllBtn.addEventListener('click', function () {
                    serialBox.querySelectorAll('.serial-chip:not(.d-none) .serial-checkbox').forEach(cb => {
                        cb.checked = true;
                    });
                    updateCorrectionSelection();
                });

                removeAllBtn.addEventListener('click', function () {
                    serialBox.querySelectorAll('.serial-chip:not(.d-none) .serial-checkbox').forEach(cb => {
                        cb.checked = false;
                    });
                    updateCorrectionSelection();
                });

                refreshBtn.addEventListener('click', function () {
                    rowEl._loadedFor = null;
                    renderSerialBox(rowEl);
                });

                newSerialsTextarea.addEventListener('input', updateCorrectionSelection);

                fileInput.addEventListener('change', function (e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    handleFileUpload(file, statusSpan, (extractedSerials, message) => {
                        const existingText = newSerialsTextarea.value.trim();
                        const existing = existingText ? existingText.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean) : [];
                        const combined = [...new Set([...existing, ...extractedSerials])];
                        newSerialsTextarea.value = combined.join('\n');
                        updateCorrectionSelection();
                        statusSpan.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>${escapeHtml(message)}</span>`;
                        fileInput.value = '';
                    });
                });

                clearNewBtn.addEventListener('click', function () {
                    newSerialsTextarea.value = '';
                    statusSpan.innerHTML = '';
                    updateCorrectionSelection();
                });

                // Initial sync
                updateCorrectionSelection();
            } else {
                // Addition mode: Scan, type, or upload
                serialBox.innerHTML = `
                    <div class="serial-addition-wrapper">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <div>
                                <label class="form-label small fw-bold text-dark mb-0">
                                    <i class="bi bi-upc-scan text-primary me-1"></i> Add Serial Numbers
                                </label>
                            </div>
                            <span class="badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge py-1 px-2" style="font-size: 12px;">
                                <i class="bi bi-box-seam me-1"></i>0 units (+0)
                            </span>
                        </div>

                        <!-- Action Toolbar: Upload file button, Clear button, File status -->
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <label class="btn btn-outline-primary btn-sm py-1 px-2 cursor-pointer mb-0 file-upload-btn" style="font-size: 12px;" title="Upload CSV, XLSX, TXT, or JSON file">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload File (.csv, .xlsx, .txt, .json)
                                <input type="file" class="d-none serial-file-input" accept=".csv,.txt,.xlsx,.xls,.json">
                            </label>

                            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 btn-clear-serials" style="font-size: 12px;">
                                <i class="bi bi-x-circle me-1"></i> Clear Text
                            </button>

                            <span class="file-upload-status text-muted small font-monospace" style="font-size: 11px;"></span>
                        </div>

                        <textarea name="products[${index}][serials]" 
                                  rows="3" 
                                  class="form-control form-control-sm font-monospace serials-textarea" 
                                  placeholder="Scan barcode or enter serial numbers (1 per line or comma-separated)...&#10;e.g. SN-AMD-10001&#10;SN-AMD-10002 (quantity auto-updates)"></textarea>
                    </div>
                `;

                const textarea = serialBox.querySelector('.serials-textarea');
                const badge = serialBox.querySelector('.serial-count-badge');
                const fileInput = serialBox.querySelector('.serial-file-input');
                const btnClear = serialBox.querySelector('.btn-clear-serials');
                const statusSpan = serialBox.querySelector('.file-upload-status');

                function syncAdditionCount() {
                    const text = textarea.value.trim();
                    if (!text) {
                        badge.innerHTML = `<i class="bi bi-box-seam me-1"></i>0 units (+0)`;
                        badge.className = 'badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge py-1 px-2';
                        qtyInput.value = 0;
                        return;
                    }

                    const serials = text.split(/[\r\n,]+/).map(s => s.trim()).filter(s => s.length > 0);
                    const count = serials.length;
                    qtyInput.value = count;

                    if (count > 0) {
                        badge.innerHTML = `<i class="bi bi-box-seam me-1"></i>${count} unit${count === 1 ? '' : 's'} (+${count})`;
                        badge.className = 'badge bg-primary font-monospace serial-count-badge py-1 px-2';
                    } else {
                        badge.innerHTML = `<i class="bi bi-box-seam me-1"></i>0 units (+0)`;
                        badge.className = 'badge bg-secondary-subtle text-body-secondary font-monospace serial-count-badge py-1 px-2';
                    }
                }

                textarea.addEventListener('input', syncAdditionCount);

                fileInput.addEventListener('change', function (e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    handleFileUpload(file, statusSpan, (extractedSerials, message) => {
                        const existingText = textarea.value.trim();
                        const existing = existingText ? existingText.split(/[\r\n,]+/).map(s => s.trim()).filter(Boolean) : [];
                        const combined = [...new Set([...existing, ...extractedSerials])];
                        textarea.value = combined.join('\n');
                        syncAdditionCount();
                        statusSpan.innerHTML = `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>${escapeHtml(message)}</span>`;
                        fileInput.value = '';
                    });
                });

                btnClear.addEventListener('click', function () {
                    textarea.value = '';
                    statusSpan.innerHTML = '';
                    syncAdditionCount();
                });

                // Initial sync
                syncAdditionCount();
            }
        }

        function bindRowEvents(rowEl) {
            const wrapper = rowEl.querySelector('.product-picker-wrapper');
            const searchInput = wrapper.querySelector('.product-search-input');
            const toggleBtn = wrapper.querySelector('.product-picker-toggle');
            const hiddenIdInput = wrapper.querySelector('.product-id-input');
            const dropdown = wrapper.querySelector('.product-dropdown-menu');
            const badgeContainer = rowEl.querySelector('.selected-product-badge');

            // Initialize if pre-populated
            if (hiddenIdInput && hiddenIdInput.value) {
                const initialProd = productsList.find(p => String(p.id) === String(hiddenIdInput.value));
                if (initialProd) {
                    searchInput.value = `${initialProd.name} (SKU: ${initialProd.sku})`;
                }
            }

            function renderDropdown(filterText = '') {
                const query = (filterText || '').toLowerCase().trim();
                const matched = productsList.filter(p => {
                    if (!query) return true;
                    const fullStr = `${p.name} (sku: ${p.sku}) ${p.category || ''}`.toLowerCase();
                    return fullStr.includes(query) || 
                           query.includes(p.sku.toLowerCase()) ||
                           (p.name && p.name.toLowerCase().includes(query)) || 
                           (p.sku && p.sku.toLowerCase().includes(query)) || 
                           (p.category && p.category.toLowerCase().includes(query));
                });

                dropdown.innerHTML = '';
                if (matched.length === 0) {
                    dropdown.innerHTML = '<div class="p-3 text-center text-muted small">No components match your search</div>';
                    return;
                }

                matched.forEach(p => {
                    const isSelected = hiddenIdInput && String(hiddenIdInput.value) === String(p.id);
                    const item = document.createElement('div');
                    item.className = `product-dropdown-item px-3 py-2 border-bottom ${isSelected ? 'is-selected bg-primary-subtle' : ''}`;
                    item.dataset.id = p.id;
                    item.innerHTML = `
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-body-emphasis">${escapeHtml(p.name)}</span>
                            <div class="d-flex align-items-center gap-1">
                                ${p.serial_tracking ? '<span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-size:10px;">S/N Tracked</span>' : ''}
                                ${isSelected ? '<span class="badge bg-success-subtle text-success small font-monospace"><i class="bi bi-check-lg me-1"></i>Selected</span>' : ''}
                            </div>
                        </div>
                        <div class="text-muted font-monospace small" style="font-size: 11px;">
                            SKU: ${escapeHtml(p.sku)} &bull; ${escapeHtml(p.category || '')}
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

                if (badgeContainer) {
                    badgeContainer.innerHTML = '';
                    badgeContainer.classList.add('d-none');
                }

                // Reset cached serials for this row and render serial box
                rowEl._loadedFor = null;
                renderSerialBox(rowEl);
            }

            searchInput.addEventListener('focus', function () {
                if (hiddenIdInput && hiddenIdInput.value) {
                    renderDropdown('');
                    this.select();
                } else {
                    renderDropdown(this.value);
                }
                dropdown.classList.remove('d-none');
            });

            searchInput.addEventListener('click', function () {
                if (hiddenIdInput && hiddenIdInput.value) {
                    renderDropdown('');
                } else {
                    renderDropdown(this.value);
                }
                dropdown.classList.remove('d-none');
            });

            searchInput.addEventListener('input', function () {
                hiddenIdInput.value = '';
                renderDropdown(this.value);
                dropdown.classList.remove('d-none');
                rowEl._loadedFor = null;
                renderSerialBox(rowEl);
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
                        const val = searchInput.value.toLowerCase().trim();
                        const exact = productsList.find(p => 
                            p.sku.toLowerCase() === val ||
                            p.name.toLowerCase() === val ||
                            `${p.name} (sku: ${p.sku})`.toLowerCase() === val
                        );
                        if (exact) {
                            selectProduct(exact);
                        } else if (val !== '') {
                            searchInput.value = '';
                            rowEl._loadedFor = null;
                            renderSerialBox(rowEl);
                        }
                    } else {
                        const currentProd = productsList.find(p => String(p.id) === String(hiddenIdInput.value));
                        if (currentProd) {
                            searchInput.value = `${currentProd.name} (SKU: ${currentProd.sku})`;
                        }
                    }
                }, 200);
            });

            // Initial render of serial box for this row
            renderSerialBox(rowEl);
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
                row.querySelector('.qty-input').value = '0';
                row.querySelector('.selected-product-badge').classList.add('d-none');
                row._loadedFor = null;
                row._loadedSerials = [];
                renderSerialBox(row);
                return;
            }
            btn.closest('.product-row').remove();
        }

        function updateQtyLabels(type) {
            // Re-render serial box on all rows when adjustment type changes
            document.querySelectorAll('.product-row').forEach(row => {
                renderSerialBox(row);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Re-fetch serials on all rows when warehouse selection changes
            const warehouseSelect = document.getElementById('warehouseSelect');
            if (warehouseSelect) {
                warehouseSelect.addEventListener('change', function () {
                    document.querySelectorAll('.product-row').forEach(row => {
                        row._loadedFor = null;
                        renderSerialBox(row);
                    });
                });
            }

            addProductRow();
        });
    </script>
</x-app-layout>

