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
                @canany(['create products', 'edit products'])
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchIngestModal">
                        <i class="bi bi-plus-circle me-1"></i> Ingest Serial Numbers
                    </button>
                @endcanany
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
                                        @can('edit products')
                                            <button type="button" class="btn-ghost text-primary" data-bs-toggle="modal" data-bs-target="#editSerialModal{{ $sn->id }}" title="Update Serial">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('delete products')
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
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            @can('edit products')
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
                            @endcan
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

    @canany(['create products', 'edit products'])
        <!-- Batch Ingest Modal -->
        <div class="modal fade" id="batchIngestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header pb-0 border-bottom-0">
                    <div>
                        <h5 class="modal-title fw-bold mb-1"><i class="bi bi-upc-scan text-primary me-2"></i>Ingest Serial Numbers</h5>
                        <p class="text-muted small mb-0">Register inventory via barcode scan or file import.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Nav Tabs for Ingestion Method -->
                <div class="px-3 pt-3">
                    <ul class="nav nav-tabs" id="ingestTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="tab-manual-btn" data-bs-toggle="tab" data-bs-target="#tab-manual-pane" type="button" role="tab" aria-controls="tab-manual-pane" aria-selected="true">
                                <i class="bi bi-upc-scan me-1"></i> Manual Scan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="tab-file-btn" data-bs-toggle="tab" data-bs-target="#tab-file-pane" type="button" role="tab" aria-controls="tab-file-pane" aria-selected="false">
                                <i class="bi bi-cloud-arrow-up me-1"></i> File Upload
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="ingestTabsContent">
                    <!-- TAB 1: Manual / Barcode Scan -->
                    <div class="tab-pane fade show active" id="tab-manual-pane" role="tabpanel" aria-labelledby="tab-manual-btn">
                        <form action="{{ route('admin.serial-numbers.store') }}" method="POST" autocomplete="off" id="manualIngestForm">
                            @csrf
                            <div class="modal-body pt-3">
                                <!-- Unified Search & Select Hardware Product Combobox -->
                                <div class="mb-3" id="productComboboxWrapper">
                                    <label class="form-label fw-bold small d-flex justify-content-between align-items-center">
                                        <span>Hardware Product *</span>
                                        <span class="text-muted small fw-normal" id="productCountBadge">{{ $products->count() }} products available</span>
                                    </label>

                                    <!-- Hidden field for form submission -->
                                    <input type="hidden" name="product_id" id="ingest_product_id" value="{{ old('product_id') }}">

                                    <!-- Relative positioning container -->
                                    <div class="position-relative">
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
                                             style="overflow-y: auto;">
                                            <div id="productOptionsContainer">
                                                @foreach($products as $p)
                                                    <div class="product-option-row px-2.5 py-1.5 rounded cursor-pointer d-flex align-items-center justify-content-between mb-0.5"
                                                         role="option"
                                                         tabindex="0"
                                                         data-id="{{ $p->id }}"
                                                         data-name="{{ $p->name }}"
                                                         data-sku="{{ $p->sku }}"
                                                         data-cost="{{ $p->cost_price ? number_format($p->cost_price, 2, '.', '') : '' }}"
                                                         data-price="{{ $p->price ? number_format($p->price, 2, '.', '') : '' }}"
                                                         data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . ($p->brand?->name ?? '')) }}">
                                                        <div class="text-truncate small text-body-emphasis pe-2 fw-medium">
                                                            {{ $p->name }}
                                                        </div>
                                                        <span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle font-monospace flex-shrink-0" style="font-size: 10.5px;">
                                                            {{ $p->sku }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div id="noProductMatchAlert" class="text-center py-3 text-muted small d-none">
                                                <i class="bi bi-search me-1"></i> No matching products
                                            </div>
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
                                    <textarea name="serials_text" id="manual_serials_text" rows="6" required class="form-control font-monospace @error('serials_text') is-invalid @enderror" placeholder="SN-RTX4090-001
SN-RTX4090-002
SN-RTX4090-003" autocomplete="off">{{ $errors->has('serials_text') ? old('serials_text') : '' }}</textarea>
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
                                <!-- File Selection -->
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-semibold mb-0">Select File <span class="text-danger">*</span></label>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-decoration-none p-0 small text-muted" type="button" data-bs-toggle="dropdown" style="font-size: 11.5px;">
                                                <i class="bi bi-download me-1"></i>Sample templates
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm small py-1" style="font-size: 12px;">
                                                <li><a class="dropdown-item py-1" href="{{ route('admin.serial-numbers.templates.download', 'csv') }}"><i class="bi bi-filetype-csv text-success me-2"></i>CSV Template</a></li>
                                                <li><a class="dropdown-item py-1" href="{{ route('admin.serial-numbers.templates.download', 'excel') }}"><i class="bi bi-file-earmark-excel text-success me-2"></i>Excel Template</a></li>
                                                <li><a class="dropdown-item py-1" href="{{ route('admin.serial-numbers.templates.download', 'json') }}"><i class="bi bi-filetype-json text-warning me-2"></i>JSON Template</a></li>
                                                <li><a class="dropdown-item py-1" href="{{ route('admin.serial-numbers.templates.download', 'text') }}"><i class="bi bi-file-text text-primary me-2"></i>Plain Text (TXT)</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <input type="file" name="file" id="batch_ingest_file" class="form-control form-control-sm" accept=".csv, .xlsx, .xls, .json, .txt">
                                    <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 11px;">
                                        <span>CSV, Excel, JSON, TXT</span>
                                        <span>Max 10MB</span>
                                    </div>

                                    <!-- Live Loading Spinner -->
                                    <div id="batch_file_parsing_spinner" class="text-center py-2 d-none text-muted small mt-2 border rounded bg-light">
                                        <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                        <span>Reading and extracting serial numbers from file...</span>
                                    </div>
                                </div>

                                <!-- Fallback Defaults Card -->
                                <div class="p-3 mb-3 rounded-3 border bg-body-tertiary">
                                    <div class="fw-semibold small text-body-secondary mb-2 d-flex align-items-center gap-1">
                                        <i class="bi bi-sliders2 text-primary"></i> Fallback Defaults
                                    </div>
                                    <div class="row g-2">
                                        <!-- Warehouse -->
                                        <div class="col-md-6">
                                            <label class="form-label small fw-medium mb-1">Warehouse</label>
                                            <select name="warehouse_id" class="form-select form-select-sm">
                                                <option value="">-- From file --</option>
                                                @foreach($warehouses as $wh)
                                                    <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Hardware Product Combobox -->
                                        <div class="col-md-6" id="fileProductComboboxWrapper">
                                            <label class="form-label small fw-medium mb-1">Hardware Product</label>
                                            <input type="hidden" name="product_id" id="file_product_id" value="">
                                            <div class="position-relative">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                                    <input type="text" 
                                                           id="fileProductSearchInput" 
                                                           class="form-control" 
                                                           placeholder="Search product or SKU..." 
                                                           autocomplete="off">
                                                    <button type="button" class="btn btn-outline-secondary d-none" id="fileClearSelectedProductBtn" title="Clear selection">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" id="fileDropdownToggleBtn" tabindex="-1" title="Toggle product list">
                                                        <i class="bi bi-chevron-down" id="fileProductDropdownChevron"></i>
                                                    </button>
                                                </div>

                                                <!-- Dropdown Options List -->
                                                <div class="product-dropdown-menu shadow-lg border rounded-3 p-1 d-none" 
                                                     id="fileProductComboboxDropdown" 
                                                     style="overflow-y: auto;">
                                                    <div id="fileProductOptionsContainer">
                                                        <div class="product-option-row px-2.5 py-1.5 rounded cursor-pointer d-flex align-items-center justify-content-between mb-0.5 text-muted"
                                                             role="option"
                                                             tabindex="0"
                                                             data-id=""
                                                             data-name=""
                                                             data-sku=""
                                                             data-cost=""
                                                             data-price=""
                                                             data-search="default file reset all none">
                                                            <div class="text-truncate small text-body-secondary">
                                                                <i class="bi bi-dash-circle me-1.5 opacity-50"></i>-- None (from file SKU) --
                                                            </div>
                                                            <span class="badge bg-body-secondary text-muted font-monospace flex-shrink-0" style="font-size: 10px;">Default</span>
                                                        </div>

                                                        @foreach($products as $p)
                                                            <div class="product-option-row px-2.5 py-1.5 rounded cursor-pointer d-flex align-items-center justify-content-between mb-0.5"
                                                                 role="option"
                                                                 tabindex="0"
                                                                 data-id="{{ $p->id }}"
                                                                 data-name="{{ $p->name }}"
                                                                 data-sku="{{ $p->sku }}"
                                                                 data-cost="{{ $p->cost_price ? number_format($p->cost_price, 2, '.', '') : '' }}"
                                                                 data-price="{{ $p->price ? number_format($p->price, 2, '.', '') : '' }}"
                                                                 data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . ($p->brand?->name ?? '')) }}">
                                                                <div class="text-truncate small text-body-emphasis pe-2 fw-medium">
                                                                    {{ $p->name }}
                                                                </div>
                                                                <span class="badge bg-secondary-subtle text-body-secondary border border-secondary-subtle font-monospace flex-shrink-0" style="font-size: 10.5px;">
                                                                    {{ $p->sku }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div id="fileNoProductMatchAlert" class="text-center py-3 text-muted small d-none">
                                                        <i class="bi bi-search me-1"></i> No matching products
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Unit Cost -->
                                        <div class="col-12 mt-2">
                                            <label class="form-label small fw-medium mb-1">Unit Cost Price ($)</label>
                                            <input type="number" step="0.01" name="cost_price" id="file_cost_price" class="form-control form-control-sm" placeholder="Standard product cost">
                                        </div>
                                    </div>
                                </div>

                                <!-- Serial Numbers Textarea Block (Always Visible, Placed after Fallback Defaults) -->
                                <div class="mb-3" id="batch_file_preview_container">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-bold small mb-0 text-dark">
                                            <i class="bi bi-card-checklist text-primary me-1"></i>Serial Numbers (<span id="batch_file_preview_count" class="text-primary fw-bold">0</span> listed)
                                        </label>
                                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none small d-none" style="font-size: 11.5px;" id="batch_file_clear_btn">
                                            <i class="bi bi-x-circle me-1"></i>Clear File
                                        </button>
                                    </div>
                                    <textarea name="serials_text" id="batch_file_preview_text" rows="6" class="form-control form-control-sm font-monospace" placeholder="Serial numbers from your file will appear here automatically, or paste/type them (1 per line)..."></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 11px;">
                                        <span><i class="bi bi-pencil-square me-1"></i>Review, edit, or enter serials above before importing.</span>
                                        <span id="batch_file_preview_status" class="text-success fw-semibold"></span>
                                    </div>
                                </div>

                                <!-- File Format Guide -->
                                <div class="accordion accordion-flush border rounded-3 overflow-hidden" id="formatGuideAccordion">
                                    <div class="accordion-item bg-transparent">
                                        <h2 class="accordion-header" id="guideHeading">
                                            <button class="accordion-button collapsed py-2 px-3 small fw-semibold bg-transparent text-body-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuide" aria-expanded="false" aria-controls="collapseGuide">
                                                <i class="bi bi-info-circle text-primary me-2"></i> File Column Reference
                                            </button>
                                        </h2>
                                        <div id="collapseGuide" class="accordion-collapse collapse" aria-labelledby="guideHeading" data-bs-parent="#formatGuideAccordion">
                                            <div class="accordion-body px-3 py-2 small text-muted">
                                                <div class="d-flex flex-wrap align-items-center gap-1.5 mb-1.5">
                                                    <span class="text-body-secondary fw-semibold me-1">Columns:</span>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">serial_number *</span>
                                                    <span class="badge bg-body-secondary text-body-secondary border font-monospace">product_sku</span>
                                                    <span class="badge bg-body-secondary text-body-secondary border font-monospace">warehouse_code</span>
                                                    <span class="badge bg-body-secondary text-body-secondary border font-monospace">cost_price</span>
                                                </div>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    Supported: CSV, Excel (.xlsx/.xls), JSON, or Plain text (1 serial per line).
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold" id="batch_import_submit_btn">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> <span id="batch_import_submit_text">Import Serial Numbers &rarr;</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcanany

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
        /* Robust Modal Scrolling & Viewport Fitting */
        #batchIngestModal .modal-dialog {
            max-height: calc(100vh - 60px);
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }
        #batchIngestModal .modal-content {
            max-height: calc(100vh - 60px);
            display: flex;
            flex-direction: column;
        }
        #batchIngestModal .tab-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        #batchIngestModal .tab-pane {
            min-height: 0;
            height: 100%;
        }
        #batchIngestModal .tab-pane.active {
            display: flex;
            flex-direction: column;
        }
        #batchIngestModal .tab-pane form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            height: 100%;
        }
        #batchIngestModal .modal-body {
            overflow-y: auto;
            flex: 1 1 auto;
            min-height: 0;
            max-height: calc(85vh - 190px);
        }
        #batchIngestModal .modal-footer {
            flex-shrink: 0;
            background-color: var(--bs-modal-bg, #ffffff);
            border-top: 1px solid var(--bs-border-color, #dee2e6);
        }

        .cursor-pointer { cursor: pointer; }
        .product-dropdown-menu {
            background-color: var(--bs-body-bg, #ffffff);
            border: 1px solid var(--bs-border-color, #dee2e6) !important;
            border-radius: 0.5rem;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22) !important;
            overflow-y: auto;
            overscroll-behavior: contain;
        }
        .product-dropdown-menu::-webkit-scrollbar {
            width: 5px;
        }
        .product-dropdown-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        .product-dropdown-menu::-webkit-scrollbar-thumb {
            background-color: rgba(148, 163, 184, 0.4);
            border-radius: 4px;
        }
        .product-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background-color: rgba(148, 163, 184, 0.7);
        }
        .product-option-row {
            transition: background-color 0.12s ease;
            cursor: pointer;
            border-radius: 0.375rem;
        }
        .product-option-row:hover,
        .product-option-row:focus,
        .product-option-row.is-active-focus {
            background-color: rgba(13, 110, 253, 0.08);
            outline: none;
        }
        .product-option-row.is-selected {
            background-color: rgba(13, 110, 253, 0.16);
        }
        html[data-theme="dark"] .product-dropdown-menu {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.75) !important;
        }
        html[data-theme="dark"] .product-option-row:hover,
        html[data-theme="dark"] .product-option-row:focus,
        html[data-theme="dark"] .product-option-row.is-active-focus {
            background-color: rgba(59, 130, 246, 0.2) !important;
        }
        html[data-theme="dark"] .product-option-row.is-selected {
            background-color: rgba(59, 130, 246, 0.35) !important;
        }
        #productSearchInput,
        #fileProductSearchInput {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function setupProductCombobox(config) {
                const wrapper = document.getElementById(config.wrapperId);
                if (!wrapper) return;

                const searchInput = document.getElementById(config.searchInputId);
                const dropdown = document.getElementById(config.dropdownId);
                const toggleBtn = document.getElementById(config.toggleBtnId);
                const clearBtn = document.getElementById(config.clearBtnId);
                const chevron = document.getElementById(config.chevronId);
                const hiddenInput = document.getElementById(config.hiddenInputId);
                const costInput = config.costInputId ? document.getElementById(config.costInputId) : null;
                const defaultCostPlaceholder = costInput ? costInput.placeholder : '';
                const options = Array.from(dropdown.querySelectorAll('.product-option-row'));
                const noMatch = document.getElementById(config.noMatchId);
                const errorMsg = config.errorMsgId ? document.getElementById(config.errorMsgId) : null;
                const form = config.formSelector ? document.querySelector(config.formSelector) : null;
                const isRequired = !!config.isRequired;

                // Move dropdown to body so it floats cleanly above all modal containers
                document.body.appendChild(dropdown);

                let selectedProductObj = null;

                function updateDropdownPosition() {
                    const inputGroup = searchInput.closest('.input-group') || searchInput;
                    const rect = inputGroup.getBoundingClientRect();

                    dropdown.style.position = 'fixed';
                    dropdown.style.top = (rect.bottom + 4) + 'px';
                    dropdown.style.left = rect.left + 'px';
                    dropdown.style.width = rect.width + 'px';
                    dropdown.style.zIndex = '1095';

                    const spaceBelow = window.innerHeight - rect.bottom - 16;
                    dropdown.style.maxHeight = Math.min(250, Math.max(130, spaceBelow)) + 'px';
                }

                function openDropdown() {
                    updateDropdownPosition();
                    dropdown.classList.remove('d-none');
                    dropdown.scrollTop = 0;
                    if (chevron) chevron.classList.replace('bi-chevron-down', 'bi-chevron-up');
                }

                function closeDropdown() {
                    dropdown.classList.add('d-none');
                    if (chevron) chevron.classList.replace('bi-chevron-up', 'bi-chevron-down');
                }

                const modalBody = wrapper.closest('.modal-body');
                if (modalBody) {
                    modalBody.addEventListener('scroll', function () {
                        if (!dropdown.classList.contains('d-none')) {
                            updateDropdownPosition();
                        }
                    }, { passive: true });
                }
                window.addEventListener('resize', function () {
                    if (!dropdown.classList.contains('d-none')) {
                        updateDropdownPosition();
                    }
                }, { passive: true });

                const modal = wrapper.closest('.modal');
                if (modal) {
                    modal.addEventListener('hidden.bs.modal', closeDropdown);
                }
                document.querySelectorAll('#ingestTabs button[data-bs-toggle="tab"]').forEach(btn => {
                    btn.addEventListener('shown.bs.tab', closeDropdown);
                });

                function selectProduct(id, name, sku, cost) {
                    hiddenInput.value = id || '';
                    if (id) {
                        const displayName = name;
                        selectedProductObj = { id, name, sku, cost, displayName };
                        searchInput.value = displayName;
                        searchInput.title = name + (sku ? ' (' + sku + ')' : '');
                        if (clearBtn) clearBtn.classList.remove('d-none');
                        if (costInput) {
                            if (cost) {
                                costInput.placeholder = 'Default: $' + parseFloat(cost).toFixed(2);
                            } else {
                                costInput.placeholder = defaultCostPlaceholder;
                            }
                        }
                    } else {
                        selectedProductObj = null;
                        searchInput.value = '';
                        searchInput.title = '';
                        if (clearBtn) clearBtn.classList.add('d-none');
                        if (costInput) {
                            costInput.placeholder = defaultCostPlaceholder;
                        }
                    }

                    searchInput.classList.remove('is-invalid');
                    if (errorMsg) errorMsg.classList.add('d-none');

                    options.forEach(opt => {
                        if (opt.dataset.id === (id || '')) {
                            opt.classList.add('is-selected');
                        } else {
                            opt.classList.remove('is-selected');
                        }
                        opt.classList.remove('d-none');
                    });

                    if (noMatch) noMatch.classList.add('d-none');
                    closeDropdown();
                }

                function clearSelection(e) {
                    if (e) e.stopPropagation();
                    selectProduct('', '', '', '');
                    openDropdown();
                    searchInput.focus();
                }

                // If pre-filled on page load (e.g. old() value)
                if (hiddenInput && hiddenInput.value) {
                    const existing = options.find(opt => opt.dataset.id === hiddenInput.value);
                    if (existing) {
                        selectProduct(existing.dataset.id, existing.dataset.name, existing.dataset.sku, existing.dataset.cost);
                    }
                }

                // Click input or focus -> open dropdown
                searchInput.addEventListener('click', function () {
                    openDropdown();
                });

                searchInput.addEventListener('focus', function () {
                    openDropdown();
                });

                if (toggleBtn) {
                    toggleBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (dropdown.classList.contains('d-none')) {
                            openDropdown();
                            searchInput.focus();
                        } else {
                            closeDropdown();
                        }
                    });
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', clearSelection);
                }

                // Filter on typing
                searchInput.addEventListener('input', function () {
                    openDropdown();
                    const term = this.value.toLowerCase().trim();
                    let matchCount = 0;

                    options.forEach(opt => {
                        const haystack = opt.dataset.search || '';
                        if (!opt.dataset.id) {
                            if (!term || 'default file reset all none'.includes(term)) {
                                opt.classList.remove('d-none');
                                matchCount++;
                            } else {
                                opt.classList.add('d-none');
                            }
                            return;
                        }

                        if (!term || haystack.includes(term)) {
                            opt.classList.remove('d-none');
                            matchCount++;
                        } else {
                            opt.classList.add('d-none');
                        }
                    });

                    if (noMatch) {
                        if (matchCount === 0) {
                            noMatch.classList.remove('d-none');
                        } else {
                            noMatch.classList.add('d-none');
                        }
                    }

                    // If user edits text away from the selected product, reset hidden id
                    if (selectedProductObj && this.value !== selectedProductObj.displayName) {
                        hiddenInput.value = '';
                        if (clearBtn) clearBtn.classList.add('d-none');
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

                // Keyboard navigation from search input
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
                            const first = visibleOptions.find(o => o.dataset.id) || visibleOptions[0];
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
                    if (!wrapper.contains(e.target) && !dropdown.contains(e.target)) {
                        closeDropdown();
                        if (selectedProductObj) {
                            searchInput.value = selectedProductObj.displayName;
                            if (clearBtn) clearBtn.classList.remove('d-none');
                        } else if (!hiddenInput.value) {
                            searchInput.value = '';
                            if (clearBtn) clearBtn.classList.add('d-none');
                            options.forEach(opt => opt.classList.remove('d-none'));
                            if (noMatch) noMatch.classList.add('d-none');
                        }
                    }
                });

                // Form submission validation (if required)
                if (form && isRequired) {
                    form.addEventListener('submit', function (e) {
                        if (!hiddenInput.value) {
                            e.preventDefault();
                            searchInput.classList.add('is-invalid');
                            if (errorMsg) errorMsg.classList.remove('d-none');
                            searchInput.focus();
                            return false;
                        }
                    });
                }
            }

            // Init Tab 1: Manual Ingest Combobox
            setupProductCombobox({
                wrapperId: 'productComboboxWrapper',
                searchInputId: 'productSearchInput',
                dropdownId: 'productComboboxDropdown',
                toggleBtnId: 'dropdownToggleBtn',
                clearBtnId: 'clearSelectedProductBtn',
                chevronId: 'productDropdownChevron',
                hiddenInputId: 'ingest_product_id',
                costInputId: 'ingest_cost_price',
                noMatchId: 'noProductMatchAlert',
                errorMsgId: 'productSelectionError',
                formSelector: '#tab-manual-pane form',
                isRequired: true
            });

            // Init Tab 2: File Upload Fallback Combobox
            setupProductCombobox({
                wrapperId: 'fileProductComboboxWrapper',
                searchInputId: 'fileProductSearchInput',
                dropdownId: 'fileProductComboboxDropdown',
                toggleBtnId: 'fileDropdownToggleBtn',
                clearBtnId: 'fileClearSelectedProductBtn',
                chevronId: 'fileProductDropdownChevron',
                hiddenInputId: 'file_product_id',
                costInputId: 'file_cost_price',
                noMatchId: 'fileNoProductMatchAlert',
                formSelector: '#tab-file-pane form',
                isRequired: false
            });
            // File Upload Live Textarea Preview for Batch Ingest Modal
            const batchFileInput = document.getElementById('batch_ingest_file');
            const batchPreviewContainer = document.getElementById('batch_file_preview_container');
            const batchPreviewText = document.getElementById('batch_file_preview_text');
            const batchPreviewCount = document.getElementById('batch_file_preview_count');
            const batchPreviewStatus = document.getElementById('batch_file_preview_status');
            const batchClearBtn = document.getElementById('batch_file_clear_btn');
            const batchParsingSpinner = document.getElementById('batch_file_parsing_spinner');
            const batchSubmitText = document.getElementById('batch_import_submit_text');

            function clearBatchUploadedFile() {
                if (batchFileInput) batchFileInput.value = '';
                if (batchPreviewText) batchPreviewText.value = '';
                if (batchClearBtn) batchClearBtn.classList.add('d-none');
                if (batchParsingSpinner) batchParsingSpinner.classList.add('d-none');
                if (batchPreviewCount) batchPreviewCount.textContent = '0';
                if (batchPreviewStatus) batchPreviewStatus.textContent = '';
                if (batchSubmitText) batchSubmitText.innerHTML = 'Import Serial Numbers &rarr;';
            }

            if (batchClearBtn) {
                batchClearBtn.addEventListener('click', clearBatchUploadedFile);
            }

            function applyBatchExtractedSerials(serials, sourceInfo) {
                if (!Array.isArray(serials)) serials = [];
                const cleanList = serials.map(s => String(s).trim().toUpperCase()).filter(Boolean);

                if (batchPreviewText) batchPreviewText.value = cleanList.join('\n');
                if (batchPreviewCount) batchPreviewCount.textContent = cleanList.length;
                if (batchPreviewStatus) batchPreviewStatus.textContent = sourceInfo || `${cleanList.length} serials ready`;
                if (batchClearBtn) batchClearBtn.classList.remove('d-none');
                if (batchParsingSpinner) batchParsingSpinner.classList.add('d-none');

                if (batchSubmitText) {
                    batchSubmitText.innerHTML = cleanList.length > 0 
                        ? `Import Serial Numbers (${cleanList.length}) &rarr;` 
                        : 'Import Serial Numbers &rarr;';
                }
            }

            if (batchPreviewText) {
                batchPreviewText.addEventListener('input', function() {
                    const lines = this.value.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
                    if (batchPreviewCount) batchPreviewCount.textContent = lines.length;
                    if (batchClearBtn) {
                        if (lines.length > 0 || (batchFileInput && batchFileInput.value)) {
                            batchClearBtn.classList.remove('d-none');
                        } else {
                            batchClearBtn.classList.add('d-none');
                        }
                    }
                    if (batchSubmitText) {
                        batchSubmitText.innerHTML = lines.length > 0 
                            ? `Import Serial Numbers (${lines.length}) &rarr;` 
                            : 'Import Serial Numbers &rarr;';
                    }
                });
            }

            function parseBatchFileOnServer(file) {
                if (batchParsingSpinner) batchParsingSpinner.classList.remove('d-none');

                const fd = new FormData();
                fd.append('file', file);
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                    || document.querySelector('input[name="_token"]')?.value 
                    || '';

                fetch("{{ route('admin.serial-numbers.parse-preview') }}", {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(async res => {
                    if (batchParsingSpinner) batchParsingSpinner.classList.add('d-none');
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success && Array.isArray(data.serials)) {
                        applyBatchExtractedSerials(data.serials, `Extracted from ${file.name}`);
                    }
                })
                .catch(() => {
                    if (batchParsingSpinner) batchParsingSpinner.classList.add('d-none');
                });
            }

            if (batchFileInput) {
                batchFileInput.addEventListener('change', function(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) {
                        clearBatchUploadedFile();
                        return;
                    }

                    const fileName = file.name.toLowerCase();
                    const ext = fileName.split('.').pop();

                    if (ext === 'csv' || ext === 'txt') {
                        const reader = new FileReader();
                        reader.onload = function(evt) {
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
                                applyBatchExtractedSerials(serials, `Extracted ${serials.length} serials from ${file.name}`);
                            } else {
                                parseBatchFileOnServer(file);
                            }
                        };
                        reader.onerror = function() {
                            parseBatchFileOnServer(file);
                        };
                        reader.readAsText(file);
                    } else if (ext === 'json') {
                        const reader = new FileReader();
                        reader.onload = function(evt) {
                            try {
                                const parsed = JSON.parse(evt.target.result);
                                const list = Array.isArray(parsed) ? parsed : (parsed.serials || parsed.items || []);
                                const serials = [];
                                list.forEach(item => {
                                    const s = typeof item === 'string' ? item : (item.serial_number || item.serial || item.sn || '');
                                    if (s) serials.push(String(s).trim());
                                });
                                applyBatchExtractedSerials(serials, `Extracted ${serials.length} serials from ${file.name}`);
                            } catch (err) {
                                parseBatchFileOnServer(file);
                            }
                        };
                        reader.onerror = function() {
                            parseBatchFileOnServer(file);
                        };
                        reader.readAsText(file);
                    } else {
                        parseBatchFileOnServer(file);
                    }
                });
            }

            const manualSerialsInput = document.getElementById('manual_serials_text');
            const batchModalEl = document.getElementById('batchIngestModal');
            if (batchModalEl) {
                batchModalEl.addEventListener('hidden.bs.modal', function() {
                    clearBatchUploadedFile();
                    @if(!$errors->any())
                        if (manualSerialsInput) manualSerialsInput.value = '';
                    @endif
                });

                @if(!$errors->any())
                    batchModalEl.addEventListener('show.bs.modal', function() {
                        clearBatchUploadedFile();
                        if (manualSerialsInput) manualSerialsInput.value = '';
                    });
                @endif
            }

            @if(!$errors->any())
                if (manualSerialsInput) {
                    manualSerialsInput.value = '';
                }
            @endif
        });
    </script>
</x-app-layout>

