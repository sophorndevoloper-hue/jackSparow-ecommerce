<x-app-layout>
    <style>
        .serial-chip {
            background-color: var(--bs-tertiary-bg, #f8f9fa);
            border: 1px solid var(--bs-border-color, #dee2e6);
            cursor: pointer;
            transition: all 0.15s ease;
            font-size: 11.5px;
            user-select: none;
        }
        .serial-chip:hover {
            border-color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.08);
        }
        .serial-chip.is-selected {
            background-color: rgba(59, 130, 246, 0.15) !important;
            border-color: #3b82f6 !important;
            color: #2563eb !important;
            font-weight: 600;
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.35);
        }
        .serial-chips-grid {
            max-height: 160px;
            overflow-y: auto;
            border: 1px solid var(--bs-border-color, #dee2e6);
            border-radius: 6px;
            padding: 8px;
            background-color: var(--bs-body-bg, #ffffff);
        }
        .transfer-component-card {
            background-color: var(--admin-surface, #ffffff);
            border: 1px solid var(--admin-border, #dbe4ef);
            border-radius: 8px;
            transition: border-color 0.15s ease;
        }
        .product-dropdown-menu {
            background-color: var(--bs-body-bg, #ffffff);
            border-color: var(--bs-border-color, #dee2e6);
        }
        .product-dropdown-item {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .product-dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.08);
        }
        .cursor-pointer {
            cursor: pointer;
        }

        /* Dark Mode Theme Support */
        html[data-theme="dark"] .transfer-component-card,
        html[data-bs-theme="dark"] .transfer-component-card {
            background-color: #141c2b !important;
            border-color: #2f3b52 !important;
        }
        html[data-theme="dark"] .product-dropdown-menu,
        html[data-bs-theme="dark"] .product-dropdown-menu {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html[data-theme="dark"] .product-dropdown-item,
        html[data-bs-theme="dark"] .product-dropdown-item {
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] .product-dropdown-item:hover,
        html[data-bs-theme="dark"] .product-dropdown-item:hover {
            background-color: rgba(96, 165, 250, 0.15) !important;
        }
        .serial-dropdown-menu {
            background-color: var(--bs-body-bg, #ffffff);
            border-color: var(--bs-border-color, #dee2e6);
        }
        .serial-dropdown-item {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .serial-dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.08);
        }
        .serial-dropdown-item.is-selected {
            background-color: rgba(13, 110, 253, 0.12);
        }
        html[data-theme="dark"] .serial-dropdown-menu,
        html[data-bs-theme="dark"] .serial-dropdown-menu {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html[data-theme="dark"] .serial-dropdown-item,
        html[data-bs-theme="dark"] .serial-dropdown-item {
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }
        html[data-theme="dark"] .serial-dropdown-item:hover,
        html[data-bs-theme="dark"] .serial-dropdown-item:hover {
            background-color: rgba(96, 165, 250, 0.15) !important;
        }
        html[data-theme="dark"] .serial-dropdown-item.is-selected,
        html[data-bs-theme="dark"] .serial-dropdown-item.is-selected {
            background-color: rgba(96, 165, 250, 0.22) !important;
        }
        html[data-theme="dark"] .transfer-title,
        html[data-bs-theme="dark"] .transfer-title {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .text-dark,
        html[data-bs-theme="dark"] .text-dark {
            color: #f1f5f9 !important;
        }
    </style>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Logistics &amp; Movement</p>
                    <h1 class="h3 mb-1">New Stock Transfer</h1>
                    <p class="text-muted mb-0">Dispatch and shift hardware components from one warehouse facility to another with serial number tracking.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.stock.transfers.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Transfers
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.stock.transfers.store') }}" method="POST" id="stockTransferForm" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Left: Route Details -->
                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 transfer-title fw-bold">Transfer Route</h5>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Source Warehouse (From) *</label>
                            <select name="from_warehouse_id" id="fromWarehouse" required class="form-select">
                                <option value="">-- Select Source Facility --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)$selectedFromId === (string)$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Physical facility where stock and serials are currently located.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Destination Warehouse (To) *</label>
                            <select name="to_warehouse_id" id="toWarehouse" required class="form-select">
                                <option value="">-- Select Destination Facility --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (string)$selectedToId === (string)$wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Destination facility where items will be received.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Transfer Status *</label>
                            <select name="status" id="transferStatusSelect" required class="form-select">
                                <option value="completed" {{ old('status', 'completed') === 'completed' ? 'selected' : '' }}>
                                    Completed (Shift stock &amp; serials immediately)
                                </option>
                                <option value="in_transit" {{ old('status') === 'in_transit' ? 'selected' : '' }}>
                                    In Transit (Deduct from source, pending arrival)
                                </option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>
                                    Pending (Draft / Not yet dispatched)
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Transfer Date</label>
                            <input type="date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Transfer Notes / Waybill #</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Optional courier waybill, tracking ID, or notes...">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm" id="submitTransferBtn">
                            <i class="bi bi-arrow-right-circle me-1"></i> Process Stock Transfer
                        </button>
                    </div>
                </div>

                <!-- Right: Product Items & Serials -->
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <div>
                                <h5 class="mb-0 transfer-title fw-bold">Components to Transfer</h5>
                                <small class="text-muted">Select parts, match serial numbers in source warehouse, and confirm quantities.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTransferProductRow()">
                                <i class="bi bi-plus-lg me-1"></i> Add Part
                            </button>
                        </div>

                        <div id="transferRowsContainer" class="d-flex flex-column gap-3">
                            <!-- Dynamic Product Rows -->
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-3 py-2" onclick="addTransferProductRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Another Component
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const productsCatalog = @json($productsData);
        const serialFetchUrl = "{{ route('admin.stock.transfers.product-serials', [], false) }}";
        let rowCounter = 0;

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const fromWarehouseSelect = document.getElementById('fromWarehouse');
            const toWarehouseSelect = document.getElementById('toWarehouse');
            const container = document.getElementById('transferRowsContainer');

            // Add first initial row
            addTransferProductRow();

            // When source warehouse changes, refresh available serials in existing rows
            if (fromWarehouseSelect) {
                fromWarehouseSelect.addEventListener('change', function () {
                    const whId = this.value;
                    const rows = document.querySelectorAll('.transfer-component-card');
                    rows.forEach(function (row) {
                        const textarea = row.querySelector('.serials-textarea');
                        if (textarea) textarea.value = '';
                        const counterSpan = row.querySelector('.selected-serials-counter');
                        if (counterSpan) counterSpan.textContent = '(0 selected)';
                        const qtyInput = row.querySelector('.qty-input');
                        if (qtyInput) qtyInput.value = 0;
                        const transferQtyBadge = row.querySelector('.transfer-qty-badge');
                        if (transferQtyBadge) transferQtyBadge.textContent = 'Transfer: 0 units';
                        const serialSearchInput = row.querySelector('.serial-search-input');
                        if (serialSearchInput) serialSearchInput.value = '';

                        const productIdInput = row.querySelector('.product-id-input');
                        if (productIdInput && productIdInput.value) {
                            if (whId) {
                                fetchSerialsForRow(row, productIdInput.value);
                            } else {
                                const infoBar = row.querySelector('.stock-info-bar');
                                if (infoBar) infoBar.classList.add('d-none');
                                const serialSection = row.querySelector('.serial-section');
                                if (serialSection) serialSection.classList.add('d-none');
                            }
                        }
                    });
                });
            }

            // Client validation: source != destination and non-zero quantity
            document.getElementById('stockTransferForm').addEventListener('submit', function (e) {
                if (fromWarehouseSelect.value && toWarehouseSelect.value && fromWarehouseSelect.value === toWarehouseSelect.value) {
                    e.preventDefault();
                    alert('Source warehouse and Destination warehouse cannot be the same facility.');
                    toWarehouseSelect.focus();
                    return;
                }

                const rows = document.querySelectorAll('.transfer-component-card');
                for (let i = 0; i < rows.length; i++) {
                    const row = rows[i];
                    const productId = row.querySelector('.product-id-input')?.value;
                    const qty = parseInt(row.querySelector('.qty-input')?.value || '0', 10);
                    if (!productId) {
                        e.preventDefault();
                        alert('Please select a hardware component for all rows.');
                        row.querySelector('.product-search-input')?.focus();
                        return;
                    }
                    if (qty <= 0) {
                        e.preventDefault();
                        alert('Transfer quantity must be at least 1 unit. Please select or scan serial numbers for the chosen component.');
                        return;
                    }
                }
            });

            // Close all combobox dropdowns on outside click
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.product-picker-wrapper')) {
                    document.querySelectorAll('.product-dropdown-menu').forEach(m => m.classList.add('d-none'));
                }
                if (!e.target.closest('.serial-picker-wrapper')) {
                    document.querySelectorAll('.serial-dropdown-menu').forEach(m => m.classList.add('d-none'));
                }
            });
        });

        // Add a new component transfer row
        function addTransferProductRow() {
            const container = document.getElementById('transferRowsContainer');
            const index = rowCounter++;

            const card = document.createElement('div');
            card.className = 'transfer-component-card p-3 shadow-sm position-relative';
            card.dataset.index = index;

            card.innerHTML = `
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-11">
                        <label class="form-label small fw-bold mb-1">Hardware Part *</label>
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
                            <input type="hidden" name="products[${index}][product_id]" class="product-id-input" value="" required>
                            
                            <!-- Floating Search & Select Dropdown Menu -->
                            <div class="product-dropdown-menu shadow rounded-2 border position-absolute w-100 mt-1 d-none" 
                                 style="max-height: 240px; overflow-y: auto; z-index: 1050;">
                            </div>
                        </div>
                        <div class="selected-product-badge mt-1 d-none"></div>
                    </div>
                    <div class="col-1 text-end pt-3">
                        <button type="button" class="btn btn-outline-danger btn-sm p-1 remove-row-btn" title="Remove Component">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                    <!-- Hidden Quantity Input: automatically managed by the number of serial numbers -->
                    <input type="hidden" name="products[${index}][quantity]" class="qty-input" value="0">
                </div>

                <!-- Stock & Serial Status Notification Bar -->
                <div class="stock-info-bar small text-muted mb-2 d-none d-flex align-items-center flex-wrap gap-2">
                    <span class="badge bg-secondary-subtle text-body-secondary stock-badge">In Source WH: 0 units</span>
                    <span class="badge bg-primary-subtle text-primary serial-count-badge d-none">Tracked Serials: 0</span>
                    <span class="badge bg-primary font-monospace transfer-qty-badge ms-auto">Transfer: 0 units</span>
                </div>

                <!-- Untracked Product Quantity Section (Only shown if product does NOT have serial tracking) -->
                <div class="untracked-qty-box border rounded p-2 bg-body-tertiary mb-2 d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small fw-bold text-dark">
                                <i class="bi bi-box-seam text-primary me-1"></i> Transfer Quantity (units) *
                            </span>
                            <div class="text-muted small" style="font-size: 11px;">This component is untracked. Specify units to transfer.</div>
                        </div>
                        <div style="max-width: 140px;">
                            <input type="number" min="1" value="1" class="form-control form-control-sm font-monospace text-center untracked-qty-field">
                        </div>
                    </div>
                </div>

                <!-- Serial Selection Block -->
                <div class="serial-section border rounded p-3 bg-body-tertiary d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-primary-subtle text-primary font-monospace small">
                                <i class="bi bi-upc-scan me-1"></i>Serial Numbers
                            </span>
                            <span class="selected-serials-counter text-muted small ms-1">(0 selected)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-link btn-sm p-0 select-all-serials-btn text-decoration-none">Add All</button>
                            <span class="text-muted">&bull;</span>
                            <button type="button" class="btn btn-link btn-sm p-0 text-secondary deselect-all-serials-btn text-decoration-none">Clear</button>
                        </div>
                    </div>

                    <!-- Search or Select Serial Number Input Box -->
                    <div class="serial-picker-wrapper position-relative mb-3">
                        <label class="form-label small fw-semibold text-body mb-1">Search or Select Serial Number:</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" 
                                   class="form-control form-control-sm serial-search-input" 
                                   placeholder="Click to select or type to search serial numbers..." 
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
                            <label class="form-label small text-muted mb-0 font-monospace">Selected Serials to Transfer (List line by line):</label>
                            <label class="btn btn-outline-secondary btn-sm py-0 px-2 cursor-pointer mb-0" style="font-size: 11px;">
                                <i class="bi bi-upload me-1"></i> Upload File
                                <input type="file" class="d-none serial-file-input" accept=".txt,.csv">
                            </label>
                        </div>
                        <textarea name="products[${index}][serials]" rows="4" class="form-control form-control-sm font-monospace serials-textarea" placeholder="Selected serial numbers will appear here line by line...&#10;You can also type or scan barcodes directly."></textarea>
                        <div class="form-text small text-muted" style="font-size: 11px;">
                            Selecting serials from the input box above adds them here line by line. Transfer quantity is automatically calculated.
                        </div>
                    </div>
                </div>
            `;

            // Setup row events
            setupRowEventListeners(card);
            container.appendChild(card);
        }

        // Set up interactive handlers for a component row
        function setupRowEventListeners(card) {
            card.availableSerials = [];

            const wrapper = card.querySelector('.product-picker-wrapper');
            const searchInput = wrapper ? wrapper.querySelector('.product-search-input') : null;
            const toggleBtn = wrapper ? wrapper.querySelector('.product-picker-toggle') : null;
            const hiddenIdInput = wrapper ? wrapper.querySelector('.product-id-input') : null;
            const dropdown = wrapper ? wrapper.querySelector('.product-dropdown-menu') : null;
            const badgeContainer = card.querySelector('.selected-product-badge');
            const infoBar = card.querySelector('.stock-info-bar');

            const qtyInput = card.querySelector('.qty-input');
            const untrackedBox = card.querySelector('.untracked-qty-box');
            const untrackedField = card.querySelector('.untracked-qty-field');
            const transferQtyBadge = card.querySelector('.transfer-qty-badge');
            const removeBtn = card.querySelector('.remove-row-btn');
            const serialSection = card.querySelector('.serial-section');
            const serialPickerWrapper = card.querySelector('.serial-picker-wrapper');
            const serialSearchInput = card.querySelector('.serial-search-input');
            const serialPickerToggle = card.querySelector('.serial-picker-toggle');
            const serialDropdown = card.querySelector('.serial-dropdown-menu');
            const serialsTextarea = card.querySelector('.serials-textarea');
            const serialFileInput = card.querySelector('.serial-file-input');
            const selectAllBtn = card.querySelector('.select-all-serials-btn');
            const deselectAllBtn = card.querySelector('.deselect-all-serials-btn');
            const counterSpan = card.querySelector('.selected-serials-counter');

            // Untracked quantity input handler
            if (untrackedField) {
                untrackedField.addEventListener('input', function () {
                    const val = parseInt(this.value, 10) || 0;
                    if (qtyInput) qtyInput.value = val;
                    if (transferQtyBadge) {
                        transferQtyBadge.textContent = `Transfer: ${val} unit${val === 1 ? '' : 's'}`;
                    }
                });
            }

            // Render product catalog dropdown
            function renderDropdown(filterText = '') {
                if (!dropdown) return;
                const query = (filterText || '').toLowerCase().trim();
                const matched = productsCatalog.filter(p => {
                    if (!query) return true;
                    const fullStr = `${p.name} (sku: ${p.sku}) ${p.category}`.toLowerCase();
                    return fullStr.includes(query) ||
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
                                ${p.serial_tracking ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size:10px;">S/N Tracked</span>' : ''}
                                ${isSelected ? '<span class="badge bg-success-subtle text-success small font-monospace"><i class="bi bi-check-lg me-1"></i>Selected</span>' : ''}
                            </div>
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
                if (searchInput) searchInput.value = `${p.name} (SKU: ${p.sku})`;
                if (hiddenIdInput) hiddenIdInput.value = p.id;
                if (dropdown) dropdown.classList.add('d-none');

                if (serialsTextarea) serialsTextarea.value = '';
                if (serialSearchInput) serialSearchInput.value = '';
                if (counterSpan) counterSpan.textContent = '(0 selected)';
                if (qtyInput) qtyInput.value = 0;
                if (transferQtyBadge) {
                    transferQtyBadge.textContent = 'Transfer: 0 units';
                }

                if (badgeContainer) {
                    if (p.serial_tracking) {
                        badgeContainer.innerHTML = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace"><i class="bi bi-upc-scan me-1"></i>Serial Number Tracking Active</span>';
                        badgeContainer.classList.remove('d-none');
                    } else {
                        badgeContainer.innerHTML = '';
                        badgeContainer.classList.add('d-none');
                    }
                }

                fetchSerialsForRow(card, p.id);
            }

            if (searchInput) {
                searchInput.addEventListener('focus', function () {
                    if (hiddenIdInput && hiddenIdInput.value) {
                        renderDropdown('');
                        this.select();
                    } else {
                        renderDropdown(this.value);
                    }
                    if (dropdown) dropdown.classList.remove('d-none');
                });

                searchInput.addEventListener('click', function () {
                    if (hiddenIdInput && hiddenIdInput.value) {
                        renderDropdown('');
                    } else {
                        renderDropdown(this.value);
                    }
                    if (dropdown) dropdown.classList.remove('d-none');
                });

                searchInput.addEventListener('input', function () {
                    if (hiddenIdInput) hiddenIdInput.value = '';
                    renderDropdown(this.value);
                    if (dropdown) dropdown.classList.remove('d-none');
                    if (infoBar) infoBar.classList.add('d-none');
                    if (serialSection) serialSection.classList.add('d-none');
                    if (untrackedBox) untrackedBox.classList.add('d-none');
                });

                searchInput.addEventListener('blur', function () {
                    setTimeout(() => {
                        if (dropdown) dropdown.classList.add('d-none');
                        if (hiddenIdInput && !hiddenIdInput.value) {
                            const exact = productsCatalog.find(p => 
                                p.sku.toLowerCase() === searchInput.value.toLowerCase().trim() ||
                                p.name.toLowerCase() === searchInput.value.toLowerCase().trim() ||
                                `${p.name} (sku: ${p.sku})`.toLowerCase() === searchInput.value.toLowerCase().trim()
                            );
                            if (exact) {
                                selectProduct(exact);
                            } else if (searchInput.value.trim() !== '') {
                                searchInput.value = '';
                                if (infoBar) infoBar.classList.add('d-none');
                                if (serialSection) serialSection.classList.add('d-none');
                                if (untrackedBox) untrackedBox.classList.add('d-none');
                            }
                        } else if (hiddenIdInput && hiddenIdInput.value) {
                            const currentProd = productsCatalog.find(p => String(p.id) === String(hiddenIdInput.value));
                            if (currentProd) {
                                searchInput.value = `${currentProd.name} (SKU: ${currentProd.sku})`;
                            }
                        }
                    }, 200);
                });
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (dropdown && dropdown.classList.contains('d-none')) {
                        if (searchInput) searchInput.focus();
                        renderDropdown('');
                        dropdown.classList.remove('d-none');
                    } else if (dropdown) {
                        dropdown.classList.add('d-none');
                    }
                });
            }

            // Remove row
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    const totalRows = document.querySelectorAll('.transfer-component-card').length;
                    if (totalRows > 1) {
                        card.remove();
                    } else {
                        alert('A stock transfer must contain at least one component.');
                    }
                });
            }

            // ---- Serial Search & Select Dropdown Handler ----
            function renderSerialDropdown(filterText = '') {
                if (!serialDropdown) return;
                const query = (filterText || '').toLowerCase().trim();
                const available = card.availableSerials || [];
                const currentList = getTextareaSerials(card);

                const matched = available.filter(s => {
                    if (!query) return true;
                    return s.serial_number && s.serial_number.toLowerCase().includes(query);
                });

                serialDropdown.innerHTML = '';
                if (available.length === 0) {
                    serialDropdown.innerHTML = '<div class="p-3 text-center text-muted small"><i class="bi bi-info-circle me-1"></i>No serial numbers available in this warehouse</div>';
                    return;
                }

                if (matched.length === 0) {
                    serialDropdown.innerHTML = `<div class="p-3 text-center text-muted small">No serials match "${escapeHtml(filterText)}"</div>`;
                    return;
                }

                matched.forEach(s => {
                    const sn = s.serial_number;
                    const snUpper = sn.toUpperCase();
                    const isAdded = currentList.includes(snUpper);

                    const item = document.createElement('div');
                    item.className = `serial-dropdown-item px-3 py-2 border-bottom d-flex align-items-center justify-content-between ${isAdded ? 'is-selected' : ''}`;
                    item.innerHTML = `
                        <span class="font-monospace ${isAdded ? 'fw-bold text-primary' : 'text-body-emphasis'}">
                            <i class="bi bi-upc me-2 text-muted"></i>${escapeHtml(sn)}
                        </span>
                        ${isAdded 
                            ? '<span class="badge bg-success-subtle text-success small font-monospace"><i class="bi bi-check-lg me-1"></i>Added (click to remove)</span>' 
                            : '<span class="badge bg-secondary-subtle text-body-secondary small font-monospace"><i class="bi bi-plus me-1"></i>Select</span>'
                        }
                    `;

                    item.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        toggleSerialInTextarea(card, sn);
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
                        const available = card.availableSerials || [];
                        const query = (this.value || '').trim().toLowerCase();
                        if (!query) return;
                        const match = available.find(s => s.serial_number.toLowerCase() === query) ||
                                      available.find(s => s.serial_number.toLowerCase().includes(query));
                        if (match) {
                            toggleSerialInTextarea(card, match.serial_number);
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

            // Select All (Add all available serials to textarea line-by-line)
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function () {
                    const available = card.availableSerials || [];
                    const allSerials = available.map(s => s.serial_number);
                    if (serialsTextarea) {
                        serialsTextarea.value = allSerials.join('\n');
                    }
                    syncCountsFromTextarea(card);
                    if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                        renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                    }
                });
            }

            // Deselect All (Clear textarea)
            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function () {
                    if (serialsTextarea) {
                        serialsTextarea.value = '';
                    }
                    syncCountsFromTextarea(card);
                    if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                        renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                    }
                });
            }

            // Textarea manual typing or barcode scan (Line by line)
            if (serialsTextarea) {
                serialsTextarea.addEventListener('input', function () {
                    syncCountsFromTextarea(card);
                    if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                        renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                    }
                });
            }

            // File upload (.txt, .csv)
            if (serialFileInput) {
                serialFileInput.addEventListener('change', function (e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function (event) {
                        const text = event.target.result;
                        const lines = text.split(/[\r\n,]+/).map(s => s.trim().toUpperCase()).filter(s => s.length > 0);
                        const current = getTextareaSerials(card);
                        const combined = Array.from(new Set([...current, ...lines]));
                        if (serialsTextarea) {
                            serialsTextarea.value = combined.join('\n');
                        }
                        syncCountsFromTextarea(card);
                        if (serialDropdown && !serialDropdown.classList.contains('d-none')) {
                            renderSerialDropdown(serialSearchInput ? serialSearchInput.value : '');
                        }
                    };
                    reader.readAsText(file);
                });
            }
        }

        // Parse serial numbers from textarea
        function getTextareaSerials(card) {
            const textarea = card.querySelector('.serials-textarea');
            if (!textarea) return [];
            const text = (textarea.value || '').trim();
            if (!text) return [];
            return text.split(/[\r\n,]+/).map(s => s.trim().toUpperCase()).filter(s => s.length > 0);
        }

        // Sync transfer quantity and counter badge from textarea content
        function syncCountsFromTextarea(card) {
            if (!card) return;
            const serials = getTextareaSerials(card);
            const unique = Array.from(new Set(serials));
            const qtyInput = card.querySelector('.qty-input');
            const counterSpan = card.querySelector('.selected-serials-counter');
            const transferQtyBadge = card.querySelector('.transfer-qty-badge');

            if (counterSpan) counterSpan.textContent = `(${unique.length} selected)`;
            if (qtyInput) qtyInput.value = unique.length;
            if (transferQtyBadge) {
                transferQtyBadge.textContent = `Transfer: ${unique.length} unit${unique.length === 1 ? '' : 's'}`;
            }
        }

        // Toggle a serial number in the textarea line by line
        function toggleSerialInTextarea(card, sn) {
            const textarea = card.querySelector('.serials-textarea');
            if (!textarea) return;
            const current = getTextareaSerials(card);
            const snUpper = sn.trim().toUpperCase();
            let updated;
            if (current.includes(snUpper)) {
                updated = current.filter(s => s !== snUpper);
            } else {
                updated = [...current, snUpper];
            }
            textarea.value = updated.join('\n');
            syncCountsFromTextarea(card);
        }

        // Fetch serial numbers available in source warehouse for a row
        function fetchSerialsForRow(card, productId) {
            if (!card) return;
            const fromWarehouseSelect = document.getElementById('fromWarehouse');
            const warehouseId = fromWarehouseSelect ? fromWarehouseSelect.value : null;
            const infoBar = card.querySelector('.stock-info-bar');
            const stockBadge = card.querySelector('.stock-badge');
            const serialCountBadge = card.querySelector('.serial-count-badge');
            const serialSection = card.querySelector('.serial-section');
            const serialSearchInput = card.querySelector('.serial-search-input');
            const serialDropdown = card.querySelector('.serial-dropdown-menu');
            const qtyInput = card.querySelector('.qty-input');
            const untrackedBox = card.querySelector('.untracked-qty-box');
            const untrackedField = card.querySelector('.untracked-qty-field');
            const transferQtyBadge = card.querySelector('.transfer-qty-badge');

            if (!warehouseId) {
                alert('Please select a Source Warehouse first.');
                if (fromWarehouseSelect) fromWarehouseSelect.focus();
                return;
            }

            if (serialSearchInput) {
                serialSearchInput.placeholder = 'Loading available serials in source warehouse...';
                serialSearchInput.disabled = true;
            }
            if (serialSection) serialSection.classList.remove('d-none');
            if (infoBar) infoBar.classList.remove('d-none');

            const url = `${serialFetchUrl}?product_id=${encodeURIComponent(productId)}&warehouse_id=${encodeURIComponent(warehouseId)}`;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(errData => {
                            throw new Error(errData.message || `Server status ${res.status}`);
                        }).catch(() => {
                            throw new Error(`Server status ${res.status}`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data || !data.success) {
                        if (serialSearchInput) {
                            serialSearchInput.placeholder = (data && data.message) || 'Error loading serials.';
                        }
                        return;
                    }

                    card.availableSerials = data.serials || [];

                    if (stockBadge) {
                        stockBadge.textContent = `In Source WH: ${data.warehouse_stock} units`;
                        stockBadge.className = data.warehouse_stock > 0 ? 'badge bg-success-subtle text-success me-1' : 'badge bg-danger-subtle text-danger me-1';
                    }

                    if (data.requires_serial_tracking || data.count > 0) {
                        if (untrackedBox) untrackedBox.classList.add('d-none');
                        if (serialCountBadge) {
                            serialCountBadge.textContent = `Tracked Serials: ${data.count}`;
                            serialCountBadge.classList.remove('d-none');
                        }
                        if (serialSection) serialSection.classList.remove('d-none');

                        if (serialSearchInput) {
                            serialSearchInput.value = '';
                            if (data.count === 0) {
                                serialSearchInput.placeholder = `No serial numbers available in ${data.warehouse_name || 'warehouse'}`;
                                serialSearchInput.disabled = true;
                            } else {
                                serialSearchInput.placeholder = `Click to select or type to search ${data.count} available serials...`;
                                serialSearchInput.disabled = false;
                            }
                        }

                        syncCountsFromTextarea(card);
                    } else {
                        // Untracked product
                        if (serialCountBadge) serialCountBadge.classList.add('d-none');
                        if (serialSection) serialSection.classList.add('d-none');
                        if (untrackedBox) untrackedBox.classList.remove('d-none');
                        const defaultQty = untrackedField ? (parseInt(untrackedField.value, 10) || 1) : 1;
                        if (qtyInput) qtyInput.value = defaultQty;
                        if (transferQtyBadge) {
                            transferQtyBadge.textContent = `Transfer: ${defaultQty} unit${defaultQty === 1 ? '' : 's'}`;
                        }
                    }
                })
                .catch(err => {
                    console.error('Fetch serials error:', err);
                    if (serialSearchInput) {
                        serialSearchInput.placeholder = `Failed to load serials (${err.message}).`;
                        serialSearchInput.disabled = true;
                    }
                });
        }
    </script>
</x-app-layout>
