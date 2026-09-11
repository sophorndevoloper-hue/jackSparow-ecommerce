<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cpu" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">B2B Catalog Management</p>
                    <h1 class="h3 mb-1">Edit: {{ $product->name }}</h1>
                    <p class="text-muted mb-0">Hardware specs, B2B price tiers, warehouse stock, and serialized units.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.products.images.index', ['product_id' => $product->id]) }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-images me-1"></i> Photos ({{ $product->images->count() }})
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Products
                </a>
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
        @elseif($errors->any() && ! $errors->hasAny(['serials_text', 'warehouse_id', 'cost_price']))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="mt-3" id="productEditForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="allow_stock_mismatch" id="allowStockMismatch" value="1">

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-4" id="productEditTabs" role="tablist">
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
                        <i class="bi bi-tags me-1"></i> 3. B2B Tier Pricing ({{ $product->priceTiers->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
                        <i class="bi bi-boxes me-1"></i> 4. Stock &amp; Serials (<span id="tabSerialsCount">{{ $product->serialNumbers->count() }}</span>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="productEditTabsContent">
                <!-- TAB 1: General & Identifiers -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8 space-y-3">
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-box-seam text-primary me-2"></i>Component Overview</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Product / Component Name *</label>
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-control">
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Category *</label>
                                        <select name="category_id" required class="form-select">
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                                                <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id) == $b->id ? 'selected' : '' }}>
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
                                                <option value="{{ $m->id }}" {{ old('make_id', $product->make_id) == $m->id ? 'selected' : '' }}>
                                                    {{ $m->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Short Description</label>
                                    <textarea name="short_description" rows="2" class="form-control">{{ old('short_description', $product->short_description) }}</textarea>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold small">Full Technical Overview / Architecture Description</label>
                                    <textarea name="description" rows="6" class="form-control">{{ old('description', $product->description) }}</textarea>
                                </div>
                            </div>

                            <!-- Hardware Identifiers -->
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-upc-scan text-primary me-2"></i>Hardware Identification &amp; Compliance</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">SKU / Internal Part # *</label>
                                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="form-control font-monospace">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Manufacturer Part Number (MPN)</label>
                                        <input type="text" name="mpn" value="{{ old('mpn', $product->mpn) }}" class="form-control font-monospace">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">UPC / EAN Barcode</label>
                                        <input type="text" name="upc_ean" value="{{ old('upc_ean', $product->upc_ean) }}" class="form-control font-monospace">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">HS Tariff Code (Export)</label>
                                        <input type="text" name="hs_code" value="{{ old('hs_code', $product->hs_code) }}" class="form-control font-monospace">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">UNSPSC Code</label>
                                        <input type="text" name="unspsc_code" value="{{ old('unspsc_code', $product->unspsc_code) }}" class="form-control font-monospace">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Logistics & Photos Quick View -->
                        <div class="col-12 col-lg-4 space-y-3">
                            <div class="panel p-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="mb-0 small fw-bold text-dark"><i class="bi bi-images text-primary me-1"></i> Product Photos ({{ $product->images->count() }})</h5>
                                    <a href="{{ route('admin.products.images.index', ['product_id' => $product->id]) }}" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 11px;">
                                        Manage Photos &rarr;
                                    </a>
                                </div>

                                @if($product->images->isNotEmpty())
                                    <div class="row g-2 mb-3">
                                        @foreach($product->images as $img)
                                            <div class="col-4 position-relative">
                                                <div class="border rounded overflow-hidden shadow-sm position-relative" style="height: 75px;">
                                                    <img src="{{ $img->image_url }}" class="w-100 h-100 object-fit-cover" alt="Product Photo">
                                                    @if($img->is_primary)
                                                        <span class="badge bg-success position-absolute top-0 start-0 m-1 shadow-sm" style="font-size: 8px;">Cover</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-3 text-center border rounded bg-light mb-3">
                                        <span class="text-muted small">No photos uploaded yet.</span>
                                    </div>
                                @endif
                            </div>

                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-truck text-primary me-2"></i>Physical &amp; Logistics</h5>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Weight (kg)</label>
                                    <input type="number" step="0.001" name="weight_kg" value="{{ old('weight_kg', $product->weight_kg) }}" class="form-control">
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Length (cm)</label>
                                        <input type="number" step="0.1" name="length_cm" value="{{ old('length_cm', $product->length_cm) }}" class="form-control">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Width (cm)</label>
                                        <input type="number" step="0.1" name="width_cm" value="{{ old('width_cm', $product->width_cm) }}" class="form-control">
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-bold">Height (cm)</label>
                                        <input type="number" step="0.1" name="height_cm" value="{{ old('height_cm', $product->height_cm) }}" class="form-control">
                                    </div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_hazmat" value="1" id="isHazmat" {{ old('is_hazmat', $product->is_hazmat) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="isHazmat">Hazardous / Lithium Battery (HazMat)</label>
                                </div>
                            </div>

                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-toggle-on text-primary me-2"></i>Publishing</h5>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold small" for="isActive">Published in Store</label>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
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
                                        <input type="text" name="socket" value="{{ old('socket', $product->socket) }}" class="form-control" placeholder="e.g. AM5, LGA1700, SP5">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Chipset Compatibility</label>
                                        <input type="text" name="chipset" value="{{ old('chipset', $product->chipset) }}" class="form-control" placeholder="e.g. X670E, B650, Z790">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Form Factor</label>
                                        <input type="text" name="form_factor" value="{{ old('form_factor', $product->form_factor) }}" class="form-control" placeholder="e.g. ATX, M.2 2280">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">TDP (Watts)</label>
                                        <input type="number" name="tdp_watts" value="{{ old('tdp_watts', $product->tdp_watts) }}" class="form-control" placeholder="e.g. 120">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Recommended PSU (Watts)</label>
                                        <input type="number" name="power_requirement_watts" value="{{ old('power_requirement_watts', $product->power_requirement_watts) }}" class="form-control" placeholder="e.g. 750">
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Key-Value Specs -->
                            <div class="panel p-4 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h5 class="mb-1"><i class="bi bi-card-list text-primary me-2"></i>Extended Technical Specifications</h5>
                                        <p class="text-muted small mb-0">Manage hardware parameters (PCIe Gen, Bus Width, Base Clock, Boost Clock, VRAM, etc.).</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSpecRow()">
                                        <i class="bi bi-plus-lg me-1"></i> Add Specification
                                    </button>
                                </div>

                                <div id="specsContainer" class="space-y-2">
                                    @php
                                        $specList = $product->specs ?: ($product->specifications ?: []);
                                    @endphp
                                    @forelse($specList as $key => $val)
                                        <div class="row g-2 align-items-center mb-2 spec-row">
                                            <div class="col-5">
                                                <input type="text" name="spec_keys[]" value="{{ $key }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="spec_values[]" value="{{ $val }}" class="form-control form-control-sm">
                                            </div>
                                            <div class="col-1 text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.spec-row').remove()">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="row g-2 align-items-center mb-2 spec-row">
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
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="panel p-4 bg-light border">
                                <h6 class="fw-bold"><i class="bi bi-info-square text-primary me-2"></i>Hardware Compatibility Engine</h6>
                                <p class="small text-muted mb-0">
                                    Socket: <strong>{{ $product->socket ?: 'Not set' }}</strong><br>
                                    Chipset: <strong>{{ $product->chipset ?: 'Not set' }}</strong><br>
                                    TDP: <strong>{{ $product->tdp_watts ? $product->tdp_watts.' W' : 'Not set' }}</strong>
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
                                        <input type="number" step="0.01" name="cost_price" id="cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="form-control" oninput="calculateFloorPrice()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Safety Margin Floor %</label>
                                        <input type="number" step="0.1" name="min_margin_percentage" id="min_margin_percentage" value="{{ old('min_margin_percentage', $product->min_margin_percentage ?? '10.0') }}" class="form-control" oninput="calculateFloorPrice()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Calculated Floor Price ($)</label>
                                        <input type="text" id="calculated_floor_price" class="form-control bg-light font-monospace fw-bold text-danger" readonly value="${{ number_format($product->getFloorPrice(), 2) }}">
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">MSRP / Regular Price ($) *</label>
                                        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Promotional Sale Price ($)</label>
                                        <input type="number" step="0.01" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">MAP Price ($)</label>
                                        <input type="number" step="0.01" name="map_price" value="{{ old('map_price', $product->map_price) }}" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Wholesale Constraints -->
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-box2 text-primary me-2"></i>B2B Order Constraints</h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Minimum Order Quantity (MOQ)</label>
                                        <input type="number" name="moq" value="{{ old('moq', $product->moq ?? 1) }}" min="1" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Case Pack Multiple</label>
                                        <input type="number" name="case_pack_multiple" value="{{ old('case_pack_multiple', $product->case_pack_multiple ?? 1) }}" min="1" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Max Order Quantity</label>
                                        <input type="number" name="max_order_quantity" value="{{ old('max_order_quantity', $product->max_order_quantity) }}" min="1" class="form-control" placeholder="No limit">
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
                                            @forelse($product->priceTiers as $tier)
                                                <tr class="tier-row">
                                                    <td>
                                                        <select name="tier_customer_group_id[]" class="form-select form-select-sm">
                                                            <option value="">All Groups (General)</option>
                                                            @foreach($customerGroups as $cg)
                                                                <option value="{{ $cg->id }}" {{ $tier->customer_group_id == $cg->id ? 'selected' : '' }}>
                                                                    {{ $cg->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="tier_min_qty[]" value="{{ $tier->min_quantity }}" min="1" class="form-control form-control-sm"></td>
                                                    <td><input type="number" name="tier_max_qty[]" value="{{ $tier->max_quantity }}" placeholder="&infin; (Unlimited)" class="form-control form-control-sm"></td>
                                                    <td><input type="number" step="0.01" name="tier_price[]" value="{{ $tier->unit_price }}" class="form-control form-control-sm"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.tier-row').remove()"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @empty
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
                                                    <td><input type="number" step="0.01" name="tier_price[]" value="" placeholder="Unit Price" class="form-control form-control-sm"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.tier-row').remove()"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="panel p-4 bg-light border">
                                <h6 class="fw-bold"><i class="bi bi-shield-check text-success me-2"></i>Margin Protection Active</h6>
                                <p class="small text-muted mb-2">
                                    Current Floor Price is <strong class="text-danger">${{ number_format($product->getFloorPrice(), 2) }}</strong>. Any volume discount configured lower than this will be overridden by the pricing service:
                                </p>
                                <div class="formula-box p-2 rounded border font-monospace small mb-3">
                                    Floor = Cost &times; (1 + MinMargin%)
                                </div>
                                <p class="small text-muted mb-0">
                                    Safeguards profitability across all wholesale customer tiers.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Inventory & Serialization -->
                <div class="tab-pane fade" id="inventory" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12 col-lg-8 space-y-3">
                            <div class="panel p-4 mb-3">
                                <h5 class="mb-3"><i class="bi bi-qr-code text-primary me-2"></i>Hardware Serialization &amp; Warranty</h5>
                                <div class="form-check form-switch mb-3 p-3 bg-light rounded border">
                                    <input class="form-check-input" type="checkbox" name="requires_serial_tracking" value="1" id="requiresSerialTracking" {{ old('requires_serial_tracking', $product->requires_serial_tracking) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="requiresSerialTracking">
                                        Individual Serial Number Tracking Active (S/N)
                                    </label>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Manufacturer Warranty (Months)</label>
                                        <input type="number" name="manufacturer_warranty_months" value="{{ old('manufacturer_warranty_months', $product->manufacturer_warranty_months ?? 36) }}" min="0" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Direct Distributor Warranty (Months)</label>
                                        <input type="number" name="distributor_warranty_months" value="{{ old('distributor_warranty_months', $product->distributor_warranty_months ?? 12) }}" min="0" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold small">Warranty Period Text</label>
                                    <input type="text" name="warranty_period" value="{{ old('warranty_period', $product->warranty_period) }}" class="form-control">
                                </div>
                            </div>

                            @php
                                $inStockSerials = $inStockSerialsCount ?? $product->serialNumbers()->where('status', \App\Models\SerialNumber::STATUS_IN_STOCK)->count();
                                $totalSerials = $totalSerialsCount ?? $product->serialNumbers()->count();
                                $isSerialized = $product->requires_serial_tracking || $totalSerials > 0;
                                $stockQtyValue = $isSerialized ? $inStockSerials : old('stock_quantity', $product->stock_quantity);
                            @endphp
                            <div class="panel p-4 mb-3 position-relative" id="stockQuantitiesPanel">
                                <h5 class="mb-3"><i class="bi bi-building text-primary me-2"></i>Stock Quantities</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold small mb-0" for="stock_quantity">Total Stock Quantity *</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-light text-secondary border font-monospace fw-normal" style="font-size: 11px;">
                                                    <i class="bi bi-upc me-1"></i><span id="inStockSerialsStockNotice">{{ $inStockSerials }}</span> in stock <span class="text-muted opacity-75">(<span id="totalSerialsStockNotice">{{ $totalSerials }}</span> total)</span>
                                                </span>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle {{ $isSerialized ? '' : 'd-none' }}" id="serialSyncLockBadge" style="font-size: 11px;">
                                                    <i class="bi bi-lock-fill me-1"></i>Matches In-Stock Serials
                                                </span>
                                            </div>
                                        </div>
                                        <input type="number" name="stock_quantity" id="stock_quantity" value="{{ $stockQtyValue }}" required class="form-control {{ $isSerialized ? 'bg-light' : '' }}" min="0" {{ $isSerialized ? 'readonly' : '' }}>
                                        <div class="form-text text-muted small mt-1 {{ $isSerialized ? '' : 'd-none' }}" id="stockQtySerialHelp">
                                            <i class="bi bi-info-circle me-1"></i>Total stock quantity strictly matches in-stock serial numbers (<span class="in-stock-serials-target">{{ $inStockSerials }}</span>). Units that are sold, shipped, or RMA are excluded.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small" for="low_stock_threshold">Low Stock Alert Limit *</label>
                                        <input type="number" name="low_stock_threshold" id="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" required class="form-control" min="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Live Serial Numbers List -->
                            <div class="panel p-4 mb-3">
                                <div id="serialsTabSuccessAlert" class="alert alert-success alert-dismissible fade show d-none mb-3" role="alert"></div>

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="bi bi-upc text-primary me-2"></i>Tracked Serial Numbers 
                                            (<span id="trackedInStockCount" class="text-success fw-bold">{{ $inStockSerials }} In Stock</span> / <span id="trackedSerialsCount">{{ $totalSerials }}</span> Total)
                                        </h5>
                                        <p class="text-muted small mb-0">Registered serialized hardware units in warehouse inventory.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#ingestSerialsModal">
                                        <i class="bi bi-plus-circle me-1"></i> Add / Upload Serials
                                    </button>
                                </div>

                                <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Serial Number</th>
                                                <th>Warehouse</th>
                                                <th>Status</th>
                                                <th>Warranty Ends</th>
                                            </tr>
                                        </thead>
                                        <tbody id="productSerialsTableBody">
                                            @forelse($product->serialNumbers as $sn)
                                                <tr>
                                                    <td class="font-monospace fw-bold">{{ $sn->serial_number }}</td>
                                                    <td>{{ $sn->warehouse?->name ?? 'Default' }}</td>
                                                    <td>
                                                        @if($sn->status === 'IN_STOCK')
                                                            <span class="badge bg-success">IN_STOCK</span>
                                                        @elseif($sn->status === 'SHIPPED')
                                                            <span class="badge bg-info text-dark">SHIPPED</span>
                                                        @elseif($sn->status === 'ALLOCATED')
                                                            <span class="badge bg-warning text-dark">ALLOCATED</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $sn->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="small">{{ $sn->warranty_end_date ? $sn->warranty_end_date->format('M d, Y') : '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-3 text-muted small">No serial numbers ingested yet for this part.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="panel p-4 bg-light border">
                                <h6 class="fw-bold"><i class="bi bi-buildings text-primary me-2"></i>Multi-Warehouse Distribution</h6>
                                @forelse($product->warehouses as $wh)
                                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                        <span>{{ $wh->name }}</span>
                                        <span class="badge bg-primary">{{ $wh->pivot->quantity }} units</span>
                                    </div>
                                @empty
                                    <p class="small text-muted mb-0">No multi-warehouse stock allocations yet.</p>
                                @endforelse

                                @if($product->warehouses->isNotEmpty())
                                    <div class="d-flex justify-content-between align-items-center pt-2 mt-1 fw-bold text-dark">
                                        <span>Total in Warehouses</span>
                                        <span class="badge bg-success font-monospace">{{ $product->warehouses->sum('pivot.quantity') }} units</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Submission Bar -->
            <div class="panel p-3 mt-4 d-flex align-items-center justify-content-between shadow-sm">
                <div>
                    <span class="text-muted small">All changes to specifications, tier pricing, and hardware identifiers will be updated immediately.</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Update Hardware Part
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal for Quick Ingesting Serial Numbers -->
    <div class="modal fade" id="ingestSerialsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <form action="{{ route('admin.products.serials.store', $product->id) }}" method="POST" id="ingestSerialsForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-upc-scan text-primary me-2"></i>Add / Upload Serial Numbers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Mode Tabs -->
                        <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded border" id="ingestTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-1 small fw-semibold" id="tab-ingest-manual-btn" data-bs-toggle="pill" data-bs-target="#tab-ingest-manual" type="button" role="tab">
                                    <i class="bi bi-upc-scan me-1"></i> Scan / Manual Entry
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 small fw-semibold" id="tab-ingest-file-btn" data-bs-toggle="pill" data-bs-target="#tab-ingest-file" type="button" role="tab">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Upload File
                                </button>
                            </li>
                        </ul>

                        <!-- Receiving Warehouse and Cost fields -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-bold small mb-1">Receiving Warehouse *</label>
                                <select name="warehouse_id" id="ingest_warehouse_id" required class="form-select form-select-sm @error('warehouse_id') is-invalid @enderror">
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }} ({{ $wh->code }})</option>
                                    @endforeach
                                </select>
                                @error('warehouse_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small mb-1">Unit Cost Price ($)</label>
                                <input type="number" step="0.01" name="cost_price" id="ingest_cost_price" value="{{ old('cost_price', $product->cost_price) }}" class="form-control form-control-sm @error('cost_price') is-invalid @enderror" placeholder="Default cost">
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tab Panes -->
                        <div class="tab-content" id="ingestTabsContent">
                            <!-- TAB 1: Scan / Manual Entry -->
                            <div class="tab-pane fade show active" id="tab-ingest-manual" role="tabpanel">
                                <label class="form-label fw-bold small mb-1">Serial Numbers (Scan barcode or paste 1 per line) *</label>
                                <textarea name="serials_text" id="ingest_serials_text" rows="5" class="form-control form-control-sm font-monospace @error('serials_text') is-invalid @enderror" placeholder="SN-AMD-90001
SN-AMD-90002
SN-AMD-90003" autocomplete="off">{{ $errors->has('serials_text') ? old('serials_text') : '' }}</textarea>
                            </div>

                            <!-- TAB 2: File Upload -->
                            <div class="tab-pane fade" id="tab-ingest-file" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label fw-bold small mb-0">Select File <span class="text-danger">*</span></label>
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
                                <input type="file" name="file" id="ingest_serials_file" class="form-control form-control-sm" accept=".csv, .xlsx, .xls, .json, .txt">
                                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 11px;">
                                    <span>Supports CSV, Excel (.xlsx/.xls), JSON, TXT</span>
                                    <span>Max 10MB</span>
                                </div>

                                <!-- Live Loading Spinner -->
                                <div id="file_parsing_spinner" class="text-center py-2 d-none text-muted small mt-2 border rounded bg-light">
                                    <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                    <span>Reading and extracting serial numbers from file...</span>
                                </div>

                                <!-- Live Extracted Serial Numbers Text Area Preview -->
                                <div class="mt-3 d-none" id="file_preview_container">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label fw-bold small mb-0 text-dark">
                                            <i class="bi bi-list-check text-primary me-1"></i>Extracted Serial Numbers (<span id="file_preview_count" class="text-primary fw-bold">0</span> found)
                                        </label>
                                        <button type="button" class="btn btn-link text-danger p-0 text-decoration-none small" style="font-size: 11.5px;" id="file_preview_clear_btn">
                                            <i class="bi bi-x-circle me-1"></i>Clear File
                                        </button>
                                    </div>
                                    <textarea name="file_preview_text" id="ingest_file_preview_text" rows="6" class="form-control form-control-sm font-monospace" placeholder="Serial numbers will appear here as a list..."></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 11px;">
                                        <span><i class="bi bi-pencil-square me-1"></i>Review or edit serials above before saving</span>
                                        <span id="file_preview_status" class="text-success fw-semibold"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Feedback Container -->
                        <div class="invalid-feedback d-block mt-2 fw-semibold d-none" id="ingest_serials_error"></div>
                        @error('serials_text')
                            <div class="invalid-feedback d-block mt-2 fw-semibold" id="server_serials_error">
                                <i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold" id="ingestSerialsSubmitBtn">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="ingestSerialsSpinner" role="status" aria-hidden="true"></span>
                            <span id="ingestSerialsSubmitText">Register Serials &rarr;</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
                <td><input type="number" name="tier_min_qty[]" value="10" min="1" class="form-control form-control-sm"></td>
                <td><input type="number" name="tier_max_qty[]" value="" placeholder="&infin; (Unlimited)" class="form-control form-control-sm"></td>
                <td><input type="number" step="0.01" name="tier_price[]" value="" placeholder="Unit Price" class="form-control form-control-sm"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="this.closest('.tier-row').remove()"><i class="bi bi-trash"></i></button>
                </td>
            `;
            container.appendChild(row);
        }

        document.addEventListener('DOMContentLoaded', function() {
            calculateFloorPrice();

            // Tab State Persistence across reload/navigation
            const storageTabKey = 'adminProductActiveTab_{{ $product->id }}';
            const savedTab = sessionStorage.getItem(storageTabKey) || window.location.hash;

            if (savedTab) {
                const tabTarget = document.querySelector(`#productEditTabs button[data-bs-target="${savedTab}"]`);
                if (tabTarget && window.bootstrap) {
                    new bootstrap.Tab(tabTarget).show();
                }
            }

            document.querySelectorAll('#productEditTabs button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', function(e) {
                    const target = e.target.getAttribute('data-bs-target');
                    if (target) {
                        sessionStorage.setItem(storageTabKey, target);
                        if (window.history && window.history.replaceState) {
                            window.history.replaceState(null, null, target);
                        } else {
                            window.location.hash = target;
                        }
                    }
                });
            });

            // Smooth AJAX Serial Ingest (Barcode scan or File Upload with Live Textarea Preview)
            const ingestForm = document.getElementById('ingestSerialsForm');
            const fileInput = document.getElementById('ingest_serials_file');
            const filePreviewContainer = document.getElementById('file_preview_container');
            const filePreviewText = document.getElementById('ingest_file_preview_text');
            const filePreviewCount = document.getElementById('file_preview_count');
            const filePreviewStatus = document.getElementById('file_preview_status');
            const filePreviewClearBtn = document.getElementById('file_preview_clear_btn');
            const fileParsingSpinner = document.getElementById('file_parsing_spinner');
            const serialsInput = document.getElementById('ingest_serials_text');
            const submitBtn = document.getElementById('ingestSerialsSubmitBtn');
            const submitText = document.getElementById('ingestSerialsSubmitText');
            const spinner = document.getElementById('ingestSerialsSpinner');
            const serialsError = document.getElementById('ingest_serials_error');
            const serverError = document.getElementById('server_serials_error');

            function clearUploadedFile() {
                if (fileInput) fileInput.value = '';
                if (filePreviewText) filePreviewText.value = '';
                if (filePreviewContainer) filePreviewContainer.classList.add('d-none');
                if (fileParsingSpinner) fileParsingSpinner.classList.add('d-none');
                if (filePreviewCount) filePreviewCount.textContent = '0';
                if (filePreviewStatus) filePreviewStatus.textContent = '';
                if (submitText) submitText.innerHTML = 'Register Serials &rarr;';
            }

            if (filePreviewClearBtn) {
                filePreviewClearBtn.addEventListener('click', clearUploadedFile);
            }

            function applyExtractedSerials(serials, sourceInfo) {
                if (!Array.isArray(serials)) serials = [];
                const cleanList = serials.map(s => String(s).trim().toUpperCase()).filter(Boolean);

                if (filePreviewText) filePreviewText.value = cleanList.join('\n');
                if (serialsInput) serialsInput.value = cleanList.join('\n');
                if (filePreviewCount) filePreviewCount.textContent = cleanList.length;
                if (filePreviewStatus) filePreviewStatus.textContent = sourceInfo || `${cleanList.length} serials ready`;
                if (filePreviewContainer) filePreviewContainer.classList.remove('d-none');
                if (fileParsingSpinner) fileParsingSpinner.classList.add('d-none');

                if (submitText) {
                    submitText.innerHTML = cleanList.length > 0 
                        ? `Register Serials (${cleanList.length}) &rarr;` 
                        : 'Register Serials &rarr;';
                }
            }

            if (filePreviewText) {
                filePreviewText.addEventListener('input', function() {
                    const lines = this.value.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
                    if (filePreviewCount) filePreviewCount.textContent = lines.length;
                    if (serialsInput) serialsInput.value = this.value;
                    if (submitText) {
                        submitText.innerHTML = lines.length > 0 
                            ? `Register Serials (${lines.length}) &rarr;` 
                            : 'Register Serials &rarr;';
                    }
                });
            }

            function parseFileOnServer(file) {
                if (fileParsingSpinner) fileParsingSpinner.classList.remove('d-none');
                if (filePreviewContainer) filePreviewContainer.classList.add('d-none');

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
                    if (fileParsingSpinner) fileParsingSpinner.classList.add('d-none');
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data.success && Array.isArray(data.serials)) {
                        applyExtractedSerials(data.serials, `Extracted from ${file.name}`);
                    } else {
                        const err = data.message || 'Unable to parse serial numbers from file.';
                        if (serialsError) {
                            serialsError.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> ${err}`;
                            serialsError.classList.remove('d-none');
                        }
                    }
                })
                .catch(err => {
                    if (fileParsingSpinner) fileParsingSpinner.classList.add('d-none');
                    if (serialsError) {
                        serialsError.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> ${err.message || 'File parsing error'}`;
                        serialsError.classList.remove('d-none');
                    }
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) {
                        clearUploadedFile();
                        return;
                    }

                    if (serialsError) {
                        serialsError.classList.add('d-none');
                        serialsError.innerHTML = '';
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
                                applyExtractedSerials(serials, `Extracted from ${file.name}`);
                            } else {
                                parseFileOnServer(file);
                            }
                        };
                        reader.onerror = function() {
                            parseFileOnServer(file);
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
                                applyExtractedSerials(serials, `Extracted from ${file.name}`);
                            } catch (err) {
                                parseFileOnServer(file);
                            }
                        };
                        reader.onerror = function() {
                            parseFileOnServer(file);
                        };
                        reader.readAsText(file);
                    } else {
                        // Excel (.xlsx, .xls)
                        parseFileOnServer(file);
                    }
                });
            }

            const modalEl = document.getElementById('ingestSerialsModal');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function() {
                    clearUploadedFile();
                    if (serialsInput) serialsInput.value = '';
                });
                modalEl.addEventListener('show.bs.modal', function() {
                    clearUploadedFile();
                    if (serialsInput) serialsInput.value = '';
                });
            }

            if (ingestForm) {
                ingestForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // If file preview textarea has content, sync to serialsInput
                    if (filePreviewText && filePreviewText.value.trim() !== '') {
                        if (serialsInput) serialsInput.value = filePreviewText.value.trim();
                    }

                    // Reset previous error messages
                    if (serialsInput) serialsInput.classList.remove('is-invalid');
                    if (fileInput) fileInput.classList.remove('is-invalid');
                    if (serialsError) {
                        serialsError.classList.add('d-none');
                        serialsError.innerHTML = '';
                    }
                    if (serverError) serverError.remove();

                    // Check if file or text is provided
                    const hasText = serialsInput && serialsInput.value.trim() !== '';
                    const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

                    if (!hasText && !hasFile) {
                        if (serialsError) {
                            serialsError.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> Please scan/enter serial numbers or choose a file to upload.';
                            serialsError.classList.remove('d-none');
                        }
                        return;
                    }

                    // Show loading state
                    submitBtn.disabled = true;
                    if (spinner) spinner.classList.remove('d-none');
                    if (submitText) submitText.textContent = hasFile && !hasText ? 'Uploading & Processing...' : 'Registering...';

                    const formData = new FormData(ingestForm);
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                        || ingestForm.querySelector('input[name="_token"]')?.value 
                        || '';

                    fetch(ingestForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        }
                    })
                    .then(async response => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            let errorMsg = data.message || 'An error occurred while ingesting serial numbers.';
                            if (data.errors) {
                                if (data.errors.serials_text) {
                                    errorMsg = Array.isArray(data.errors.serials_text) ? data.errors.serials_text[0] : data.errors.serials_text;
                                } else if (data.errors.file) {
                                    errorMsg = Array.isArray(data.errors.file) ? data.errors.file[0] : data.errors.file;
                                }
                            }

                            if (serialsError) {
                                serialsError.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> ${errorMsg}`;
                                serialsError.classList.remove('d-none');
                            }
                            return;
                        }

                        // Hide modal
                        if (modalEl && window.bootstrap) {
                            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            bsModal.hide();
                        }

                        // Reset inputs
                        if (serialsInput) serialsInput.value = '';
                        clearUploadedFile();

                        // Update table in Tab 4
                        if (data.serials && data.serials.length > 0) {
                            const tbody = document.getElementById('productSerialsTableBody');
                            if (tbody) {
                                const emptyRow = tbody.querySelector('tr td[colspan]');
                                if (emptyRow) emptyRow.closest('tr').remove();

                                data.serials.forEach(sn => {
                                    const tr = document.createElement('tr');
                                    tr.className = 'table-success-subtle';
                                    tr.innerHTML = `
                                        <td class="font-monospace fw-bold">${sn.serial_number}</td>
                                        <td>${sn.warehouse_name}</td>
                                        <td><span class="badge bg-success">IN_STOCK</span></td>
                                        <td class="small">${sn.warranty_end_date}</td>
                                    `;
                                    tbody.prepend(tr);
                                });
                            }
                        }

                        // Update badge counts and stock quantity
                        const inStockCount = data.in_stock_count !== undefined ? data.in_stock_count : (data.total_count || 0);
                        const totalCount = data.total_count !== undefined ? data.total_count : inStockCount;

                        const countMap = {
                            'trackedSerialsCount': totalCount,
                            'tabSerialsCount': inStockCount,
                            'inStockSerialsStockNotice': inStockCount,
                            'totalSerialsStockNotice': totalCount,
                            'trackedInStockCount': inStockCount + ' In Stock',
                        };

                        Object.entries(countMap).forEach(([id, val]) => {
                            const el = document.getElementById(id);
                            if (el) el.textContent = val;
                        });

                        document.querySelectorAll('.in-stock-serials-target').forEach(el => el.textContent = inStockCount);

                        updateSerialSyncLock(inStockCount, totalCount);

                        // Show success alert in Stock & Serials tab
                        const successAlert = document.getElementById('serialsTabSuccessAlert');
                        if (successAlert) {
                            successAlert.innerHTML = `
                                <i class="bi bi-check-circle-fill me-2"></i> ${data.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            successAlert.classList.remove('d-none');
                        }
                    })
                    .catch(err => {
                        if (serialsError) {
                            serialsError.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1 text-danger"></i> ${err.message || 'Network error occurred.'}`;
                            serialsError.classList.remove('d-none');
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        if (spinner) spinner.classList.add('d-none');
                        if (submitText) submitText.innerHTML = 'Register Serials &rarr;';
                    });
                });
            }

            // Sync and lock stock quantity directly to in-stock serial numbers count
            function updateSerialSyncLock(overrideInStock, overrideTotal) {
                const trackingCheckbox = document.getElementById('requiresSerialTracking');
                const stockInput = document.getElementById('stock_quantity');
                const lockBadge = document.getElementById('serialSyncLockBadge');
                const helpText = document.getElementById('stockQtySerialHelp');

                const inStockNotice = document.getElementById('inStockSerialsStockNotice');
                const totalNotice = document.getElementById('totalSerialsStockNotice');

                let inStockCount = overrideInStock !== undefined 
                    ? overrideInStock 
                    : (inStockNotice ? parseInt(inStockNotice.textContent.trim(), 10) || 0 : 0);
                let totalCount = overrideTotal !== undefined 
                    ? overrideTotal 
                    : (totalNotice ? parseInt(totalNotice.textContent.trim(), 10) || 0 : inStockCount);

                const isTracked = (trackingCheckbox && trackingCheckbox.checked) || totalCount > 0;

                if (stockInput) {
                    if (isTracked) {
                        stockInput.value = inStockCount;
                        stockInput.readOnly = true;
                        stockInput.classList.add('bg-light');
                        if (lockBadge) lockBadge.classList.remove('d-none');
                        if (helpText) helpText.classList.remove('d-none');
                    } else {
                        stockInput.readOnly = false;
                        stockInput.classList.remove('bg-light');
                        if (lockBadge) lockBadge.classList.add('d-none');
                        if (helpText) helpText.classList.add('d-none');
                    }
                }

                document.querySelectorAll('.in-stock-serials-target').forEach(el => el.textContent = inStockCount);
            }

            const requiresSerialCheckbox = document.getElementById('requiresSerialTracking');
            if (requiresSerialCheckbox) {
                requiresSerialCheckbox.addEventListener('change', updateSerialSyncLock);
            }

            // Run on load
            updateSerialSyncLock();
        });
    </script>
</x-app-layout>
