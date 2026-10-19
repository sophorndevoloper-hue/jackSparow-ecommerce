<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Website Setup &amp; Storefront CMS</p>
                    <h1 class="h3 mb-1">Website Products Showcase</h1>
                    <p class="text-muted mb-0">Manage and curate computer hardware products from your inventory before they appear on the customer storefront website.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @can('edit website settings')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Product to Website
                    </button>
                @endcan
                <a href="{{ route('admin.storefront-menus.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-menu-button-wide me-1"></i> Website Menus
                </a>
                <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Preview Storefront
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the following validation errors:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Showcase Section Nav Tabs -->
        <div class="panel p-0 overflow-hidden mb-4 shadow-sm border mt-3">
            <ul class="nav nav-tabs nav-fill bg-light px-3 pt-3 border-bottom" role="tablist">
                @foreach($sections as $secKey => $secLabel)
                    @php
                        $icons = [
                            'featured' => 'bi-stars',
                            'deals' => 'bi-tag-fill',
                            'bestseller' => 'bi-trophy-fill',
                            'collections' => 'bi-collection-fill',
                            'promotions' => 'bi-megaphone-fill',
                        ];
                        $icon = $icons[$secKey] ?? 'bi-box-seam';
                    @endphp
                    <li class="nav-item" role="presentation">
                        <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 py-3 {{ $section === $secKey ? 'active text-primary' : 'text-secondary' }}"
                           href="{{ route('admin.website-products.index', ['section' => $secKey]) }}">
                            <i class="bi {{ $icon }} fs-5"></i>
                            <span>{{ $secLabel }}</span>
                            <span class="badge {{ $section === $secKey ? 'bg-primary' : 'bg-secondary' }} rounded-pill ms-1">
                                {{ $counts[$secKey] ?? 0 }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="p-4 bg-white">
                <!-- Section Information Banner -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 bg-light rounded-3 border mb-4">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi {{ $icons[$section] ?? 'bi-box-seam' }} text-primary"></i>
                            <span>{{ $sections[$section] }}</span>
                        </h5>
                        <p class="small text-muted mb-0">
                            @if($section === 'featured')
                                Curated hardware components displayed in the prominent <strong>Featured Components</strong> grid on the website homepage.
                            @elseif($section === 'deals')
                                Promotional components shown in the <strong>Flash Deals &amp; Clearance</strong> strip and highlighted discount areas.
                            @elseif($section === 'bestseller')
                                Top-selling rigs and hardware components highlighted for trust and high purchase volume.
                            @endif
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @can('edit website settings')
                            <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Product to Section
                            </button>
                        @endcan
                    </div>
                </div>

                <!-- Products Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">Order</th>
                                    <th>Product Details</th>
                                    <th>Category &amp; Brand</th>
                                    <th>Inventory Stock</th>
                                    <th>Price</th>
                                    <th>Showcase Badge</th>
                                    <th class="text-center" style="width: 120px;">Storefront Status</th>
                                    <th class="text-end" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($showcaseProducts as $item)
                                    @php
                                        $prod = $item->product;
                                    @endphp
                                    <tr>
                                        <!-- Sort Order -->
                                        <td class="text-center fw-bold text-muted font-monospace">
                                            #{{ $item->sort_order }}
                                        </td>

                                        <!-- Product Info & Photo Thumbnail -->
                                        <td>
                                            @if($prod)
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-3 bg-light border p-1 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0 shadow-xs" style="width: 48px; height: 48px;">
                                                        @if($prod->primary_image_url)
                                                            <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" class="w-100 h-100 object-fit-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'bi bi-cpu fs-4 text-primary\'></i>';">
                                                        @else
                                                            <i class="bi bi-cpu text-primary fs-4"></i>
                                                        @endif
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="fw-bold text-dark text-truncate" style="max-width: 320px;">
                                                            {{ $item->custom_title ?? $prod->name }}
                                                        </div>
                                                        @if($item->custom_title && $item->custom_title !== $prod->name)
                                                            <div class="small text-muted text-truncate" style="max-width: 320px; font-size: 11px;">
                                                                Catalog: {{ $prod->name }}
                                                            </div>
                                                        @endif
                                                        <div class="small font-monospace text-muted" style="font-size: 11px;">
                                                            SKU: <span class="text-primary">{{ $prod->sku }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-danger fw-bold">Missing Product #{{ $item->product_id }}</span>
                                            @endif
                                        </td>

                                        <!-- Category & Brand -->
                                        <td>
                                            @if($prod)
                                                <div><span class="badge bg-light text-dark border">{{ $prod->category?->name ?? 'None' }}</span></div>
                                                <div class="small text-muted mt-0.5">{{ $prod->brand?->name ?? 'Generic' }}</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Inventory Stock -->
                                        <td>
                                            @if($prod)
                                                @if($prod->stock_quantity <= 0)
                                                    <span class="badge bg-danger-subtle text-danger border border-danger">Out of Stock (0)</span>
                                                @elseif($prod->stock_quantity <= $prod->low_stock_threshold)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning">Low: {{ $prod->stock_quantity }}</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success">{{ $prod->stock_quantity }} Available</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Price & Discount -->
                                        <td>
                                            @if($prod)
                                                <div class="fw-bold text-dark">${{ number_format($prod->price, 2) }}</div>
                                                @if($prod->sale_price)
                                                    <div class="small text-success fw-bold">
                                                        Sale: ${{ number_format($prod->sale_price, 2) }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Showcase Badge -->
                                        <td>
                                            @if($item->badge)
                                                <span class="badge bg-primary text-white fw-bold px-2 py-1">
                                                    {{ $item->badge }}
                                                </span>
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>

                                        <!-- Live Toggle Switch -->
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('admin.website-products.toggle-active', $item->id) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm border-0 bg-transparent p-0" title="Click to toggle website display">
                                                    @if($item->is_active)
                                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1 rounded-pill" style="cursor: pointer;">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Live
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1 rounded-pill" style="cursor: pointer;">
                                                            <i class="bi bi-eye-slash-fill me-1"></i> Hidden
                                                        </span>
                                                    @endif
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-end">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                @if($prod)
                                                    <a href="{{ route('product.show', $prod->slug) }}" target="_blank" class="btn btn-sm btn-outline-info" title="Preview on Website">
                                                        <i class="bi bi-box-arrow-up-right"></i>
                                                    </a>
                                                @endif
                                                @can('edit website settings')
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editItemModal_{{ $item->id }}" title="Edit Showcase Details">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteItemModal_{{ $item->id }}" title="Remove from Website">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>

                                            @can('edit website settings')
                                                <!-- Edit Item Modal -->
                                                <div class="modal fade text-start" id="editItemModal_{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('admin.website-products.update', $item->id) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title fw-bold">
                                                                        <i class="bi bi-pencil-square text-primary me-2"></i> Edit Showcase Product
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <!-- Product Snapshot Preview -->
                                                                    <div class="d-flex align-items-center gap-3 p-2 bg-light rounded-3 border mb-3">
                                                                        <div class="rounded-2 bg-white border p-1 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 44px; height: 44px;">
                                                                            @if($prod && $prod->primary_image_url)
                                                                                <img src="{{ $prod->primary_image_url }}" alt="{{ $prod->name }}" class="w-100 h-100 object-fit-contain">
                                                                            @else
                                                                                <i class="bi bi-cpu text-primary fs-5"></i>
                                                                            @endif
                                                                        </div>
                                                                        <div class="min-w-0">
                                                                            <span class="fw-semibold text-dark text-truncate d-block small">{{ $prod?->name }}</span>
                                                                            <span class="text-muted small font-monospace" style="font-size: 11px;">SKU: {{ $prod?->sku }} &bull; ${{ number_format($prod?->price ?? 0, 2) }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row g-3">
                                                                        <div class="col-12">
                                                                            <label class="form-label small fw-bold">Custom Marketing Title</label>
                                                                            <input type="text" name="custom_title" class="form-control form-control-sm" value="{{ old('custom_title', $item->custom_title) }}" placeholder="{{ $prod?->name }}">
                                                                        </div>

                                                                        <div class="col-md-7">
                                                                            <label class="form-label small fw-bold">Showcase Tag / Badge</label>
                                                                            <input type="text" name="badge" class="form-control form-control-sm" value="{{ old('badge', $item->badge) }}" placeholder="e.g. HOT, NEW, SALE, SAVE $50">
                                                                        </div>

                                                                        <div class="col-md-5">
                                                                            <label class="form-label small fw-bold">Display Order</label>
                                                                            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $item->sort_order) }}" min="0">
                                                                        </div>

                                                                        <div class="col-12">
                                                                            <div class="form-check form-switch">
                                                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive_{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}>
                                                                                <label class="form-check-label small fw-bold" for="editIsActive_{{ $item->id }}">
                                                                                    Display on Storefront Website
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                                        <i class="bi bi-check-lg me-1"></i> Save Changes
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Delete Confirmation Modal -->
                                                <div class="modal fade text-start" id="deleteItemModal_{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                                        <div class="modal-content">
                                                            <form method="POST" action="{{ route('admin.website-products.destroy', $item->id) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title fw-bold text-danger">
                                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Remove Product
                                                                    </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body small">
                                                                    Are you sure you want to remove <strong>{{ $item->custom_title ?? $prod?->name }}</strong> from the <strong>{{ $sections[$section] }}</strong> showcase?
                                                                    <div class="mt-2 text-muted">This will only remove it from the website section; the product will remain safe in inventory.</div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                            <p class="fw-bold mb-1">No products configured for {{ $sections[$section] }} yet.</p>
                                            <p class="small text-muted mb-3">Add items from your inventory catalog to showcase them on the website.</p>
                                            @can('edit website settings')
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                                    <i class="bi bi-plus-circle me-1"></i> Add First Product
                                                </button>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
    </div>

    <!-- Add Product to Showcase Modal -->
    @can('edit website settings')
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.website-products.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="addProductModalLabel">
                                <i class="bi bi-plus-circle-fill text-primary me-2"></i> Add Product to Website Showcase
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-3 p-lg-4">
                            <div class="row g-4">
                                <!-- LEFT BLOCK: Select, Search & Showcase Configuration -->
                                <div class="col-lg-6">
                                    <div class="border rounded-3 p-3 h-100 d-flex flex-column bg-light-subtle shadow-xs left-showcase-block">
                                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                            <span class="fw-bold small text-uppercase tracking-wider">
                                                <i class="bi bi-search text-primary me-1.5"></i> Select &amp; Configure Showcase
                                            </span>
                                            <span class="badge bg-primary-subtle text-primary small">Left Block: Select &amp; Set</span>
                                        </div>

                                        <div class="vstack gap-3 flex-grow-1">
                                            <!-- Hardware Search & Selection Combobox -->
                                            <div>
                                                <label class="form-label small fw-bold">Search or Select Hardware Product <span class="text-danger">*</span></label>
                                                <div class="product-picker-wrapper position-relative">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                                                        <input type="text" 
                                                               id="hardwareSearchInput" 
                                                               class="form-control form-control-sm" 
                                                               placeholder="Search by name, SKU, brand, or click to browse..." 
                                                               autocomplete="off">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="hardwareDropdownBtn" tabindex="-1" title="Toggle hardware catalog list">
                                                            <i class="bi bi-chevron-down"></i>
                                                        </button>
                                                    </div>

                                                    <!-- Floating Dropdown Menu for Search Results -->
                                                    <div id="hardwareDropdownMenu" 
                                                         class="shadow-lg rounded-2 border position-absolute w-100 mt-1 d-none bg-white" 
                                                         style="max-height: 250px; overflow-y: auto; overflow-x: hidden; z-index: 1060;">
                                                        <!-- Populated dynamically via JS -->
                                                    </div>
                                                </div>
                                                <div class="form-text small">Click or type to search and select inventory components to add to the right side list.</div>
                                            </div>

                                            <!-- Section Selector & Badge -->
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <label class="form-label small fw-bold">Showcase Section <span class="text-danger">*</span></label>
                                                    <select name="section" class="form-select form-select-sm" required>
                                                        @foreach($sections as $secKey => $secLabel)
                                                            @if($secKey !== 'collections' && $secKey !== 'promotions')
                                                                <option value="{{ $secKey }}" {{ $section === $secKey ? 'selected' : '' }}>
                                                                    {{ $secLabel }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-sm-6">
                                                    <label class="form-label small fw-bold">Showcase Tag / Badge (Optional)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="text" name="badge" id="showcaseBadgeInput" class="form-control" placeholder="e.g. HOT, NEW, SALE">
                                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Presets</button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="document.getElementById('showcaseBadgeInput').value = 'HOT'">HOT</a></li>
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="document.getElementById('showcaseBadgeInput').value = 'NEW'">NEW</a></li>
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="document.getElementById('showcaseBadgeInput').value = 'SALE'">SALE</a></li>
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="document.getElementById('showcaseBadgeInput').value = 'BESTSELLER'">BESTSELLER</a></li>
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="document.getElementById('showcaseBadgeInput').value = 'TOP PICK'">TOP PICK</a></li>
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="document.getElementById('showcaseBadgeInput').value = 'EXCLUSIVE'">EXCLUSIVE</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Custom Marketing Title -->
                                            <div>
                                                <label class="form-label small fw-bold">Custom Marketing Title (Optional)</label>
                                                <input type="text" name="custom_title" class="form-control form-control-sm" placeholder="Leave blank to use default catalog name">
                                            </div>

                                            <!-- Sort Order & Switch -->
                                            <div class="row g-2 align-items-center pt-1">
                                                <div class="col-sm-5">
                                                    <label class="form-label small fw-bold mb-1">Display Order</label>
                                                    <input type="number" name="sort_order" class="form-control form-control-sm" value="0" min="0">
                                                </div>
                                                <div class="col-sm-7 pt-sm-3">
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                                                        <label class="form-check-label small fw-bold" for="createIsActive">
                                                            Immediately Display on Website
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT BLOCK: Selected Product List -->
                                <div class="col-lg-6">
                                    <div class="border rounded-3 p-3 h-100 d-flex flex-column bg-light-subtle shadow-xs right-showcase-block">
                                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold small text-uppercase tracking-wider">
                                                    <i class="bi bi-card-checklist text-success me-1.5"></i> Selected Hardware List
                                                </span>
                                                <span class="badge bg-secondary-subtle text-body-secondary font-monospace" id="selectedCountBadge" style="font-size: 11px;">0 selected</span>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 d-none" id="clearAllSelectedBtn" style="font-size: 11px;">
                                                <i class="bi bi-trash me-1"></i> Clear All
                                            </button>
                                        </div>

                                        <!-- The List Container -->
                                        <div id="selectedHardwareBox" 
                                             class="flex-grow-1 border rounded-2 p-2 bg-white d-flex flex-column justify-content-center" 
                                             style="min-height: 260px; max-height: 360px; overflow-y: auto; overflow-x: hidden;">
                                            
                                            <!-- Empty Placeholder -->
                                            <div id="selectedHardwareEmpty" class="d-flex flex-column align-items-center justify-content-center text-center py-5 text-muted my-auto">
                                                <div class="rounded-circle bg-light p-3 mb-2 border">
                                                    <i class="bi bi-box-arrow-in-right fs-2 text-secondary opacity-50"></i>
                                                </div>
                                                <span class="fw-semibold d-block">No hardware selected yet</span>
                                                <span class="text-muted small mt-1" style="max-width: 260px;">Search and select components on the left block to add them here.</span>
                                            </div>

                                            <!-- Selected Hardware List -->
                                            <div id="selectedHardwareList" class="vstack gap-2 d-none" style="width: 100%; overflow-x: hidden;">
                                                <!-- Populated dynamically via JS -->
                                            </div>
                                        </div>

                                        <!-- Hidden Inputs for Form Submission -->
                                        <div id="selectedHardwareHiddenInputs"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check-lg me-1"></i> Add to Website Showcase
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endcan

    <style>
        .product-dropdown-item {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .product-dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.08);
        }
        .product-dropdown-item.is-selected {
            background-color: rgba(25, 135, 84, 0.08);
        }
        .selected-hardware-item {
            transition: all 0.15s ease;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
        }
        .selected-hardware-item:hover {
            border-color: #0d6efd !important;
        }
        .selected-hardware-title {
            color: #1e293b;
            font-weight: 600;
        }
        .selected-hardware-sku {
            color: #64748b;
        }

        /* Dark mode theme overrides */
        html[data-theme="dark"] #hardwareDropdownMenu,
        html[data-bs-theme="dark"] #hardwareDropdownMenu,
        [data-theme="dark"] #hardwareDropdownMenu,
        [data-bs-theme="dark"] #hardwareDropdownMenu {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] #selectedHardwareBox,
        html[data-bs-theme="dark"] #selectedHardwareBox,
        [data-theme="dark"] #selectedHardwareBox,
        [data-bs-theme="dark"] #selectedHardwareBox {
            background-color: #0b1329 !important;
            border-color: #27354f !important;
        }
        html[data-theme="dark"] .left-showcase-block,
        html[data-bs-theme="dark"] .left-showcase-block,
        [data-theme="dark"] .left-showcase-block,
        [data-bs-theme="dark"] .left-showcase-block,
        html[data-theme="dark"] .right-showcase-block,
        html[data-bs-theme="dark"] .right-showcase-block,
        [data-theme="dark"] .right-showcase-block,
        [data-bs-theme="dark"] .right-showcase-block {
            background-color: #111c30 !important;
            border-color: #27354f !important;
        }
        html[data-theme="dark"] .selected-hardware-item,
        html[data-bs-theme="dark"] .selected-hardware-item,
        [data-theme="dark"] .selected-hardware-item,
        [data-bs-theme="dark"] .selected-hardware-item {
            background-color: #131f37 !important;
            border-color: #27354f !important;
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .selected-hardware-title,
        html[data-bs-theme="dark"] .selected-hardware-title,
        [data-theme="dark"] .selected-hardware-title,
        [data-bs-theme="dark"] .selected-hardware-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .selected-hardware-sku,
        html[data-bs-theme="dark"] .selected-hardware-sku,
        [data-theme="dark"] .selected-hardware-sku,
        [data-bs-theme="dark"] .selected-hardware-sku {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .product-dropdown-item:hover,
        html[data-bs-theme="dark"] .product-dropdown-item:hover,
        [data-theme="dark"] .product-dropdown-item:hover,
        [data-bs-theme="dark"] .product-dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        html[data-theme="dark"] .product-dropdown-item.is-selected,
        html[data-bs-theme="dark"] .product-dropdown-item.is-selected,
        [data-theme="dark"] .product-dropdown-item.is-selected,
        [data-bs-theme="dark"] .product-dropdown-item.is-selected {
            background-color: rgba(25, 135, 84, 0.2) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const catalogHardwareData = @json($catalogHardwareData ?? []);

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            }

            const searchInput = document.getElementById('hardwareSearchInput');
            const dropdownBtn = document.getElementById('hardwareDropdownBtn');
            const dropdownMenu = document.getElementById('hardwareDropdownMenu');
            const selectedBox = document.getElementById('selectedHardwareBox');
            const selectedEmpty = document.getElementById('selectedHardwareEmpty');
            const selectedList = document.getElementById('selectedHardwareList');
            const selectedCountBadge = document.getElementById('selectedCountBadge');
            const clearAllBtn = document.getElementById('clearAllSelectedBtn');
            const hiddenInputsContainer = document.getElementById('selectedHardwareHiddenInputs');
            const customTitleInput = document.querySelector('#addProductModal input[name="custom_title"]');

            if (!searchInput || !dropdownMenu) return;

            let selectedProducts = [];

            function renderDropdown(query = '') {
                const q = (query || '').toLowerCase().trim();
                const filtered = catalogHardwareData.filter(p => {
                    if (!q) return true;
                    const fullText = `${p.name} ${p.sku} ${p.category || ''} ${p.brand || ''}`.toLowerCase();
                    return fullText.includes(q);
                });

                dropdownMenu.innerHTML = '';

                if (filtered.length === 0) {
                    dropdownMenu.innerHTML = `
                        <div class="p-3 text-center text-muted small">
                            <i class="bi bi-search text-secondary d-block mb-1"></i>
                            No hardware products match "<strong>${escapeHtml(query)}</strong>"
                        </div>
                    `;
                    return;
                }

                filtered.forEach(p => {
                    const isSelected = selectedProducts.some(sp => sp.id === p.id);
                    const item = document.createElement('div');
                    item.className = `product-dropdown-item d-flex align-items-center justify-content-between p-2 border-bottom ${isSelected ? 'is-selected bg-success-subtle' : ''}`;
                    item.style.cursor = 'pointer';

                    const imgHtml = p.primary_image_url
                        ? `<img src="${escapeHtml(p.primary_image_url)}" alt="${escapeHtml(p.name)}" class="w-100 h-100 object-fit-contain">`
                        : `<i class="bi bi-cpu text-primary fs-5"></i>`;

                    const badgeHtml = isSelected
                        ? `<span class="badge bg-success text-white small font-monospace"><i class="bi bi-check-lg me-1"></i>Selected</span>`
                        : `<span class="badge bg-light text-muted border small font-monospace"><i class="bi bi-plus me-1"></i>Select</span>`;

                    item.innerHTML = `
                        <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1 pe-2" style="min-width: 0; overflow: hidden;">
                            <div class="rounded-2 bg-white border p-1 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 36px; height: 36px;">
                                ${imgHtml}
                            </div>
                            <div class="min-w-0 flex-grow-1" style="min-width: 0; overflow: hidden;">
                                <span class="fw-semibold text-truncate d-block small selected-hardware-title" style="font-size: 12px;" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</span>
                                <div class="d-flex flex-wrap align-items-center gap-1 font-monospace" style="font-size: 10.5px;">
                                    <span class="selected-hardware-sku text-truncate" style="max-width: 120px;">${escapeHtml(p.sku)}</span>
                                    <span class="text-secondary opacity-50">&bull;</span>
                                    <span class="text-primary fw-bold">$${escapeHtml(p.formatted_price)}</span>
                                    <span class="text-secondary opacity-50">&bull;</span>
                                    <span class="${p.stock_quantity > 0 ? 'text-success' : 'text-danger'}">
                                        ${p.stock_quantity} in stock
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 ms-1">
                            ${badgeHtml}
                        </div>
                    `;

                    item.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        toggleProduct(p);
                    });

                    dropdownMenu.appendChild(item);
                });
            }

            function toggleProduct(p) {
                const index = selectedProducts.findIndex(sp => sp.id === p.id);
                if (index > -1) {
                    selectedProducts.splice(index, 1);
                } else {
                    selectedProducts.push(p);
                }
                updateSelectedUI();
                renderDropdown(searchInput.value);
            }

            window.removeSelectedHardware = function (productId) {
                selectedProducts = selectedProducts.filter(p => p.id !== productId);
                updateSelectedUI();
                renderDropdown(searchInput.value);
            };

            function updateSelectedUI() {
                if (selectedProducts.length === 0) {
                    selectedEmpty.classList.remove('d-none');
                    selectedList.classList.add('d-none');
                    selectedList.innerHTML = '';
                    if (selectedBox) selectedBox.classList.add('justify-content-center');
                    clearAllBtn.classList.add('d-none');
                    selectedCountBadge.textContent = '0 selected';
                    selectedCountBadge.className = 'badge bg-secondary-subtle text-body-secondary font-monospace';
                    hiddenInputsContainer.innerHTML = '';
                    if (customTitleInput) customTitleInput.placeholder = 'Leave blank to use default catalog name';
                } else {
                    selectedEmpty.classList.add('d-none');
                    selectedList.classList.remove('d-none');
                    selectedList.innerHTML = '';
                    if (selectedBox) selectedBox.classList.remove('justify-content-center');
                    clearAllBtn.classList.remove('d-none');
                    selectedCountBadge.textContent = `${selectedProducts.length} selected`;
                    selectedCountBadge.className = 'badge bg-success-subtle text-success border border-success-subtle font-monospace';

                    let hiddenHtml = `<input type="hidden" name="product_id" value="${selectedProducts[0].id}">`;

                    selectedProducts.forEach(p => {
                        hiddenHtml += `<input type="hidden" name="product_ids[]" value="${p.id}">`;

                        const imgHtml = p.primary_image_url
                            ? `<img src="${escapeHtml(p.primary_image_url)}" alt="${escapeHtml(p.name)}" class="w-100 h-100 object-fit-contain">`
                            : `<i class="bi bi-cpu text-primary fs-5"></i>`;

                        const itemEl = document.createElement('div');
                        itemEl.className = 'selected-hardware-item p-2 rounded-2 border shadow-xs';
                        itemEl.style.width = '100%';
                        itemEl.style.boxSizing = 'border-box';
                        itemEl.innerHTML = `
                            <div class="d-flex align-items-center gap-2" style="min-width: 0; width: 100%;">
                                <div class="rounded-2 bg-light border p-1 d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 38px; height: 38px;">
                                    ${imgHtml}
                                </div>
                                <div class="min-w-0 flex-grow-1 pe-1" style="min-width: 0; overflow: hidden;">
                                    <div class="selected-hardware-title text-truncate small lh-sm" title="${escapeHtml(p.name)}" style="font-size: 12px;">
                                        ${escapeHtml(p.name)}
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-0.5" style="font-size: 10.5px;">
                                        <span class="selected-hardware-sku font-monospace text-truncate" style="max-width: 120px;">${escapeHtml(p.sku)}</span>
                                        <span class="text-secondary opacity-50">&bull;</span>
                                        <span class="text-primary fw-bold font-monospace">$${escapeHtml(p.formatted_price)}</span>
                                        <span class="text-secondary opacity-50">&bull;</span>
                                        <span class="${p.stock_quantity > 0 ? 'text-success' : 'text-danger'} font-monospace">
                                            ${p.stock_quantity} in stock
                                        </span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm p-0 rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;" onclick="removeSelectedHardware(${p.id})" title="Remove item">
                                    <i class="bi bi-x-lg" style="font-size: 10px;"></i>
                                </button>
                            </div>
                        `;
                        selectedList.appendChild(itemEl);
                    });

                    hiddenInputsContainer.innerHTML = hiddenHtml;

                    if (customTitleInput) {
                        if (selectedProducts.length === 1) {
                            customTitleInput.placeholder = selectedProducts[0].name;
                        } else {
                            customTitleInput.placeholder = `${selectedProducts.length} hardware products selected`;
                        }
                    }
                }
            }

            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    selectedProducts = [];
                    updateSelectedUI();
                    renderDropdown(searchInput.value);
                });
            }

            searchInput.addEventListener('focus', function () {
                renderDropdown(this.value);
                dropdownMenu.classList.remove('d-none');
            });

            searchInput.addEventListener('click', function () {
                renderDropdown(this.value);
                dropdownMenu.classList.remove('d-none');
            });

            searchInput.addEventListener('input', function () {
                renderDropdown(this.value);
                dropdownMenu.classList.remove('d-none');
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const firstItem = dropdownMenu.querySelector('.product-dropdown-item');
                    if (firstItem) {
                        firstItem.dispatchEvent(new MouseEvent('mousedown'));
                        this.value = '';
                        renderDropdown('');
                    }
                } else if (e.key === 'Escape') {
                    dropdownMenu.classList.add('d-none');
                }
            });

            searchInput.addEventListener('blur', function () {
                setTimeout(() => {
                    dropdownMenu.classList.add('d-none');
                }, 250);
            });

            if (dropdownBtn) {
                dropdownBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (dropdownMenu.classList.contains('d-none')) {
                        searchInput.focus();
                        renderDropdown(searchInput.value);
                        dropdownMenu.classList.remove('d-none');
                    } else {
                        dropdownMenu.classList.add('d-none');
                    }
                });
            }

            const modalForm = document.querySelector('#addProductModal form');
            if (modalForm) {
                modalForm.addEventListener('submit', function (e) {
                    if (selectedProducts.length === 0) {
                        e.preventDefault();
                        searchInput.focus();
                        searchInput.classList.add('is-invalid');
                        setTimeout(() => searchInput.classList.remove('is-invalid'), 3000);
                        alert('Please select at least one hardware product from inventory.');
                    }
                });
            }
        });
    </script>
</x-app-layout>

