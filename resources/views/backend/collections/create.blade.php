<x-app-layout>
    <style>
        .collection-panel-title {
            color: var(--admin-text, #1f2937);
            font-weight: 700;
        }
        .product-search-dropdown {
            background-color: var(--admin-surface, #ffffff);
            border: 1px solid var(--admin-border, #dbe4ef);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }
        .product-search-item {
            background-color: var(--admin-surface, #ffffff);
            color: var(--admin-text, #1f2937);
            border-color: var(--admin-border, #dbe4ef);
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .product-search-item:hover,
        .product-search-item:focus {
            background-color: rgba(37, 99, 235, 0.08);
            color: var(--admin-text, #1f2937);
        }
        .product-search-item.is-added {
            background-color: var(--admin-surface-soft, #f8fafc);
            opacity: 0.75;
        }
        .product-search-item-title {
            color: var(--admin-text, #1f2937);
        }
        .product-search-item-sku {
            color: var(--admin-muted, #6b7280);
        }
        .selected-product-card {
            background-color: var(--admin-surface, #ffffff);
            border: 1px solid var(--admin-border, #dbe4ef);
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }
        .selected-product-card:hover {
            border-color: var(--admin-primary, #2563eb);
        }
        .selected-product-title {
            color: var(--admin-text, #1f2937);
        }
        .selected-product-sku {
            color: var(--admin-muted, #6b7280);
        }
        .banner-preview-box,
        .empty-selection-box {
            background-color: var(--admin-surface-soft, #f8fafc);
            border: 1px dashed var(--admin-border, #dbe4ef);
        }
        .btn-remove-item {
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.4);
            background-color: transparent;
            transition: all 0.15s ease;
        }
        .btn-remove-item:hover,
        .btn-remove-item:focus {
            color: #ffffff;
            background-color: #ef4444;
            border-color: #ef4444;
        }

        /* Dark Mode Theme Support */
        html[data-theme="dark"] .collection-panel-title,
        html[data-bs-theme="dark"] .collection-panel-title {
            color: #f1f5f9 !important;
        }
        html[data-theme="dark"] .product-search-dropdown,
        html[data-bs-theme="dark"] .product-search-dropdown {
            background-color: #182235 !important;
            border-color: #2f3b52 !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.7) !important;
        }
        html[data-theme="dark"] .product-search-item,
        html[data-bs-theme="dark"] .product-search-item {
            background-color: #182235 !important;
            color: #e5edf7 !important;
            border-color: #243149 !important;
        }
        html[data-theme="dark"] .product-search-item:hover,
        html[data-bs-theme="dark"] .product-search-item:hover,
        html[data-theme="dark"] .product-search-item:focus,
        html[data-bs-theme="dark"] .product-search-item:focus {
            background-color: #24324a !important;
            color: #ffffff !important;
        }
        html[data-theme="dark"] .product-search-item.is-added,
        html[data-bs-theme="dark"] .product-search-item.is-added {
            background-color: #111827 !important;
            opacity: 0.65;
        }
        html[data-theme="dark"] .product-search-item-title,
        html[data-bs-theme="dark"] .product-search-item-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .product-search-item-sku,
        html[data-bs-theme="dark"] .product-search-item-sku {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .selected-product-card,
        html[data-bs-theme="dark"] .selected-product-card {
            background-color: #141c2b !important;
            border-color: #2f3b52 !important;
        }
        html[data-theme="dark"] .selected-product-card:hover,
        html[data-bs-theme="dark"] .selected-product-card:hover {
            border-color: #3b82f6 !important;
        }
        html[data-theme="dark"] .selected-product-title,
        html[data-bs-theme="dark"] .selected-product-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .selected-product-sku,
        html[data-bs-theme="dark"] .selected-product-sku {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .banner-preview-box,
        html[data-bs-theme="dark"] .banner-preview-box,
        html[data-theme="dark"] .empty-selection-box,
        html[data-bs-theme="dark"] .empty-selection-box {
            background-color: #111827 !important;
            border-color: #2f3b52 !important;
        }
        html[data-theme="dark"] .btn-remove-item,
        html[data-bs-theme="dark"] .btn-remove-item {
            color: #f87171 !important;
            border-color: rgba(248, 113, 113, 0.35) !important;
            background-color: rgba(248, 113, 113, 0.08) !important;
        }
        html[data-theme="dark"] .btn-remove-item:hover,
        html[data-bs-theme="dark"] .btn-remove-item:hover {
            color: #ffffff !important;
            background-color: #dc2626 !important;
            border-color: #dc2626 !important;
        }
    </style>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-collection" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Catalog Organization</p>
                    <h1 class="h3 mb-1">New Product Collection</h1>
                    <p class="text-muted mb-0">Create a curated product bundle or themed collection.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Collections
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

        <form action="{{ route('admin.collections.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Left: Main Information & Product Selector -->
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 collection-panel-title">Collection Details</h5>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Collection Name *</label>
                            <input type="text" name="name" id="collectionName" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Extreme Gaming Builds, Workstation Essentials">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Slug (URL identifier)</label>
                            <input type="text" name="slug" id="collectionSlug" value="{{ old('slug') }}" class="form-control font-monospace" placeholder="e.g. extreme-gaming-builds (leave blank to auto-generate)">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Brief summary of this product collection...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Products Assignment: Search & Select Only -->
                    <div class="panel p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <div>
                                <h5 class="mb-0 collection-panel-title">Assign Hardware Components</h5>
                                <small class="text-muted">Search and select products to include in this collection.</small>
                            </div>
                            <span class="badge bg-primary font-monospace" id="selectedCountBadge">
                                0 products selected
                            </span>
                        </div>

                        <!-- Search Bar with Live Dropdown Picker -->
                        <div class="position-relative mb-3">
                            <label class="form-label fw-bold small text-secondary mb-1">Search &amp; Add Products</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="productSearchInput" class="form-control" placeholder="Type component name or SKU (e.g. RTX, Ryzen, Corsair)..." autocomplete="off">
                                <button type="button" class="btn btn-outline-secondary" id="clearSearchInputBtn" style="display: none;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <!-- Dropdown Results -->
                            <div id="searchResultsDropdown" class="list-group product-search-dropdown position-absolute w-100 shadow-lg border rounded-bottom overflow-auto mt-1" style="display: none; max-height: 280px; z-index: 1050;">
                            </div>
                        </div>

                        <!-- Selected Products List (ONLY displays selected products) -->
                        <div class="mt-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold small text-uppercase text-muted">Selected Components in this Collection</span>
                                <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0" id="removeAllProductsBtn" style="display: none;">
                                    <i class="bi bi-trash3"></i> Remove All
                                </button>
                            </div>

                            <div id="selectedProductsContainer" class="d-flex flex-column gap-2">
                                <!-- Populated dynamically by JS -->
                            </div>

                            <!-- Empty state when no products are selected -->
                            <div id="emptySelectionState" class="text-center py-4 border border-dashed rounded empty-selection-box">
                                <i class="bi bi-cpu text-muted fs-2 d-block mb-1 opacity-50"></i>
                                <div class="text-muted fw-semibold small">No products selected yet</div>
                                <small class="text-muted">Type in the search box above and click a product to add it to this collection.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Media & Visibility Controls -->
                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 collection-panel-title">Publishing &amp; Status</h5>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="isActive">Active / Visible</label>
                            <div class="form-text text-muted small">Disable to hide this collection from the store.</div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeatured" {{ old('is_featured') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="isFeatured">Featured Collection</label>
                            <div class="form-text text-muted small">Promote on the homepage or top navigation.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Sort Order</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-control" placeholder="0">
                        </div>
                    </div>

                    <!-- Banner Image Management & Live Preview -->
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 collection-panel-title">Collection Banner Image</h5>

                        <div class="mb-3 text-center">
                            <div id="bannerPreviewWrapper" class="position-relative rounded border banner-preview-box d-flex align-items-center justify-content-center overflow-hidden shadow-sm mb-2" style="min-height: 140px; max-height: 180px;">
                                <img id="bannerPreviewImg"
                                     src="{{ old('image') }}"
                                     alt="Banner Preview"
                                     class="w-100 h-100 object-fit-cover {{ empty(old('image')) ? 'd-none' : '' }}">
                                <div id="bannerPlaceholder" class="text-muted p-3 text-center {{ !empty(old('image')) ? 'd-none' : '' }}">
                                    <i class="bi bi-image fs-1 d-block mb-1 text-secondary opacity-50"></i>
                                    <small class="d-block">No banner image selected</small>
                                </div>
                            </div>

                            <div id="bannerActions" class="d-flex align-items-center justify-content-center gap-2 {{ empty(old('image')) ? 'd-none' : '' }}">
                                <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" id="removeBannerBtn">
                                    <i class="bi bi-trash3 me-1"></i> Clear Banner
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Upload Image File</label>
                            <input type="file" name="image_file" id="imageFileInput" class="form-control form-control-sm" accept="image/png,image/jpeg,image/webp,image/gif">
                            <div class="form-text small">Recommended size: 1200x400px (JPG, PNG, WebP)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Or External Image URL</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="image" id="imageUrlInput" value="{{ old('image') }}" class="form-control" placeholder="https://...">
                                <button class="btn btn-outline-secondary" type="button" id="previewUrlBtn">Apply</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Create Collection
                    </button>
                </div>
            </div>
        </form>
    </div>

    @php
        $oldProductIds = old('product_ids');
        $initialSelected = is_array($oldProductIds) ? $products->whereIn('id', $oldProductIds)->values() : collect([]);
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Slug auto generation
            const nameInput = document.getElementById('collectionName');
            const slugInput = document.getElementById('collectionSlug');

            if (nameInput && slugInput) {
                nameInput.addEventListener('input', function () {
                    if (!slugInput.dataset.manual) {
                        slugInput.value = nameInput.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    }
                });
                slugInput.addEventListener('input', function () {
                    slugInput.dataset.manual = 'true';
                });
            }

            // Products data
            const allProducts = @json($products);
            const initialSelected = @json($initialSelected);

            // Selected Products State Map: id => product object
            const selectedMap = new Map();
            initialSelected.forEach(function (p) {
                selectedMap.set(Number(p.id), p);
            });

            // DOM Elements - Products
            const searchInput = document.getElementById('productSearchInput');
            const clearSearchBtn = document.getElementById('clearSearchInputBtn');
            const dropdown = document.getElementById('searchResultsDropdown');
            const container = document.getElementById('selectedProductsContainer');
            const emptyState = document.getElementById('emptySelectionState');
            const badge = document.getElementById('selectedCountBadge');
            const removeAllBtn = document.getElementById('removeAllProductsBtn');

            // Render selected products
            function renderSelected() {
                container.innerHTML = '';
                const size = selectedMap.size;

                if (size === 0) {
                    emptyState.style.display = 'block';
                    removeAllBtn.style.display = 'none';
                    badge.textContent = '0 products selected';
                } else {
                    emptyState.style.display = 'none';
                    removeAllBtn.style.display = 'inline-block';
                    badge.textContent = size + ' product' + (size === 1 ? '' : 's') + ' selected';

                    selectedMap.forEach(function (product, id) {
                        const row = document.createElement('div');
                        row.className = 'card border p-2 shadow-sm d-flex flex-row align-items-center justify-content-between selected-product-card';
                        row.dataset.id = id;

                        const priceFormatted = product.price ? '$' + Number(product.price).toFixed(2) : '';

                        row.innerHTML = `
                            <input type="hidden" name="product_ids[]" value="${id}">
                            <div class="d-flex align-items-center gap-2 overflow-hidden flex-grow-1">
                                <span class="badge bg-primary-subtle text-primary p-2 rounded">
                                    <i class="bi bi-cpu"></i>
                                </span>
                                <div class="text-truncate">
                                    <div class="fw-bold selected-product-title small text-truncate">${escapeHtml(product.name)}</div>
                                    <div class="selected-product-sku small font-monospace" style="font-size: 11px;">
                                        SKU: <span class="text-primary">${escapeHtml(product.sku || 'N/A')}</span>
                                        ${priceFormatted ? ` &bull; <span class="text-success fw-semibold">${priceFormatted}</span>` : ''}
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-remove-item btn-sm px-2 py-1 ms-2 remove-btn" title="Remove component from collection">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        `;

                        row.querySelector('.remove-btn').addEventListener('click', function () {
                            selectedMap.delete(id);
                            renderSelected();
                        });

                        container.appendChild(row);
                    });
                }
            }

            // Escape HTML for safety
            function escapeHtml(text) {
                if (!text) return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            // Search filtering
            function filterProducts(query) {
                const term = query.toLowerCase().trim();
                dropdown.innerHTML = '';

                if (!term) {
                    dropdown.style.display = 'none';
                    clearSearchBtn.style.display = 'none';
                    return;
                }

                clearSearchBtn.style.display = 'inline-block';

                const matches = allProducts.filter(function (p) {
                    const name = (p.name || '').toLowerCase();
                    const sku = (p.sku || '').toLowerCase();
                    return name.includes(term) || sku.includes(term);
                }).slice(0, 20);

                if (matches.length === 0) {
                    dropdown.innerHTML = `
                        <div class="list-group-item product-search-item text-muted text-center py-3 small">
                            <i class="bi bi-exclamation-circle me-1"></i> No matching hardware components found for "<strong>${escapeHtml(term)}</strong>"
                        </div>
                    `;
                } else {
                    matches.forEach(function (product) {
                        const isAdded = selectedMap.has(Number(product.id));
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action product-search-item d-flex align-items-center justify-content-between p-2 ' + (isAdded ? 'is-added' : '');

                        const priceFormatted = product.price ? '$' + Number(product.price).toFixed(2) : '';

                        item.innerHTML = `
                            <div class="text-truncate me-2">
                                <div class="fw-semibold small product-search-item-title text-truncate">${escapeHtml(product.name)}</div>
                                <small class="product-search-item-sku font-monospace" style="font-size: 11px;">SKU: ${escapeHtml(product.sku || 'N/A')} ${priceFormatted ? `&bull; ${priceFormatted}` : ''}</small>
                            </div>
                            <div>
                                ${isAdded
                                    ? '<span class="badge bg-secondary-subtle text-secondary small"><i class="bi bi-check2"></i> Added</span>'
                                    : '<span class="badge bg-primary text-white small"><i class="bi bi-plus-lg"></i> Add</span>'
                                }
                            </div>
                        `;

                        item.addEventListener('click', function (e) {
                            e.preventDefault();
                            if (!isAdded) {
                                selectedMap.set(Number(product.id), product);
                                renderSelected();
                                searchInput.value = '';
                                dropdown.style.display = 'none';
                                clearSearchBtn.style.display = 'none';
                                searchInput.focus();
                            }
                        });

                        dropdown.appendChild(item);
                    });
                }

                dropdown.style.display = 'block';
            }

            // Product search events
            searchInput.addEventListener('input', function () {
                filterProducts(this.value);
            });

            searchInput.addEventListener('focus', function () {
                if (this.value.trim().length > 0) {
                    filterProducts(this.value);
                }
            });

            clearSearchBtn.addEventListener('click', function () {
                searchInput.value = '';
                dropdown.style.display = 'none';
                clearSearchBtn.style.display = 'none';
                searchInput.focus();
            });

            removeAllBtn.addEventListener('click', function () {
                if (confirm('Remove all components from this collection?')) {
                    selectedMap.clear();
                    renderSelected();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target) && !clearSearchBtn.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            // Initial render of selected products
            renderSelected();

            // -------------------------------------------------------------
            // Banner Preview & Image Logic
            // -------------------------------------------------------------
            const imageFileInput = document.getElementById('imageFileInput');
            const imageUrlInput = document.getElementById('imageUrlInput');
            const previewUrlBtn = document.getElementById('previewUrlBtn');
            const bannerPreviewImg = document.getElementById('bannerPreviewImg');
            const bannerPlaceholder = document.getElementById('bannerPlaceholder');
            const bannerActions = document.getElementById('bannerActions');
            const removeBannerBtn = document.getElementById('removeBannerBtn');

            function showBanner(src) {
                if (src) {
                    bannerPreviewImg.src = src;
                    bannerPreviewImg.classList.remove('d-none');
                    bannerPlaceholder.classList.add('d-none');
                    bannerActions.classList.remove('d-none');
                } else {
                    hideBanner();
                }
            }

            function hideBanner() {
                bannerPreviewImg.src = '';
                bannerPreviewImg.classList.add('d-none');
                bannerPlaceholder.classList.remove('d-none');
                bannerActions.classList.add('d-none');
            }

            // Live preview when file is selected
            if (imageFileInput) {
                imageFileInput.addEventListener('change', function (event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            showBanner(e.target.result);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Live preview when URL is provided
            if (previewUrlBtn && imageUrlInput) {
                previewUrlBtn.addEventListener('click', function () {
                    const url = imageUrlInput.value.trim();
                    if (url) {
                        showBanner(url);
                    }
                });

                imageUrlInput.addEventListener('input', function () {
                    const url = imageUrlInput.value.trim();
                    if (url.startsWith('http://') || url.startsWith('https://')) {
                        showBanner(url);
                    }
                });
            }

            // Remove banner action
            if (removeBannerBtn) {
                removeBannerBtn.addEventListener('click', function () {
                    hideBanner();
                    if (imageFileInput) imageFileInput.value = '';
                    if (imageUrlInput) imageUrlInput.value = '';
                });
            }
        });
    </script>
</x-app-layout>
