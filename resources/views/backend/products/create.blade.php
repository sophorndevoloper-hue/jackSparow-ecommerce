<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cpu" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">B2B Catalog Management</p>
                    <h1 class="h3 mb-1">Add Hardware Component</h1>
                    <p class="text-muted mb-0">Configure specifications, B2B tier pricing, serialization, and hardware identifiers.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Products
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

        <form id="productCreateForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-4" id="productFormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="bi bi-info-circle me-1"></i> 1. General &amp; Identifiers
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">
                        <i class="bi bi-sliders me-1"></i> 2. Technical Specs
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="b2b-tab" data-bs-toggle="tab" data-bs-target="#b2b" type="button" role="tab">
                        <i class="bi bi-tags me-1"></i> 3. B2B Tier Pricing &amp; Margin Floor
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="bi bi-boxes me-1"></i> 4. Inventory &amp; Serialization
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="productFormTabsContent">
                <!-- TAB 1: General & Identifiers -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8 space-y-3">
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-box-seam text-primary me-2"></i>Component Overview</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Product / Component Name *</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. AMD Ryzen 9 7950X3D Desktop Processor">
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Category *</label>
                                        <select name="category_id" required class="form-select">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Manufacturer / Brand</label>
                                        <select name="brand_id" class="form-select">
                                            <option value="">Select Brand (Optional)</option>
                                            @foreach($brands as $b)
                                                <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>
                                                    {{ $b->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Hardware Make / OEM</label>
                                        <select name="make_id" class="form-select">
                                            <option value="">Select Make (Optional)</option>
                                            @foreach($makes as $m)
                                                <option value="{{ $m->id }}" {{ old('make_id') == $m->id ? 'selected' : '' }}>
                                                    {{ $m->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Short Description</label>
                                    <textarea name="short_description" rows="2" class="form-control" placeholder="Brief hardware highlight for wholesale catalog...">{{ old('short_description') }}</textarea>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold small">Full Technical Overview / Architecture Description</label>
                                    <textarea name="description" rows="6" class="form-control" placeholder="Detailed architectural breakdown, PCIe lane topologies, and enterprise specifications...">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            <!-- Hardware Identifiers -->
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-upc-scan text-primary me-2"></i>Hardware Identification &amp; Compliance</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">SKU / Internal Part # *</label>
                                        <input type="text" name="sku" value="{{ old('sku') }}" required class="form-control font-monospace" placeholder="e.g. CPU-AMD-7950X3D">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Manufacturer Part Number (MPN)</label>
                                        <input type="text" name="mpn" value="{{ old('mpn') }}" class="form-control font-monospace" placeholder="e.g. 100-100000908WOF">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">UPC / EAN Barcode</label>
                                        <input type="text" name="upc_ean" value="{{ old('upc_ean') }}" class="form-control font-monospace" placeholder="e.g. 730143314916">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">HS Tariff Code (Export)</label>
                                        <input type="text" name="hs_code" value="{{ old('hs_code', '8473.30.1180') }}" class="form-control font-monospace" placeholder="e.g. 8473.30.1180">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">UNSPSC Code</label>
                                        <input type="text" name="unspsc_code" value="{{ old('unspsc_code', '43201503') }}" class="form-control font-monospace" placeholder="e.g. 43201503">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Logistics & Status -->
                        <div class="col-12 col-lg-4 space-y-3">
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-truck text-primary me-2"></i>Physical &amp; Logistics</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Weight (kg)</label>
                                    <input type="number" step="0.001" name="weight_kg" value="{{ old('weight_kg', '0.250') }}" class="form-control" placeholder="0.250">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Length (cm)</label>
                                        <input type="number" step="0.1" name="length_cm" value="{{ old('length_cm', '13.0') }}" class="form-control">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Width (cm)</label>
                                        <input type="number" step="0.1" name="width_cm" value="{{ old('width_cm', '13.0') }}" class="form-control">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Height (cm)</label>
                                        <input type="number" step="0.1" name="height_cm" value="{{ old('height_cm', '4.0') }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_hazmat" value="1" id="isHazmat" {{ old('is_hazmat') ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="isHazmat">Hazardous / Lithium Battery (HazMat)</label>
                                </div>
                            </div>

                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-toggle-on text-primary me-2"></i>Publishing</h5>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                    <label class="form-check-label fw-bold small" for="isActive">Published in Store</label>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured">
                                    <label class="form-check-label fw-bold small" for="isFeatured">Featured on B2B Portal</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Technical Specifications -->
                <div class="tab-pane fade" id="specs" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-cpu-fill text-primary me-2"></i>Core Architecture &amp; Standard Specs</h5>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Socket Standard</label>
                                        <input type="text" name="socket" value="{{ old('socket') }}" class="form-control" placeholder="e.g. AM5, LGA1700, SP5, TR5">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Chipset Compatibility</label>
                                        <input type="text" name="chipset" value="{{ old('chipset') }}" class="form-control" placeholder="e.g. X670E, B650, Z790, W790">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Form Factor</label>
                                        <input type="text" name="form_factor" value="{{ old('form_factor') }}" class="form-control" placeholder="e.g. ATX, M-ATX, M.2 2280, 2U">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">TDP (Watts)</label>
                                        <input type="number" name="tdp_watts" value="{{ old('tdp_watts') }}" class="form-control" placeholder="e.g. 120">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Recommended PSU (Watts)</label>
                                        <input type="number" name="power_requirement_watts" value="{{ old('power_requirement_watts') }}" class="form-control" placeholder="e.g. 750">
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Key-Value Specs -->
                            <div class="panel p-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h5 class="mb-1"><i class="bi bi-card-list text-primary me-2"></i>Extended Technical Specifications</h5>
                                        <p class="text-muted small mb-0">Add granular hardware parameters (PCIe Gen, Bus Width, Base Clock, Boost Clock, VRAM, Cache, etc.).</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSpecRow()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Specification
                                    </button>
                                </div>

                                <div id="specsContainer" class="space-y-2">
                                    <div class="row g-2 align-items-center mb-2 spec-row">
                                        <div class="col-5">
                                            <input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="Spec name (e.g. Cores / Threads)" value="Cores / Threads">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Spec value (e.g. 16 Cores / 32 Threads)">
                                        </div>
                                        <div class="col-1 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.spec-row').remove()">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-center mb-2 spec-row">
                                        <div class="col-5">
                                            <input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="Spec name" value="Base / Boost Clock">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Spec value (e.g. 4.2 GHz / 5.7 GHz)">
                                        </div>
                                        <div class="col-1 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.spec-row').remove()">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-center mb-2 spec-row">
                                        <div class="col-5">
                                            <input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="Spec name" value="Memory Support">
                                        </div>
                                        <div class="col-6">
                                            <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Spec value (e.g. DDR5-5200)">
                                        </div>
                                        <div class="col-1 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.spec-row').remove()">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="panel p-4 bg-light border">
                                <h6 class="fw-bold"><i class="bi bi-info-square text-primary me-2"></i>Compatibility Rules</h6>
                                <p class="small text-muted mb-0">
                                    Specifying <strong>Socket</strong> and <strong>TDP</strong> will enable the automatic Bill-of-Materials (BOM) validation engine to check compatibility with Motherboards, RAM generations, and PSUs.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: B2B Tier Pricing & Margin Floor -->
                <div class="tab-pane fade" id="b2b" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <!-- Base & Cost Prices -->
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-cash-stack text-primary me-2"></i>Base Pricing &amp; Margin Safeguards</h5>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Cost Price (COGS) ($)</label>
                                        <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', '500.00') }}" class="form-control" placeholder="500.00" oninput="calculateFloorPrice()">
                                        <div class="form-text small">Acquisition cost per unit</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Safety Margin Floor %</label>
                                        <input type="number" step="0.1" name="min_margin_percentage" id="min_margin_percentage" value="{{ old('min_margin_percentage', '12.0') }}" class="form-control" placeholder="12.0" oninput="calculateFloorPrice()">
                                        <div class="form-text small">Minimum profit margin guard</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Calculated Floor Price ($)</label>
                                        <input type="text" id="calculated_floor_price" class="form-control bg-light font-monospace fw-bold text-danger" readonly value="$560.00">
                                        <div class="form-text small text-danger">Strict minimum sale limit</div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">MSRP / Regular Price ($) *</label>
                                        <input type="number" step="0.01" name="price" value="{{ old('price', '699.00') }}" required class="form-control" placeholder="699.00">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Promotional Sale Price ($)</label>
                                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price') }}" class="form-control" placeholder="649.00">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">MAP Price ($)</label>
                                        <input type="number" step="0.01" name="map_price" value="{{ old('map_price', '649.00') }}" class="form-control" placeholder="649.00">
                                        <div class="form-text small">Minimum Advertised Price</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Wholesale Order Rules -->
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-box2 text-primary me-2"></i>B2B Order Constraints</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Minimum Order Quantity (MOQ)</label>
                                        <input type="number" name="moq" value="{{ old('moq', 1) }}" min="1" class="form-control">
                                        <div class="form-text small">Default 1 for retail/samples</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Case Pack Multiple</label>
                                        <input type="number" name="case_pack_multiple" value="{{ old('case_pack_multiple', 1) }}" min="1" class="form-control">
                                        <div class="form-text small">Order in multiples (e.g. 5, 10, 20)</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Max Order Quantity</label>
                                        <input type="number" name="max_order_quantity" value="{{ old('max_order_quantity') }}" min="1" class="form-control" placeholder="No limit">
                                        <div class="form-text small">Cap per individual order</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Price Tiers Table -->
                            <div class="panel p-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h5 class="mb-1"><i class="bi bi-graph-down-arrow text-primary me-2"></i>Volume Pricing Breaks</h5>
                                        <p class="text-muted small mb-0">Configure quantity discount tiers for specific B2B customer groups.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addTierRow()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Pricing Tier
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 25%;">Target Group</th>
                                                <th style="width: 20%;">Min Qty</th>
                                                <th style="width: 20%;">Max Qty</th>
                                                <th style="width: 25%;">Unit Price ($)</th>
                                                <th style="width: 10%;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tiersContainer">
                                            <!-- Default 1-9 Tier -->
                                            <tr class="tier-row">
                                                <td>
                                                    <select name="tier_customer_group_id[]" class="form-select form-select-sm">
                                                        <option value="">All Groups (General)</option>
                                                        @foreach($customerGroups as $cg)
                                                            <option value="{{ $cg->id }}">{{ $cg->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" name="tier_min_qty[]" value="10" min="1" class="form-control form-control-sm"></td>
                                                <td><input type="number" name="tier_max_qty[]" value="49" min="1" class="form-control form-control-sm"></td>
                                                <td><input type="number" step="0.01" name="tier_price[]" value="620.00" class="form-control form-control-sm"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.tier-row').remove()"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                            <!-- 50+ Volume Tier -->
                                            <tr class="tier-row">
                                                <td>
                                                    <select name="tier_customer_group_id[]" class="form-select form-select-sm">
                                                        <option value="">All Groups (General)</option>
                                                        @foreach($customerGroups as $cg)
                                                            <option value="{{ $cg->id }}">{{ $cg->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td><input type="number" name="tier_min_qty[]" value="50" min="1" class="form-control form-control-sm"></td>
                                                <td><input type="number" name="tier_max_qty[]" value="" placeholder="&infin; (Unlimited)" class="form-control form-control-sm"></td>
                                                <td><input type="number" step="0.01" name="tier_price[]" value="580.00" class="form-control form-control-sm"></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.tier-row').remove()"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="panel p-4 bg-light border">
                                <h6 class="fw-bold"><i class="bi bi-shield-check text-success me-2"></i>Margin Floor Protection Active</h6>
                                <p class="small text-muted mb-2">
                                    The pricing calculation engine automatically prevents orders from executing if a volume discount falls below the minimum margin threshold:
                                </p>
                                <div class="formula-box p-2 rounded border font-monospace small mb-3">
                                    Floor = Cost &times; (1 + MinMargin%)
                                </div>
                                <p class="small text-muted mb-0">
                                    If an enterprise discount drops below the floor, the system will raise it to the floor price and alert the account executive.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Inventory & Serialization -->
                <div class="tab-pane fade" id="inventory" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8">
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-qr-code text-primary me-2"></i>Hardware Serialization Settings</h5>
                                <div class="form-check form-switch mb-3 p-3 bg-light rounded border">
                                    <input class="form-check-input" type="checkbox" name="requires_serial_tracking" value="1" id="requiresSerialTracking" checked>
                                    <label class="form-check-label fw-bold" for="requiresSerialTracking">
                                        Enable Individual Serial Number Tracking (S/N)
                                    </label>
                                    <div class="text-muted small mt-1">
                                        Recommended for high-value components (CPUs, GPUs, Motherboards, Server Hardware). Enables warranty activation and RMA tracking.
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Manufacturer Warranty (Months)</label>
                                        <input type="number" name="manufacturer_warranty_months" value="{{ old('manufacturer_warranty_months', 36) }}" min="0" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Direct Distributor Warranty (Months)</label>
                                        <input type="number" name="distributor_warranty_months" value="{{ old('distributor_warranty_months', 12) }}" min="0" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold small">Warranty Summary Text</label>
                                    <input type="text" name="warranty_period" value="{{ old('warranty_period', '3-Year Manufacturer Direct Warranty') }}" class="form-control">
                                </div>
                            </div>

                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-building text-primary me-2"></i>Stock Level Initial Setup</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Initial Stock Quantity *</label>
                                        <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 25) }}" required class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Low Stock Alert Threshold *</label>
                                        <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" required class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="panel p-4 bg-light border">
                                <h6 class="fw-bold"><i class="bi bi-info-circle text-primary me-2"></i>Serial Number Registration</h6>
                                <p class="small text-muted mb-0">
                                    Once this product is created, you can bulk ingest serial numbers via barcode scanner or CSV in the product edit screen or through the Serial Number Registry.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Submission Bar / Wizard Stepper -->
            <div class="panel p-3 mt-4 d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm" id="productFormBottomBar">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" id="tabStepBadge">Step 1 of 4</span>
                    <span class="text-muted small" id="tabStepHelp">Section 1: General &amp; Identifiers</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- Previous Step Button -->
                    <button type="button" id="btnPrevTab" class="btn btn-outline-secondary" style="display: none;">
                        <i class="bi bi-arrow-left me-1"></i> Previous
                    </button>

                    <!-- Next Step Button (Shown on Tabs 1, 2, 3) -->
                    <button type="button" id="btnNextTab" class="btn btn-primary fw-bold shadow-sm">
                        <span id="btnNextText">Next: Technical Specs</span> <i class="bi bi-arrow-right ms-1"></i>
                    </button>

                    <!-- Save Buttons (Shown ONLY on Tab 4: Inventory & Serialization) -->
                    <div id="saveButtonsGroup" class="d-flex gap-2" style="display: none !important;">
                        <button type="submit" name="action" value="save" class="btn btn-outline-secondary">
                            <i class="bi bi-check2 me-1"></i> Save Hardware Part
                        </button>
                        <button type="submit" name="action" value="save_and_upload" class="btn btn-primary fw-bold shadow-sm">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Save &amp; Upload Images &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function calculateFloorPrice() {
            const cost = parseFloat(document.getElementById('cost_price').value) || 0;
            const margin = parseFloat(document.getElementById('min_margin_percentage').value) || 0;
            const floor = cost * (1 + (margin / 100));
            document.getElementById('calculated_floor_price').value = '$' + floor.toFixed(2);
        }

        function addSpecRow() {
            const container = document.getElementById('specsContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 align-items-center mb-2 spec-row';
            row.innerHTML = `
                <div class="col-5">
                    <input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="Spec name">
                </div>
                <div class="col-6">
                    <input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Spec value">
                </div>
                <div class="col-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.spec-row').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
        }

        function addTierRow() {
            const container = document.getElementById('tiersContainer');
            const row = document.createElement('tr');
            row.className = 'tier-row';
            row.innerHTML = `
                <td>
                    <select name="tier_customer_group_id[]" class="form-select form-select-sm">
                        <option value="">All Groups (General)</option>
                        @foreach($customerGroups as $cg)
                            <option value="{{ $cg->id }}">{{ $cg->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="tier_min_qty[]" value="100" min="1" class="form-control form-control-sm"></td>
                <td><input type="number" name="tier_max_qty[]" value="" placeholder="&infin; (Unlimited)" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="tier_price[]" value="" placeholder="550.00" class="form-control form-control-sm"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.tier-row').remove()"><i class="bi bi-trash"></i></button>
                </td>
            `;
            container.appendChild(row);
        }

        // Stepper Navigation Logic
        const formTabs = [
            { id: 'general', trigger: '#general-tab', target: '#general', label: 'General & Identifiers', nextLabel: 'Next: Technical Specs' },
            { id: 'specs', trigger: '#specs-tab', target: '#specs', label: 'Technical Specs', nextLabel: 'Next: B2B Pricing' },
            { id: 'b2b', trigger: '#b2b-tab', target: '#b2b', label: 'B2B Tier Pricing & Margin Floor', nextLabel: 'Next: Inventory & Serialization' },
            { id: 'inventory', trigger: '#inventory-tab', target: '#inventory', label: 'Inventory & Serialization', nextLabel: '' },
        ];

        let currentStepIndex = 0;
        const btnPrevTab = document.getElementById('btnPrevTab');
        const btnNextTab = document.getElementById('btnNextTab');
        const btnNextText = document.getElementById('btnNextText');
        const saveButtonsGroup = document.getElementById('saveButtonsGroup');
        const tabStepBadge = document.getElementById('tabStepBadge');
        const tabStepHelp = document.getElementById('tabStepHelp');

        function updateStepNavigation(targetSelector) {
            const targetId = (targetSelector || '#general').replace('#', '');
            const index = formTabs.findIndex(t => t.id === targetId);
            if (index === -1) return;

            currentStepIndex = index;
            const isFirst = (currentStepIndex === 0);
            const isLast = (currentStepIndex === formTabs.length - 1);

            // Update badge and helper description
            if (tabStepBadge) {
                tabStepBadge.textContent = `Step ${currentStepIndex + 1} of ${formTabs.length}`;
            }
            if (tabStepHelp) {
                tabStepHelp.textContent = isLast
                    ? 'Section 4: Inventory & Serialization — Final Step'
                    : `Section ${currentStepIndex + 1}: ${formTabs[currentStepIndex].label}`;
            }

            // Previous Button visibility
            if (btnPrevTab) {
                btnPrevTab.style.display = isFirst ? 'none' : 'inline-flex';
            }

            // Next Button vs Save Buttons
            if (isLast) {
                if (btnNextTab) btnNextTab.style.display = 'none';
                if (saveButtonsGroup) saveButtonsGroup.style.setProperty('display', 'flex', 'important');
            } else {
                if (btnNextTab) {
                    btnNextTab.style.display = 'inline-flex';
                    if (btnNextText) {
                        btnNextText.textContent = formTabs[currentStepIndex].nextLabel;
                    }
                }
                if (saveButtonsGroup) {
                    saveButtonsGroup.style.setProperty('display', 'none', 'important');
                }
            }
        }

        // Listen for tab switching via clicking tabs directly
        document.querySelectorAll('#productFormTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
            tabBtn.addEventListener('shown.bs.tab', function (e) {
                const target = e.target.getAttribute('data-bs-target');
                updateStepNavigation(target);
            });
        });

        // Next Button click handler: validates inputs of current section before advancing
        if (btnNextTab) {
            btnNextTab.addEventListener('click', function () {
                const currentTab = formTabs[currentStepIndex];
                const currentPane = document.querySelector(currentTab.target);

                if (currentPane) {
                    const inputs = currentPane.querySelectorAll('input, select, textarea');
                    for (const input of inputs) {
                        if (!input.checkValidity()) {
                            input.reportValidity();
                            return;
                        }
                    }
                }

                if (currentStepIndex < formTabs.length - 1) {
                    const nextTab = formTabs[currentStepIndex + 1];
                    const nextTabBtn = document.querySelector(nextTab.trigger);
                    if (nextTabBtn) {
                        bootstrap.Tab.getOrCreateInstance(nextTabBtn).show();
                        window.scrollTo({ top: 120, behavior: 'smooth' });
                    }
                }
            });
        }

        // Previous Button click handler
        if (btnPrevTab) {
            btnPrevTab.addEventListener('click', function () {
                if (currentStepIndex > 0) {
                    const prevTab = formTabs[currentStepIndex - 1];
                    const prevTabBtn = document.querySelector(prevTab.trigger);
                    if (prevTabBtn) {
                        bootstrap.Tab.getOrCreateInstance(prevTabBtn).show();
                        window.scrollTo({ top: 120, behavior: 'smooth' });
                    }
                }
            });
        }

        // Form submission guard: if any input across inactive tabs is invalid, switch to that tab so user sees it
        const createForm = document.getElementById('productCreateForm') || document.querySelector('form');
        if (createForm) {
            createForm.addEventListener('submit', function (e) {
                if (!createForm.checkValidity()) {
                    const firstInvalid = createForm.querySelector(':invalid');
                    if (firstInvalid) {
                        const parentPane = firstInvalid.closest('.tab-pane');
                        if (parentPane && !parentPane.classList.contains('active')) {
                            e.preventDefault();
                            const trigger = document.querySelector(`[data-bs-target="#${parentPane.id}"]`);
                            if (trigger) {
                                bootstrap.Tab.getOrCreateInstance(trigger).show();
                                setTimeout(() => {
                                    firstInvalid.focus();
                                    firstInvalid.reportValidity();
                                }, 150);
                            }
                        }
                    }
                }
            });
        }

        // Auto-switch to tab containing server error if any exists on load
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', function () {
                const firstErrorField = document.querySelector('.is-invalid, input:invalid, select:invalid, textarea:invalid');
                if (firstErrorField) {
                    const errPane = firstErrorField.closest('.tab-pane');
                    if (errPane) {
                        const errTabBtn = document.querySelector(`[data-bs-target="#${errPane.id}"]`);
                        if (errTabBtn) {
                            bootstrap.Tab.getOrCreateInstance(errTabBtn).show();
                        }
                    }
                }
            });
        @endif

        // Initialize floor price calculation & step navigation on load
        document.addEventListener('DOMContentLoaded', function () {
            calculateFloorPrice();
            updateStepNavigation('#general');
        });
    </script>
</x-app-layout>
