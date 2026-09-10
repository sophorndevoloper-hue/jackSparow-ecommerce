<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cpu" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">B2B Hardware Management</p>
                    <h1 class="h3 mb-1">Hardware Products &amp; Components</h1>
                    <p class="text-muted mb-0">Manage computer components, technical specifications, B2B price tiers, and serialized inventory.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.serial-numbers.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-upc-scan me-1"></i> Serial Registry
                </a>
                <a href="{{ route('admin.customer-groups.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bi bi-people me-1"></i> Customer Groups
                </a>
                @canany(['create products', 'edit products'])
                    <a href="{{ route('admin.products.images.index') }}" class="btn btn-outline-primary btn-sm me-2">
                        <i class="bi bi-images me-1"></i> Photos
                    </a>
                @endcanany
                @can('create products')
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Computer Part
                    </a>
                @endcan
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
        @endif

        <!-- Filter & Search Bar -->
        <div class="panel p-3 mb-3 mt-3">
            <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, SKU, MPN...">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="brand_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="make_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Makes</option>
                        @foreach($makes as $m)
                            <option value="{{ $m->id }}" {{ request('make_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="stock" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Stock</option>
                        <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock Alerts</option>
                        <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>

                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill" title="Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'brand_id', 'make_id', 'stock']))
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="panel p-3">
            <x-datatable id="productsTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="productsTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="product-thumb-preview position-relative rounded-3 bg-body-secondary border overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 44px; height: 44px; cursor: pointer; transition: transform 0.15s ease;" 
                                             title="Click to view all images ({{ $product->images->count() }})"
                                             onclick="openProductGallery({{ $product->id }})"
                                             onmouseover="this.style.transform='scale(1.06)'"
                                             onmouseout="this.style.transform='scale(1)'">
                                            @if($product->primary_image_url)
                                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-cpu fs-5 text-primary"></i>
                                            @endif
                                            @if($product->images->count() > 1)
                                                <span class="position-absolute bottom-0 end-0 bg-dark text-white rounded-start px-1 font-monospace" style="font-size: 8px; opacity: 0.85;">
                                                    {{ $product->images->count() }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            @can('edit products')
                                                <a href="{{ route('admin.products.edit', $product->id) }}" class="fw-semibold text-body-emphasis text-decoration-none d-block text-truncate" style="max-width: 320px;" title="{{ $product->name }}">
                                                    {{ $product->name }}
                                                </a>
                                            @else
                                                <span class="fw-semibold text-body-emphasis d-block text-truncate" style="max-width: 320px;">{{ $product->name }}</span>
                                            @endcan
                                            <div class="text-muted small font-monospace mt-1 d-flex align-items-center gap-2" style="font-size: 11px;">
                                                <span>SKU: <strong class="text-body">{{ $product->sku }}</strong></span>
                                                @if($product->mpn)
                                                    <span class="text-body-tertiary">&bull;</span>
                                                    <span class="text-secondary" title="MPN: {{ $product->mpn }}">MPN: {{ Str::limit($product->mpn, 16) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-body-emphasis">{{ $product->category->name }}</div>
                                    <div class="text-muted small d-flex align-items-center gap-1 mt-1 flex-wrap" style="font-size: 11px;">
                                        @if($product->brand)
                                            <span class="text-body-secondary">{{ $product->brand->name }}</span>
                                        @endif
                                        @if($product->make)
                                            <span class="badge bg-body-secondary text-body-secondary border-0 px-1 py-0">Make: {{ $product->make->name }}</span>
                                        @endif
                                        @if($product->socket)
                                            <span class="badge bg-body-secondary text-body-secondary border-0 px-1 py-0 font-monospace">{{ $product->socket }}</span>
                                        @endif
                                        @if($product->tdp_watts)
                                            <span class="badge bg-body-secondary text-body-secondary border-0 px-1 py-0 font-monospace">{{ $product->tdp_watts }}W</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-baseline gap-2">
                                        @if($product->sale_price && $product->sale_price < $product->price)
                                            <span class="fw-bold text-body-emphasis">${{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-muted text-decoration-line-through small" style="font-size: 11px;">${{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="fw-bold text-body-emphasis">${{ number_format($product->price, 2) }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 11px;">
                                        Cost: ${{ number_format($product->cost_price ?: 0, 2) }}
                                        @if($product->cost_price && $product->getFloorPrice() > 0)
                                            <span class="text-body-tertiary">&bull;</span> Floor: ${{ number_format($product->getFloorPrice(), 2) }}
                                        @endif
                                    </div>
                                    @if($product->priceTiers->isNotEmpty())
                                        <div class="text-success small mt-1 d-flex align-items-center gap-1" style="font-size: 11px;">
                                            <i class="bi bi-layers-half"></i> {{ $product->priceTiers->count() }} Tiers Configured
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium text-body">MOQ: {{ $product->moq ?? 1 }}</div>
                                    @if(($product->case_pack_multiple ?? 1) > 1)
                                        <div class="text-muted small mt-1" style="font-size: 11px;">Pack: &times;{{ $product->case_pack_multiple }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($product->stock_quantity <= 0)
                                        <span class="badge bg-danger-subtle text-danger border-0 px-2 py-1">
                                            <i class="bi bi-x-circle me-1"></i>0 Out
                                        </span>
                                    @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                        <span class="badge bg-warning-subtle text-warning border-0 px-2 py-1">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $product->stock_quantity }} Low
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i>{{ $product->stock_quantity }} units
                                        </span>
                                    @endif

                                    @if($product->requires_serial_tracking)
                                        <div class="text-info small mt-1 d-flex align-items-center gap-1" style="font-size: 11px;">
                                            <i class="bi bi-upc-scan"></i> Serial Tracked
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Draft</span>
                                    @endif
                                    @if($product->is_featured)
                                        <div class="mt-1">
                                            <span class="badge bg-primary-subtle text-primary border-0 px-1 py-0" style="font-size: 10px;">
                                                <i class="bi bi-star-fill me-1"></i>Featured
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <button type="button" class="btn-ghost" title="Quick View Images" onclick="openProductGallery({{ $product->id }})">
                                            <i class="bi bi-images"></i>
                                        </button>
                                        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="btn-ghost" title="View on Storefront">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @can('edit products')
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn-ghost text-primary" title="Edit Component">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete products')
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this computer part?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    No computer parts found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Product Image Gallery Modal -->
    <div class="modal fade" id="productGalleryModal" tabindex="-1" aria-labelledby="productGalleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="galleryModalTitle">
                            <i class="bi bi-images text-primary me-2"></i> <span id="galleryProductName">Product Gallery</span>
                        </h5>
                        <div class="mt-1">
                            <span class="badge bg-secondary font-monospace" id="galleryProductSku">SKU: -</span>
                            <span class="badge bg-light-subtle text-body border ms-1" id="galleryProductCategory">Category</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 text-center">
                    <!-- Main Image Viewer Container -->
                    <div id="galleryViewerWrapper" class="position-relative bg-dark rounded-3 overflow-hidden d-flex align-items-center justify-content-center shadow-inner mb-3" style="min-height: 380px; max-height: 460px;">
                        <img id="galleryMainImage" src="" alt="Product Large View" class="w-100 h-100 object-fit-contain" style="max-height: 440px; transition: opacity 0.2s ease;">
                        
                        <!-- Prev / Next Navigation Arrows -->
                        <button type="button" id="galleryPrevBtn" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 start-0 translate-middle-y ms-2 opacity-75 shadow" style="width: 40px; height: 40px;" onclick="navigateGallery(-1)" aria-label="Previous image">
                            <i class="bi bi-chevron-left fs-5"></i>
                        </button>
                        <button type="button" id="galleryNextBtn" class="btn btn-dark btn-sm rounded-circle position-absolute top-50 end-0 translate-middle-y me-2 opacity-75 shadow" style="width: 40px; height: 40px;" onclick="navigateGallery(1)" aria-label="Next image">
                            <i class="bi bi-chevron-right fs-5"></i>
                        </button>

                        <!-- Badges overlay -->
                        <div class="position-absolute top-0 start-0 m-3 d-flex gap-2">
                            <span id="galleryCoverBadge" class="badge bg-success shadow-sm" style="display: none;">
                                <i class="bi bi-star-fill me-1"></i> Primary Cover
                            </span>
                        </div>

                        <div class="position-absolute bottom-0 end-0 m-3">
                            <span id="galleryCounterBadge" class="badge bg-dark bg-opacity-75 shadow-sm font-monospace">
                                1 / 1
                            </span>
                        </div>
                    </div>

                    <!-- Thumbnails Strip -->
                    <div id="galleryThumbnailsStrip" class="d-flex align-items-center justify-content-center gap-2 flex-wrap py-2 border rounded"></div>

                    <!-- Empty State -->
                    <div id="galleryEmptyState" class="py-5 text-center" style="display: none;">
                        <i class="bi bi-image text-muted display-3 d-block mb-3"></i>
                        <h5 class="fw-bold mb-1">No Images Uploaded</h5>
                        <p class="text-muted small mb-3">This product currently does not have any product images attached.</p>
                        @canany(['create products', 'edit products'])
                            <a id="galleryUploadBtn" href="#" class="btn btn-primary btn-sm">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Upload Images for this Product
                            </a>
                        @endcanany
                    </div>
                </div>

                <div class="modal-footer border-top d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small">Price: <strong id="galleryProductPrice" class="text-body">$0.00</strong></span>
                        <span class="text-muted small ms-3">Stock: <strong id="galleryProductStock" class="text-body">0 units</strong></span>
                    </div>
                    <div class="d-flex gap-2">
                        @canany(['create products', 'edit products'])
                            <a id="galleryManageImagesLink" href="#" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-gear me-1"></i> Manage Media
                            </a>
                        @endcanany
                        @can('edit products')
                            <a id="galleryEditProductLink" href="#" class="btn btn-primary btn-sm">
                                <i class="bi bi-pencil me-1"></i> Edit Product
                            </a>
                        @endcan
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $galleryProductsData = $products->mapWithKeys(function ($p) {
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'category' => $p->category->name ?? '',
                'brand' => $p->brand->name ?? '',
                'price' => number_format($p->price, 2),
                'stock' => $p->stock_quantity,
                'edit_url' => route('admin.products.edit', $p->id),
                'manage_images_url' => route('admin.products.images.index', ['product_id' => $p->id]),
                'images' => $p->images->map(fn($img) => [
                    'id' => $img->id,
                    'url' => $img->image_url,
                    'is_primary' => (bool)$img->is_primary,
                ])->values(),
            ]];
        });
    @endphp

    <script>
        const productsGalleryMap = @json($galleryProductsData);
        let activeProductGallery = null;
        let currentImageIndex = 0;
        let galleryModalInstance = null;

        function openProductGallery(productId) {
            const product = productsGalleryMap[productId];
            if (!product) return;

            activeProductGallery = product;
            currentImageIndex = 0;

            document.getElementById('galleryProductName').innerText = product.name;
            document.getElementById('galleryProductSku').innerText = 'SKU: ' + product.sku;
            document.getElementById('galleryProductCategory').innerText = product.category + (product.brand ? ' | ' + product.brand : '');
            document.getElementById('galleryProductPrice').innerText = '$' + product.price;
            document.getElementById('galleryProductStock').innerText = product.stock + ' units';
            
            const manageMediaLink = document.getElementById('galleryManageImagesLink');
            if (manageMediaLink) {
                manageMediaLink.href = product.manage_images_url;
            }
            const editProductLink = document.getElementById('galleryEditProductLink');
            if (editProductLink) {
                editProductLink.href = product.edit_url;
            }
            const uploadBtn = document.getElementById('galleryUploadBtn');
            if (uploadBtn) {
                uploadBtn.href = product.manage_images_url;
            }

            const viewerWrapper = document.getElementById('galleryViewerWrapper');
            const thumbnailsStrip = document.getElementById('galleryThumbnailsStrip');
            const emptyState = document.getElementById('galleryEmptyState');

            if (!product.images || product.images.length === 0) {
                viewerWrapper.style.display = 'none';
                thumbnailsStrip.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                viewerWrapper.style.display = 'flex';
                thumbnailsStrip.style.display = 'flex';
                emptyState.style.display = 'none';

                renderThumbnailsStrip();
                displayGalleryImage(0);
            }

            if (!galleryModalInstance) {
                galleryModalInstance = new bootstrap.Modal(document.getElementById('productGalleryModal'));
            }
            galleryModalInstance.show();
        }

        function renderThumbnailsStrip() {
            const strip = document.getElementById('galleryThumbnailsStrip');
            strip.innerHTML = '';

            activeProductGallery.images.forEach((img, idx) => {
                const thumb = document.createElement('div');
                thumb.className = `border rounded overflow-hidden p-1 bg-white cursor-pointer gallery-thumb-item ${idx === currentImageIndex ? 'border-primary border-3 shadow-sm' : 'opacity-75'}`;
                thumb.style.cssText = 'width: 58px; height: 58px; cursor: pointer; transition: all 0.15s ease;';
                thumb.onclick = () => displayGalleryImage(idx);
                thumb.innerHTML = `<img src="${img.url}" alt="Thumb" class="w-100 h-100 object-fit-cover rounded">`;
                strip.appendChild(thumb);
            });
        }

        function displayGalleryImage(index) {
            if (!activeProductGallery || !activeProductGallery.images.length) return;

            if (index < 0) index = activeProductGallery.images.length - 1;
            if (index >= activeProductGallery.images.length) index = 0;

            currentImageIndex = index;
            const imgData = activeProductGallery.images[index];

            const mainImg = document.getElementById('galleryMainImage');
            mainImg.style.opacity = '0.3';
            setTimeout(() => {
                mainImg.src = imgData.url;
                mainImg.style.opacity = '1';
            }, 100);

            const coverBadge = document.getElementById('galleryCoverBadge');
            coverBadge.style.display = imgData.is_primary ? 'inline-block' : 'none';

            document.getElementById('galleryCounterBadge').innerText = `${index + 1} / ${activeProductGallery.images.length}`;

            const prevBtn = document.getElementById('galleryPrevBtn');
            const nextBtn = document.getElementById('galleryNextBtn');
            const showNav = activeProductGallery.images.length > 1;
            prevBtn.style.display = showNav ? 'block' : 'none';
            nextBtn.style.display = showNav ? 'block' : 'none';

            const thumbs = document.querySelectorAll('.gallery-thumb-item');
            thumbs.forEach((el, idx) => {
                if (idx === index) {
                    el.className = 'border border-primary border-3 rounded overflow-hidden p-1 bg-white shadow-sm gallery-thumb-item';
                    el.style.opacity = '1';
                } else {
                    el.className = 'border rounded overflow-hidden p-1 bg-white gallery-thumb-item opacity-75';
                    el.style.opacity = '0.7';
                }
            });
        }

        function navigateGallery(direction) {
            displayGalleryImage(currentImageIndex + direction);
        }

        document.addEventListener('keydown', function (e) {
            const modalEl = document.getElementById('productGalleryModal');
            if (modalEl && modalEl.classList.contains('show')) {
                if (e.key === 'ArrowLeft') {
                    navigateGallery(-1);
                } else if (e.key === 'ArrowRight') {
                    navigateGallery(1);
                }
            }
        });
    </script>
</x-app-layout>
