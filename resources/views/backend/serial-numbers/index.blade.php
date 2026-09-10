<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Hardware Serialization</p>
                    <h1 class="h3 mb-1">Serial Number Registry &amp; Warranties</h1>
                    <p class="text-muted mb-0">Track individual component serials, warehouse allocation, outbound shipment, and RMA status.</p>
                </div>
            </div>
            <div class="heading-actions d-flex gap-2">
                <div class="dropdown">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download Templates
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 13px;">
                        <li><h6 class="dropdown-header text-uppercase fw-bold" style="font-size: 11px;">Starter Templates</h6></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.serial-numbers.templates.download', 'csv') }}">
                                <i class="bi bi-filetype-csv text-success fs-6"></i>
                                <div>
                                    <div class="fw-semibold">CSV Template (.csv)</div>
                                    <span class="text-muted" style="font-size: 11px;">Standard comma-separated spreadsheet</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.serial-numbers.templates.download', 'excel') }}">
                                <i class="bi bi-file-earmark-excel text-success fs-6"></i>
                                <div>
                                    <div class="fw-semibold">Excel Template (.xlsx)</div>
                                    <span class="text-muted" style="font-size: 11px;">Microsoft Excel workbook spreadsheet</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.serial-numbers.templates.download', 'json') }}">
                                <i class="bi bi-filetype-json text-warning fs-6"></i>
                                <div>
                                    <div class="fw-semibold">JSON Template (.json)</div>
                                    <span class="text-muted" style="font-size: 11px;">Array of structured serial objects</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.serial-numbers.templates.download', 'text') }}">
                                <i class="bi bi-file-text text-primary fs-6"></i>
                                <div>
                                    <div class="fw-semibold">Plain Text (.txt)</div>
                                    <span class="text-muted" style="font-size: 11px;">Simple one barcode per line format</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchIngestModal">
                    <i class="bi bi-plus-circle me-1"></i> Ingest Serial Numbers
                </button>
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
        @elseif($errors->any() && ! $errors->hasAny(['serials_text', 'product_id', 'warehouse_id', 'cost_price', 'status', 'notes']))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i>
                    <strong>Action Required:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="panel p-3 mb-3 mt-3">
            <form action="{{ route('admin.serial-numbers.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control font-monospace" placeholder="Search Serial or SKU...">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Hardware Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="IN_STOCK" {{ request('status') === 'IN_STOCK' ? 'selected' : '' }}>IN_STOCK (Available)</option>
                        <option value="ALLOCATED" {{ request('status') === 'ALLOCATED' ? 'selected' : '' }}>ALLOCATED (Order Reserved)</option>
                        <option value="SHIPPED" {{ request('status') === 'SHIPPED' ? 'selected' : '' }}>SHIPPED (With Customer)</option>
                        <option value="RETURNED_RMA" {{ request('status') === 'RETURNED_RMA' ? 'selected' : '' }}>RETURNED_RMA (In Service)</option>
                        <option value="DEFECTIVE_SCRAP" {{ request('status') === 'DEFECTIVE_SCRAP' ? 'selected' : '' }}>DEFECTIVE_SCRAP</option>
                        <option value="RETURNED_TO_VENDOR" {{ request('status') === 'RETURNED_TO_VENDOR' ? 'selected' : '' }}>RETURNED_TO_VENDOR</option>
                        <option value="OTHER" {{ request('status') === 'OTHER' ? 'selected' : '' }}>OTHER (Miscellaneous)</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="warehouse_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <a href="{{ route('admin.serial-numbers.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>

        <!-- Serials Table -->
        <div class="panel p-3">
            <x-datatable id="serialNumbersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="serialNumbersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($serialNumbers as $sn)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $sn->serial_number }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.products.edit', $sn->product_id) }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                        {{ $sn->product?->name }}
                                    </a>
                                    <div class="text-muted small font-monospace" style="font-size: 11px;">SKU: {{ $sn->product?->sku }}</div>
                                </td>
                                <td>
                                    <span class="text-body-emphasis small">{{ $sn->warehouse?->name ?? 'Default Warehouse' }}</span>
                                </td>
                                <td>
                                    @if($sn->status === 'IN_STOCK')
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>IN_STOCK
                                        </span>
                                    @elseif($sn->status === 'ALLOCATED')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border-0 px-2 py-1">ALLOCATED</span>
                                    @elseif($sn->status === 'SHIPPED')
                                        <span class="badge bg-info-subtle text-info-emphasis border-0 px-2 py-1">SHIPPED</span>
                                    @elseif($sn->status === 'RETURNED_RMA')
                                        <span class="badge bg-danger-subtle text-danger border-0 px-2 py-1">RETURNED_RMA</span>
                                    @elseif($sn->status === 'DEFECTIVE_SCRAP')
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">SCRAP</span>
                                    @elseif($sn->status === 'OTHER')
                                        <span class="badge bg-secondary">OTHER</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">{{ $sn->status }}</span>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $sn->inbound_date ? $sn->inbound_date->format('M d, Y') : '-' }}</td>
                                <td>
                                    @if($sn->warranty_end_date)
                                        @if($sn->warranty_end_date->isPast())
                                            <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Expired ({{ $sn->warranty_end_date->format('M Y') }})</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                                Valid until {{ $sn->warranty_end_date->format('M d, Y') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">Not Activated</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <button type="button" class="btn-ghost text-primary" data-bs-toggle="modal" data-bs-target="#editSerialModal{{ $sn->id }}" title="Update Serial">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($sn->status === 'IN_STOCK')
                                            <form action="{{ route('admin.serial-numbers.destroy', $sn->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  data-confirm-title="Delete Serial Number"
                                                  data-confirm="Are you sure you want to permanently delete serial number '{{ $sn->serial_number }}'?"
                                                  data-confirm-detail="This action will permanently delete this record and automatically deduct warehouse stock. This cannot be undone."
                                                  data-confirm-btn="Delete Serial Number"
                                                  data-confirm-type="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete Serial Number">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn-ghost text-muted opacity-50" disabled title="Only IN_STOCK serial numbers can be deleted (Current status: {{ $sn->status }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Status Modal -->
                            <div class="modal fade" id="editSerialModal{{ $sn->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.serial-numbers.update', $sn->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Update Serial: {{ $sn->serial_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Lifecycle Status *</label>
                                                    <select name="status" required class="form-select">
                                                        <option value="IN_STOCK" {{ $sn->status === 'IN_STOCK' ? 'selected' : '' }}>IN_STOCK (In Warehouse)</option>
                                                        <option value="ALLOCATED" {{ $sn->status === 'ALLOCATED' ? 'selected' : '' }}>ALLOCATED (Reserved for Order)</option>
                                                        <option value="SHIPPED" {{ $sn->status === 'SHIPPED' ? 'selected' : '' }}>SHIPPED (Delivered)</option>
                                                        <option value="RETURNED_RMA" {{ $sn->status === 'RETURNED_RMA' ? 'selected' : '' }}>RETURNED_RMA (Customer Return)</option>
                                                        <option value="DEFECTIVE_SCRAP" {{ $sn->status === 'DEFECTIVE_SCRAP' ? 'selected' : '' }}>DEFECTIVE_SCRAP (Unrepairable)</option>
                                                        <option value="RETURNED_TO_VENDOR" {{ $sn->status === 'RETURNED_TO_VENDOR' ? 'selected' : '' }}>RETURNED_TO_VENDOR (Vendor RMA)</option>
                                                        <option value="OTHER" {{ $sn->status === 'OTHER' ? 'selected' : '' }}>OTHER (Miscellaneous / Other)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-0">
                                                    <label class="form-label fw-bold small">Lifecycle Notes / Remarks</label>
                                                    <textarea name="notes" rows="4" class="form-control" placeholder="Order allocation, RMA ticket #, failure reason, or technician remarks...">{{ $sn->notes }}</textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm fw-bold">Save Status</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    No serial numbers found matching your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $serialNumbers->links() }}
            </div>
        </div>
    </div>

    <!-- Batch Ingest Modal -->
    <div class="modal fade" id="batchIngestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header pb-0 border-bottom-0">
                    <div>
                        <h5 class="modal-title fw-bold mb-1"><i class="bi bi-upc-scan text-primary me-2"></i>Ingest Serial Numbers</h5>
                        <p class="text-muted small mb-0">Register serialized hardware units via manual scanning or bulk file upload.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Nav Tabs for Ingestion Method -->
                <div class="px-3 pt-3">
                    <ul class="nav nav-tabs" id="ingestTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="tab-manual-btn" data-bs-toggle="tab" data-bs-target="#tab-manual-pane" type="button" role="tab" aria-controls="tab-manual-pane" aria-selected="true">
                                <i class="bi bi-keyboard me-1"></i> Manual / Barcode Scan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-file-btn" data-bs-toggle="tab" data-bs-target="#tab-file-pane" type="button" role="tab" aria-controls="tab-file-pane" aria-selected="false">
                                <i class="bi bi-cloud-arrow-up me-1"></i> File Upload (CSV, Excel, JSON, TXT)
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="ingestTabsContent">
                    <!-- TAB 1: Manual / Barcode Scan -->
                    <div class="tab-pane fade show active" id="tab-manual-pane" role="tabpanel" aria-labelledby="tab-manual-btn">
                        <form action="{{ route('admin.serial-numbers.store') }}" method="POST">
                            @csrf
                            <div class="modal-body pt-3">
                                <!-- Unified Search & Select Hardware Product Combobox -->
                                <div class="mb-3 position-relative" id="productComboboxWrapper">
                                    <label class="form-label fw-bold small d-flex justify-content-between align-items-center">
                                        <span>Hardware Product *</span>
                                        <span class="text-muted small fw-normal" id="productCountBadge">{{ $products->count() }} products available</span>
                                    </label>

                                    <!-- Hidden field for form submission -->
                                    <input type="hidden" name="product_id" id="ingest_product_id" value="{{ old('product_id') }}">

                                    <!-- Single Unified Search & Select Input -->
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" 
                                               id="productSearchInput" 
                                               class="form-control" 
                                               placeholder="Search product name, SKU, or specs..." 
                                               autocomplete="off">
                                        <button type="button" class="btn btn-outline-secondary d-none" id="clearSelectedProductBtn" title="Clear selection">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="dropdownToggleBtn" tabindex="-1" title="Toggle product list">
                                            <i class="bi bi-chevron-down" id="productDropdownChevron"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback d-none" id="productSelectionError">Please search and select a hardware product from the list.</div>

                                    <!-- Dropdown Options List -->
                                    <div class="product-dropdown-menu shadow-lg border rounded-3 p-1 d-none" 
                                         id="productComboboxDropdown" 
                                         style="position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1070; max-height: 250px; overflow-y: auto;">
                                        
                                        <div id="productOptionsContainer">
                                            @foreach($products as $p)
                                                <div class="product-option-row px-3 py-2 rounded cursor-pointer d-flex align-items-center justify-content-between mb-1"
                                                     role="option"
                                                     tabindex="0"
                                                     data-id="{{ $p->id }}"
                                                     data-name="{{ $p->name }}"
                                                     data-sku="{{ $p->sku }}"
                                                     data-cost="{{ $p->cost_price ? number_format($p->cost_price, 2, '.', '') : '' }}"
                                                     data-price="{{ $p->price ? number_format($p->price, 2, '.', '') : '' }}"
                                                     data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . ($p->brand?->name ?? '')) }}">
                                                    <div class="overflow-hidden me-2">
                                                        <div class="fw-semibold text-truncate item-title">{{ $p->name }}</div>
                                                        <div class="small text-muted font-monospace d-flex align-items-center gap-2 mt-1">
                                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $p->sku }}</span>
                                                            @if($p->cost_price)
                                                                <span class="text-body-secondary">Cost: ${{ number_format($p->cost_price, 2) }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="text-end text-nowrap flex-shrink-0">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">${{ number_format($p->price, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div id="noProductMatchAlert" class="text-center py-4 text-muted small d-none">
                                            <i class="bi bi-search me-1"></i> No products match your search query
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Destination Warehouse *</label>
                                    <select name="warehouse_id" required class="form-select @error('warehouse_id') is-invalid @enderror">
                                        @foreach($warehouses as $wh)
                                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }} ({{ $wh->code }})</option>
                                        @endforeach
                                    </select>
                                    @error('warehouse_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Unit Cost Price ($)</label>
                                    <input type="number" step="0.01" name="cost_price" id="ingest_cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price') }}" placeholder="Leave empty to use product default cost">
                                    @error('cost_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-bold small">Serial Numbers (Scan barcode or paste 1 per line) *</label>
                                    <textarea name="serials_text" rows="6" required class="form-control font-monospace @error('serials_text') is-invalid @enderror" placeholder="SN-RTX4090-001
SN-RTX4090-002
SN-RTX4090-003">{{ old('serials_text') }}</textarea>
                                    @error('serials_text')
                                        <div class="invalid-feedback d-block mt-2 fw-semibold">
                                            <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">Register Inbound Serials &rarr;</button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: File Upload (CSV, Excel, JSON, TXT) -->
                    <div class="tab-pane fade" id="tab-file-pane" role="tabpanel" aria-labelledby="tab-file-btn">
                        <form action="{{ route('admin.serial-numbers.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body pt-3">
                                <!-- Download Templates Helper Box -->
                                <div class="p-3 mb-3 rounded border bg-light-subtle">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-bold small text-body-emphasis"><i class="bi bi-file-earmark-arrow-down me-1 text-primary"></i>Download Sample Starter Templates</span>
                                        <span class="text-muted small" style="font-size: 11px;">Pre-formatted templates ready to fill</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a href="{{ route('admin.serial-numbers.templates.download', 'csv') }}" class="btn btn-outline-secondary btn-sm py-1 px-2 font-monospace" style="font-size: 12px;">
                                            <i class="bi bi-filetype-csv text-success me-1"></i>CSV (.csv)
                                        </a>
                                        <a href="{{ route('admin.serial-numbers.templates.download', 'excel') }}" class="btn btn-outline-secondary btn-sm py-1 px-2 font-monospace" style="font-size: 12px;">
                                            <i class="bi bi-file-earmark-excel text-success me-1"></i>Excel (.xlsx)
                                        </a>
                                        <a href="{{ route('admin.serial-numbers.templates.download', 'json') }}" class="btn btn-outline-secondary btn-sm py-1 px-2 font-monospace" style="font-size: 12px;">
                                            <i class="bi bi-filetype-json text-warning me-1"></i>JSON (.json)
                                        </a>
                                        <a href="{{ route('admin.serial-numbers.templates.download', 'text') }}" class="btn btn-outline-secondary btn-sm py-1 px-2 font-monospace" style="font-size: 12px;">
                                            <i class="bi bi-file-text text-primary me-1"></i>Plain Text (.txt)
                                        </a>
                                    </div>
                                </div>

                                <!-- File Input -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Choose File (CSV, Excel, JSON, TXT) *</label>
                                    <input type="file" name="file" required class="form-control" accept=".csv, .xlsx, .xls, .json, .txt">
                                    <div class="form-text small text-muted">
                                        Supports <code>.csv</code>, <code>.xlsx</code>, <code>.xls</code>, <code>.json</code>, <code>.txt</code> (Max size: 10MB).
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <!-- Fallback Destination Warehouse -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Default Destination Warehouse</label>
                                        <select name="warehouse_id" class="form-select">
                                            <option value="">-- Use warehouse from file, or select default --</option>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small text-muted">Used if the file row doesn't specify <code>warehouse_code</code>.</div>
                                    </div>

                                    <!-- Fallback Hardware Product -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Default Hardware Product</label>
                                        <select name="product_id" class="form-select">
                                            <option value="">-- Use product SKU from file, or select default --</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small text-muted">Used if the file row doesn't specify <code>product_sku</code>.</div>
                                    </div>
                                </div>

                                <!-- Fallback Cost Price -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Default Unit Cost Price ($)</label>
                                    <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="Leave empty to use product default cost">
                                </div>

                                <!-- Supported Format Reference -->
                                <div class="p-3 rounded border bg-light-subtle small">
                                    <div class="fw-bold mb-1"><i class="bi bi-info-circle text-primary me-1"></i>Format Reference Guide</div>
                                    <div class="text-muted">
                                        <ul class="mb-0 ps-3">
                                            <li><strong>CSV &amp; Excel:</strong> Header columns: <code>serial_number</code>, <code>product_sku</code> (optional), <code>warehouse_code</code> (optional), <code>cost_price</code> (optional).</li>
                                            <li><strong>JSON:</strong> Array of objects (e.g. <code>[{"serial_number": "SN01", "product_sku": "SKU01"}]</code>) or simple array of serial strings.</li>
                                            <li><strong>Plain Text:</strong> One serial number per line (e.g. <code>SN-0001</code>).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Import Serial Numbers &rarr;
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($errors->has('serials_text') || $errors->has('product_id') || $errors->has('warehouse_id') || $errors->has('cost_price'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('batchIngestModal');
                if (modalEl && window.bootstrap) {
                    var modal = new window.bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
    @endif

    <style>
        .cursor-pointer { cursor: pointer; }
        .product-dropdown-menu {
            background-color: var(--bs-body-bg, #ffffff);
            border-color: var(--bs-border-color, #dee2e6) !important;
        }
        .product-option-row {
            transition: background-color 0.12s ease, border-color 0.12s ease;
            border: 1px solid transparent;
        }
        .product-option-row:hover,
        .product-option-row:focus,
        .product-option-row.is-active-focus {
            background-color: rgba(13, 110, 253, 0.08);
            border-color: rgba(13, 110, 253, 0.25);
            outline: none;
        }
        .product-option-row.is-selected {
            background-color: rgba(13, 110, 253, 0.14);
            border-color: #0d6efd;
        }
        html[data-theme="dark"] .product-dropdown-menu {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.6) !important;
        }
        html[data-theme="dark"] .product-option-row:hover,
        html[data-theme="dark"] .product-option-row:focus,
        html[data-theme="dark"] .product-option-row.is-active-focus {
            background-color: rgba(59, 130, 246, 0.2) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
        }
        html[data-theme="dark"] .product-option-row.is-selected {
            background-color: rgba(59, 130, 246, 0.3) !important;
            border-color: #3b82f6 !important;
        }
        html[data-theme="dark"] .product-option-row .item-title {
            color: #f8fafc !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('productComboboxWrapper');
            if (!wrapper) return;

            const searchInput = document.getElementById('productSearchInput');
            const dropdown = document.getElementById('productComboboxDropdown');
            const toggleBtn = document.getElementById('dropdownToggleBtn');
            const clearBtn = document.getElementById('clearSelectedProductBtn');
            const chevron = document.getElementById('productDropdownChevron');
            const hiddenInput = document.getElementById('ingest_product_id');
            const costInput = document.getElementById('ingest_cost_price');
            const options = Array.from(document.querySelectorAll('.product-option-row'));
            const noMatch = document.getElementById('noProductMatchAlert');
            const errorMsg = document.getElementById('productSelectionError');
            const ingestForm = document.querySelector('#batchIngestModal form');

            let selectedProductObj = null;

            function openDropdown() {
                dropdown.classList.remove('d-none');
                dropdown.scrollTop = 0;
                if (chevron) chevron.classList.replace('bi-chevron-down', 'bi-chevron-up');
            }

            function closeDropdown() {
                dropdown.classList.add('d-none');
                if (chevron) chevron.classList.replace('bi-chevron-up', 'bi-chevron-down');
            }

            function selectProduct(id, name, sku, cost) {
                hiddenInput.value = id;
                selectedProductObj = { id, name, sku, cost };
                searchInput.value = name + ' (' + sku + ')';
                clearBtn.classList.remove('d-none');
                searchInput.classList.remove('is-invalid');
                errorMsg.classList.add('d-none');

                if (cost) {
                    costInput.placeholder = 'Product default: $' + parseFloat(cost).toFixed(2);
                } else {
                    costInput.placeholder = 'Leave empty to use product default cost';
                }

                options.forEach(opt => {
                    if (opt.dataset.id === id) {
                        opt.classList.add('is-selected');
                    } else {
                        opt.classList.remove('is-selected');
                    }
                    opt.classList.remove('d-none');
                });

                noMatch.classList.add('d-none');
                closeDropdown();
            }

            function clearSelection(e) {
                if (e) e.stopPropagation();
                hiddenInput.value = '';
                selectedProductObj = null;
                searchInput.value = '';
                clearBtn.classList.add('d-none');
                costInput.placeholder = 'Leave empty to use product default cost';
                options.forEach(opt => {
                    opt.classList.remove('is-selected');
                    opt.classList.remove('d-none');
                });
                noMatch.classList.add('d-none');
                openDropdown();
                searchInput.focus();
            }

            // Click input or focus -> open dropdown
            searchInput.addEventListener('click', function () {
                openDropdown();
            });

            searchInput.addEventListener('focus', function () {
                openDropdown();
            });

            toggleBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (dropdown.classList.contains('d-none')) {
                    openDropdown();
                    searchInput.focus();
                } else {
                    closeDropdown();
                }
            });

            clearBtn.addEventListener('click', clearSelection);

            // Filter on typing
            searchInput.addEventListener('input', function () {
                openDropdown();
                const term = this.value.toLowerCase().trim();
                let matchCount = 0;

                options.forEach(opt => {
                    const haystack = opt.dataset.search;
                    if (!term || haystack.includes(term)) {
                        opt.classList.remove('d-none');
                        matchCount++;
                    } else {
                        opt.classList.add('d-none');
                    }
                });

                if (matchCount === 0) {
                    noMatch.classList.remove('d-none');
                } else {
                    noMatch.classList.add('d-none');
                }

                // If user edits text away from the selected product, reset hidden id
                if (selectedProductObj && this.value !== (selectedProductObj.name + ' (' + selectedProductObj.sku + ')')) {
                    hiddenInput.value = '';
                    clearBtn.classList.add('d-none');
                }
            });

            // Option selection
            options.forEach(opt => {
                opt.addEventListener('click', function () {
                    selectProduct(this.dataset.id, this.dataset.name, this.dataset.sku, this.dataset.cost);
                });
                opt.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        selectProduct(this.dataset.id, this.dataset.name, this.dataset.sku, this.dataset.cost);
                    }
                });
            });

            // Keyboard navigation
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    openDropdown();
                    const visibleOptions = options.filter(o => !o.classList.contains('d-none'));
                    if (visibleOptions.length > 0) {
                        visibleOptions[0].focus();
                    }
                } else if (e.key === 'Enter') {
                    const visibleOptions = options.filter(o => !o.classList.contains('d-none'));
                    if (visibleOptions.length > 0) {
                        e.preventDefault();
                        const first = visibleOptions[0];
                        selectProduct(first.dataset.id, first.dataset.name, first.dataset.sku, first.dataset.cost);
                    }
                }
            });

            // Option arrow key navigation
            options.forEach(opt => {
                opt.addEventListener('keydown', function (e) {
                    const visibleOptions = options.filter(o => !o.classList.contains('d-none'));
                    const currentIndex = visibleOptions.indexOf(this);

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (currentIndex < visibleOptions.length - 1) {
                            visibleOptions[currentIndex + 1].focus();
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (currentIndex > 0) {
                            visibleOptions[currentIndex - 1].focus();
                        } else {
                            searchInput.focus();
                        }
                    } else if (e.key === 'Escape') {
                        closeDropdown();
                        searchInput.focus();
                    }
                });
            });

            // Close on click outside
            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    closeDropdown();
                    // If user left input without selecting a product, restore or clear
                    if (selectedProductObj) {
                        searchInput.value = selectedProductObj.name + ' (' + selectedProductObj.sku + ')';
                        clearBtn.classList.remove('d-none');
                    } else if (!hiddenInput.value) {
                        searchInput.value = '';
                        clearBtn.classList.add('d-none');
                        options.forEach(opt => opt.classList.remove('d-none'));
                        noMatch.classList.add('d-none');
                    }
                }
            });

            // Form submission validation
            if (ingestForm) {
                ingestForm.addEventListener('submit', function (e) {
                    if (!hiddenInput.value) {
                        e.preventDefault();
                        searchInput.classList.add('is-invalid');
                        errorMsg.classList.remove('d-none');
                        searchInput.focus();
                        return false;
                    }
                });
            }
        });
    </script>
</x-app-layout>

