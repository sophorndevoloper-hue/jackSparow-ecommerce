<x-app-layout>
    <!-- Cropper.js Stylesheet -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <style>
        .cropper-view-box, .cropper-face {
            border-radius: 4px;
        }
        .gallery-card {
            transition: all 0.2s ease-in-out;
        }
        .gallery-card.marked-delete {
            border: 2px solid #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.12) !important;
            opacity: 0.8;
        }
        .gallery-card.marked-delete .image-element {
            filter: grayscale(85%) brightness(80%);
        }
        
        /* Dark Theme Styles for Photo Editor */
        .editor-modal-content {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }
        .editor-container {
            min-height: 440px;
            max-height: 520px;
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #1e293b;
        }
        .editor-container img {
            max-width: 100%;
            max-height: 480px;
            display: block;
        }
        .editor-sidebar-panel {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
        }
        .editor-sidebar-panel label, 
        .editor-sidebar-panel span,
        .editor-sidebar-panel h6 {
            color: #f8fafc !important;
        }
        .editor-sidebar-panel .text-muted {
            color: #94a3b8 !important;
        }
        .editor-btn-tool {
            background-color: #1e293b;
            color: #f8fafc;
            border: 1px solid #475569;
        }
        .editor-btn-tool:hover {
            background-color: #334155;
            color: #ffffff;
            border-color: #64748b;
        }
        .editor-btn-tool.active {
            background-color: #0284c7 !important;
            color: #ffffff !important;
            border-color: #38bdf8 !important;
        }
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            max-height: 280px;
            overflow-y: auto;
        }
        /* When dark theme class or body dark is present */
        .admin-shell.theme-dark .search-results-dropdown,
        body.theme-dark .search-results-dropdown {
            background-color: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }
        .search-item-hover:hover {
            background-color: rgba(37, 99, 235, 0.08);
            cursor: pointer;
        }
    </style>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-images" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Catalog &amp; Media Management</p>
                    <h1 class="h3 mb-1">Product Images &amp; Gallery</h1>
                    <p class="text-muted mb-0">Crop and edit hardware photos before uploading, manage galleries, and set primary cover images.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @can('create products')
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Product
                    </a>
                @endcan
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-list-ul me-1"></i> All Products
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
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Unified Search & Select Target Product Bar -->
        <div class="panel p-3 mt-3">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-9 position-relative" id="productComboboxWrapper">
                    <label class="form-label fw-bold small text-body mb-1 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-cpu text-primary me-1"></i> Target Product:</span>
                        <span class="text-muted small fw-normal">{{ $products->count() }} products in catalog</span>
                    </label>

                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" 
                               id="unifiedProductSearchInput" 
                               class="form-control" 
                               placeholder="Type to search product name, SKU, or click arrow to choose..." 
                               value="{{ $selectedProduct ? $selectedProduct->name . ' (SKU: ' . $selectedProduct->sku . ')' : '' }}"
                               autocomplete="off">
                        @if($selectedProductId)
                            <button type="button" class="btn btn-outline-secondary" title="Clear selection (Show all products)" onclick="filterProductGallery('')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @endif
                        <button type="button" class="btn btn-outline-secondary" id="unifiedDropdownToggleBtn" title="Browse all products">
                            <i class="bi bi-chevron-down" id="unifiedDropdownChevron"></i>
                        </button>
                    </div>

                    <!-- Dropdown Options List -->
                    <div class="combobox-dropdown-menu p-1 d-none" id="unifiedProductDropdown">
                        <div id="unifiedProductOptionsContainer">
                            @foreach($products as $p)
                                <div class="combobox-item-row d-flex align-items-center justify-content-between mb-1 {{ (string)$selectedProductId === (string)$p->id ? 'is-active-focus' : '' }}"
                                     data-id="{{ $p->id }}"
                                     data-name="{{ $p->name }}"
                                     data-sku="{{ $p->sku }}"
                                     data-category="{{ $p->category->name ?? '' }}"
                                     data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . ($p->category->name ?? '') . ' ' . ($p->brand?->name ?? '')) }}"
                                     onclick="filterProductGallery({{ $p->id }})">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                        <div class="rounded bg-light border overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                            @if($p->primary_image_url)
                                                <img src="{{ $p->primary_image_url }}" alt="{{ $p->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <i class="bi bi-cpu text-primary small"></i>
                                            @endif
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fw-semibold text-truncate small item-title">{{ $p->name }}</div>
                                            <div class="text-muted font-monospace" style="font-size: 11px;">
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">{{ $p->sku }}</span>
                                                <span class="ms-1">{{ $p->category->name ?? '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end text-nowrap flex-shrink-0">
                                        <span class="badge {{ $p->images->count() > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-muted' }}" style="font-size: 11px;">
                                            <i class="bi bi-images me-1"></i>{{ $p->images->count() }} photo(s)
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="unifiedNoMatchAlert" class="text-center py-4 text-muted small d-none">
                            <i class="bi bi-search me-1"></i> No matching products found
                        </div>
                    </div>
                </div>

                <!-- Show All / Reset button -->
                <div class="col-12 col-md-3 text-md-end pt-md-4">
                    @if($selectedProductId)
                        <a href="{{ route('admin.products.images.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-grid me-1"></i> View All Products
                        </a>
                    @else
                        <span class="badge bg-secondary-subtle text-body border py-2 px-3 d-inline-block w-100 text-center">
                            <i class="bi bi-collection me-1 text-primary"></i> Showing All Product Galleries
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if($selectedProduct)
            <!-- Main Form with Save & Cancel -->
            <form id="galleryManagerForm" action="{{ route('admin.products.images.save') }}" method="POST" enctype="multipart/form-data" class="mt-4" onsubmit="return validateFormBeforeSave(event)">
                @csrf
                <input type="hidden" name="product_id" value="{{ $selectedProduct->id }}">
                <input type="hidden" name="primary_image_id" id="selectedPrimaryInput" value="{{ $selectedProduct->images->firstWhere('is_primary', true)?->id ?? $selectedProduct->images->first()?->id }}">

                <!-- Hidden inputs for files and deletions -->
                <input type="file" name="images[]" id="hiddenFormFileInput" multiple accept="image/*" style="display: none;">
                <div id="deletedImageIdsContainer"></div>

                <!-- Floating / Top Action Banner -->
                <div class="panel p-3 mb-4 bg-light border border-primary-subtle d-flex flex-wrap align-items-center justify-content-between gap-3 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Editing Media: {{ $selectedProduct->name }}</h6>
                            <span class="text-muted small" id="pendingChangesText">No unsaved changes yet. Add new photos or choose photos to delete.</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" id="cancelChangesBtn" onclick="resetGalleryChanges()">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" id="saveChangesBtn">
                            <i class="bi bi-check-circle me-1"></i> Save Changes
                        </button>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Left Column: Add & Edit New Photos -->
                    <div class="col-12 col-lg-5">
                        <div class="panel p-4 h-100">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0 text-dark fw-bold">
                                    <i class="bi bi-cloud-arrow-up text-primary me-2"></i> Add New Photos
                                </h5>
                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 11px;">
                                    <i class="bi bi-crop me-1"></i> Interactive Cropper
                                </span>
                            </div>
                            <p class="text-muted small mb-3">Drop photos here. You can <strong>crop, rotate, tune lighting, and edit</strong> photos before uploading.</p>

                            <!-- Dropzone File Picker -->
                            <div class="border border-2 border-dashed rounded-3 p-4 text-center bg-light-subtle position-relative mb-3" id="dropArea" style="cursor: pointer;">
                                <i class="bi bi-cloud-arrow-up fs-1 text-primary d-block mb-2"></i>
                                <span class="fw-bold d-block text-dark mb-1">Click to browse or drag &amp; drop images</span>
                                <span class="text-muted small d-block mb-2">JPG, PNG, WEBP, GIF (Max 5MB each)</span>
                                <input type="file" id="imagesPickerInput" multiple accept="image/*" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" onchange="handleNewFileSelection(this)">
                            </div>

                            <!-- Staged Photos Queue -->
                            <div id="stagedPhotosHeader" class="d-flex align-items-center justify-content-between mb-2" style="display: none !important;">
                                <span class="small fw-bold text-dark">Photos to Upload (<span id="stagedCount">0</span>):</span>
                                <button type="button" class="btn btn-link text-danger p-0 small text-decoration-none" onclick="clearStagedPhotos()">
                                    Clear all
                                </button>
                            </div>

                            <div id="newImagesPreviewContainer" class="row g-2 mb-3"></div>
                        </div>
                    </div>

                    <!-- Right Column: Current Existing Gallery -->
                    <div class="col-12 col-lg-7">
                        <div class="panel p-4 h-100">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 border-bottom pb-2">
                                <div>
                                    <h5 class="mb-0 text-dark fw-bold">
                                        <i class="bi bi-collection text-primary me-2"></i> Current Product Photos ({{ $selectedProduct->images->count() }})
                                    </h5>
                                    <div class="mt-1" id="galleryCountersBadge">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" id="countKeepingBadge">
                                            Keeping: {{ $selectedProduct->images->count() }}
                                        </span>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" id="countDeletingBadge" style="display: none;">
                                            Deleting: 0
                                        </span>
                                    </div>
                                </div>
                                @if($selectedProduct->images->isNotEmpty())
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;" onclick="toggleSelectAllDeletions()">
                                            <i class="bi bi-check2-square me-1"></i> <span id="selectAllBtnText">Select All to Delete</span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if($selectedProduct->images->isEmpty())
                                <div id="noExistingImagesPlaceholder" class="text-center py-5">
                                    <i class="bi bi-image text-muted display-4 d-block mb-3"></i>
                                    <h6 class="text-dark fw-bold">No images stored for this product</h6>
                                    <p class="text-muted small mb-0">Use the upload box on the left to add photos and click Save.</p>
                                </div>
                            @else
                                <div class="row g-3" id="existingGalleryGrid">
                                    @foreach($selectedProduct->images as $img)
                                        <div class="col-6 col-sm-4 image-card-wrapper" id="image-wrapper-{{ $img->id }}" data-image-id="{{ $img->id }}">
                                            <div class="card h-100 border shadow-sm position-relative overflow-hidden gallery-card" id="card-{{ $img->id }}">
                                                <!-- Image Preview Container -->
                                                <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <img src="{{ $img->image_url }}" alt="Product Photo" class="w-100 h-100 object-fit-cover image-element" id="img-elem-{{ $img->id }}">
                                                    
                                                    <!-- Primary Cover Badge -->
                                                    <span class="position-absolute top-0 start-0 m-2 badge bg-success shadow-sm cover-badge" id="cover-badge-{{ $img->id }}" style="{{ $img->is_primary ? '' : 'display: none;' }}">
                                                        <i class="bi bi-star-fill me-1"></i> Cover Photo
                                                    </span>

                                                    <!-- Marked for Deletion Badge (Top Right) -->
                                                    <span class="position-absolute top-0 end-0 m-2 badge bg-danger shadow-sm delete-status-badge" id="delete-status-badge-{{ $img->id }}" style="display: none;">
                                                        <i class="bi bi-trash-fill me-1"></i> To Delete
                                                    </span>
                                                </div>

                                                <!-- Card Actions Footer -->
                                                <div class="card-body p-2 d-flex align-items-center justify-content-between bg-white border-top">
                                                    <!-- Left: Set Cover / Cover Status -->
                                                    <div class="cover-control-wrapper" id="cover-ctrl-{{ $img->id }}">
                                                        <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 set-cover-btn" id="set-cover-btn-{{ $img->id }}" style="font-size: 11px; {{ $img->is_primary ? 'display: none;' : '' }}" onclick="selectPrimaryImage({{ $img->id }})">
                                                            <i class="bi bi-star me-1"></i> Set Cover
                                                        </button>
                                                        <span class="text-success small fw-bold is-cover-text" id="is-cover-text-{{ $img->id }}" style="font-size: 11px; {{ $img->is_primary ? '' : 'display: none;' }}">
                                                            <i class="bi bi-check-circle-fill"></i> Primary
                                                        </span>
                                                    </div>

                                                    <!-- Right: Toggle Delete / Keep Button -->
                                                    <div>
                                                        <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 delete-toggle-btn" id="delete-toggle-btn-{{ $img->id }}" title="Click to Delete" onclick="toggleImageDeleteSelection({{ $img->id }})" style="font-size: 11px;">
                                                            <i class="bi bi-trash"></i> Delete
                                                        </button>
                                                        <button type="button" class="btn btn-success btn-sm py-0 px-2 undo-toggle-btn" id="undo-toggle-btn-{{ $img->id }}" title="Click to Keep" onclick="toggleImageDeleteSelection({{ $img->id }})" style="font-size: 11px; display: none;">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Keep
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Bottom Save & Cancel Bar -->
                <div class="panel p-3 mt-4 d-flex align-items-center justify-content-end gap-2 bg-light border">
                    <button type="button" class="btn btn-outline-secondary px-3" onclick="resetGalleryChanges()">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        @else
            <!-- Overview Grid when no product is selected -->
            <div class="panel p-4 mt-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="mb-0 text-dark fw-bold">
                        <i class="bi bi-collection text-primary me-2"></i> All Hardware Photos Gallery
                    </h5>
                    <small class="text-muted">Select or search a target product above to add, edit, or remove photos with Save / Cancel.</small>
                </div>

                @if($images->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-image text-muted display-4 d-block mb-3"></i>
                        <h6 class="text-dark fw-bold">No product images found in catalog</h6>
                        <p class="text-muted small mb-0">Select or search a product from the top bar to manage hardware photos.</p>
                    </div>
                @else
                    <div class="row g-3">
                        @foreach($images as $img)
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                <div class="card h-100 border shadow-sm position-relative overflow-hidden">
                                    <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 130px;">
                                        <img src="{{ $img->image_url }}" alt="Product Photo" class="w-100 h-100 object-fit-cover">
                                        @if($img->is_primary)
                                            <span class="position-absolute top-0 start-0 m-1 badge bg-success shadow-sm" style="font-size: 9px;">
                                                <i class="bi bi-star-fill"></i> Cover
                                            </span>
                                        @endif
                                    </div>
                                    <div class="card-body p-2 d-flex flex-column justify-content-between">
                                        <small class="text-dark fw-bold text-truncate d-block mb-2" title="{{ $img->product?->name }}">
                                            {{ $img->product?->name ?? 'Unassigned' }}
                                        </small>

                                        <a href="{{ route('admin.products.images.index', ['product_id' => $img->product_id]) }}" class="btn btn-outline-primary btn-sm w-100 py-0" style="font-size: 11px;">
                                            <i class="bi bi-pencil me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($images->hasPages())
                        <div class="mt-4 border-top pt-3">
                            {{ $images->links() }}
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- INTERACTIVE PHOTO EDITOR MODAL (Dark Theme & Cropper.js) -->
    <!-- ======================================================== -->
    <div class="modal fade" id="imageEditorModal" tabindex="-1" aria-labelledby="imageEditorModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content editor-modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom border-secondary bg-dark text-white">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="imageEditorModalLabel">
                        <i class="bi bi-crop text-primary"></i> Photo Editor &amp; Cropper
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3" style="background-color: #0f172a;">
                    <div class="row g-3">
                        <!-- Left: Cropper Workspace -->
                        <div class="col-12 col-lg-8">
                            <div class="editor-container shadow-inner">
                                <img id="editorTargetImage" src="" alt="Image to Edit">
                            </div>

                            <!-- Toolbar Strip with high contrast buttons -->
                            <div class="d-flex align-items-center justify-content-center gap-2 mt-3 flex-wrap">
                                <div class="btn-group btn-group-sm shadow-sm">
                                    <button type="button" class="btn editor-btn-tool" onclick="cropperRotate(-90)" title="Rotate Counter-Clockwise">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> -90&deg;
                                    </button>
                                    <button type="button" class="btn editor-btn-tool" onclick="cropperRotate(90)" title="Rotate Clockwise">
                                        <i class="bi bi-arrow-clockwise me-1"></i> +90&deg;
                                    </button>
                                </div>

                                <div class="btn-group btn-group-sm shadow-sm">
                                    <button type="button" class="btn editor-btn-tool" onclick="cropperFlip('h')" title="Flip Horizontal">
                                        <i class="bi bi-symmetry-vertical me-1"></i> Flip H
                                    </button>
                                    <button type="button" class="btn editor-btn-tool" onclick="cropperFlip('v')" title="Flip Vertical">
                                        <i class="bi bi-symmetry-horizontal me-1"></i> Flip V
                                    </button>
                                </div>

                                <div class="btn-group btn-group-sm shadow-sm">
                                    <button type="button" class="btn editor-btn-tool" onclick="cropperZoom(0.1)" title="Zoom In">
                                        <i class="bi bi-zoom-in"></i>
                                    </button>
                                    <button type="button" class="btn editor-btn-tool" onclick="cropperZoom(-0.1)" title="Zoom Out">
                                        <i class="bi bi-zoom-out"></i>
                                    </button>
                                    <button type="button" class="btn editor-btn-tool text-warning" onclick="cropperReset()" title="Reset All Transforms">
                                        <i class="bi bi-arrow-repeat me-1"></i> Reset
                                    </button>
                                </div>

                                <div class="btn-group btn-group-sm shadow-sm">
                                    <button type="button" id="dragModePanBtn" class="btn editor-btn-tool" onclick="setDragMode('move')" title="Move Canvas Mode">
                                        <i class="bi bi-arrows-move me-1"></i> Pan
                                    </button>
                                    <button type="button" id="dragModeCropBtn" class="btn editor-btn-tool active" onclick="setDragMode('crop')" title="Crop Box Mode">
                                        <i class="bi bi-crop me-1"></i> Crop
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Aspect Ratio & Color Tuning Controls -->
                        <div class="col-12 col-lg-4">
                            <div class="editor-sidebar-panel p-3 rounded h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <!-- Aspect Ratio Presets -->
                                    <h6 class="fw-bold text-white mb-2 d-flex align-items-center gap-1">
                                        <i class="bi bi-aspect-ratio text-info"></i> Aspect Ratio Presets
                                    </h6>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <button type="button" class="btn editor-btn-tool btn-sm w-100 ratio-btn active" id="ratioBtnFree" onclick="cropperSetRatio(NaN, this)">
                                                Free (Custom)
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn editor-btn-tool btn-sm w-100 ratio-btn" onclick="cropperSetRatio(1, this)">
                                                1:1 (Square)
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn editor-btn-tool btn-sm w-100 ratio-btn" onclick="cropperSetRatio(4/3, this)">
                                                4:3 (Standard)
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn editor-btn-tool btn-sm w-100 ratio-btn" onclick="cropperSetRatio(16/9, this)">
                                                16:9 (Widescreen)
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Lighting & Color Filters -->
                                    <h6 class="fw-bold text-white mb-2 d-flex align-items-center gap-1">
                                        <i class="bi bi-sliders text-info"></i> Lighting &amp; Color Tuning
                                    </h6>
                                    
                                    <!-- Brightness -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small text-white">
                                            <span>Brightness</span>
                                            <span id="brightnessVal" class="fw-bold text-info">100%</span>
                                        </div>
                                        <input type="range" class="form-range" id="brightnessSlider" min="30" max="180" value="100" oninput="updateFilterValues()">
                                    </div>

                                    <!-- Contrast -->
                                    <div class="mb-2">
                                        <div class="d-flex justify-content-between small text-white">
                                            <span>Contrast</span>
                                            <span id="contrastVal" class="fw-bold text-info">100%</span>
                                        </div>
                                        <input type="range" class="form-range" id="contrastSlider" min="30" max="180" value="100" oninput="updateFilterValues()">
                                    </div>

                                    <!-- Saturation -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small text-white">
                                            <span>Saturation</span>
                                            <span id="saturationVal" class="fw-bold text-info">100%</span>
                                        </div>
                                        <input type="range" class="form-range" id="saturationSlider" min="0" max="200" value="100" oninput="updateFilterValues()">
                                    </div>

                                    <!-- Quick Filter Presets -->
                                    <div class="d-flex gap-1 mb-3">
                                        <button type="button" class="btn editor-btn-tool btn-sm flex-grow-1 py-1" style="font-size: 11px;" onclick="applyPreset('normal')">Normal</button>
                                        <button type="button" class="btn editor-btn-tool btn-sm flex-grow-1 py-1" style="font-size: 11px;" onclick="applyPreset('bw')">B&amp;W</button>
                                        <button type="button" class="btn editor-btn-tool btn-sm flex-grow-1 py-1" style="font-size: 11px;" onclick="applyPreset('warm')">Warm</button>
                                        <button type="button" class="btn editor-btn-tool btn-sm flex-grow-1 py-1" style="font-size: 11px;" onclick="applyPreset('cool')">Cool</button>
                                    </div>
                                </div>

                                <div class="border-top border-secondary pt-3">
                                    <button type="button" class="btn btn-success w-100 py-2 fw-bold text-white shadow" onclick="saveCropperResult()">
                                        <i class="bi bi-check2 me-1"></i> Apply &amp; Save to Upload Queue
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top border-secondary bg-dark text-white">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel / Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal when saving deletions -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content editor-modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Permanent Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-2 text-white">You have selected <strong class="text-danger fw-bold" id="confirmDeleteCount">0</strong> product photo(s) to delete permanently.</p>
                    <p class="text-muted small mb-0">Once saved, these files will be unlinked and deleted from storage. Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer border-top border-secondary bg-dark">
                    <button type="button" class="btn btn-outline-secondary btn-sm text-white" data-bs-dismiss="modal">Cancel / Review</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" onclick="proceedWithFormSubmit()">
                        Yes, Delete &amp; Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Product List Data for Fast Live Search -->
    @php
        $productsJsonData = $products->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'category' => $p->category->name ?? '',
                'images_count' => $p->images->count(),
                'primary_image' => $p->primary_image_url,
            ];
        });
    @endphp

    <!-- Cropper.js Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

    <script>
        const catalogProducts = @json($productsJsonData);

        function filterProductGallery(productId) {
            if (productId) {
                window.location.href = "{{ route('admin.products.images.index') }}?product_id=" + productId;
            } else {
                window.location.href = "{{ route('admin.products.images.index') }}";
            }
        }

        // ==========================================
        // UNIFIED SEARCHABLE PRODUCT COMBOBOX
        // ==========================================
        (function initUnifiedCombobox() {
            const wrapper = document.getElementById('productComboboxWrapper');
            const searchInput = document.getElementById('unifiedProductSearchInput');
            const dropdown = document.getElementById('unifiedProductDropdown');
            const toggleBtn = document.getElementById('unifiedDropdownToggleBtn');
            const chevron = document.getElementById('unifiedDropdownChevron');
            const noMatchAlert = document.getElementById('unifiedNoMatchAlert');
            const items = dropdown ? dropdown.querySelectorAll('.combobox-item-row') : [];

            if (!wrapper || !searchInput || !dropdown) return;

            function openDropdown() {
                dropdown.classList.remove('d-none');
                if (chevron) {
                    chevron.classList.remove('bi-chevron-down');
                    chevron.classList.add('bi-chevron-up');
                }
            }

            function closeDropdown() {
                dropdown.classList.add('d-none');
                if (chevron) {
                    chevron.classList.remove('bi-chevron-up');
                    chevron.classList.add('bi-chevron-down');
                }
            }

            function toggleDropdown() {
                if (dropdown.classList.contains('d-none')) {
                    openDropdown();
                } else {
                    closeDropdown();
                }
            }

            function filterItems(query) {
                const term = query.trim().toLowerCase();
                let visibleCount = 0;

                items.forEach(item => {
                    const searchData = item.getAttribute('data-search') || '';
                    if (!term || searchData.includes(term)) {
                        item.style.display = '';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (noMatchAlert) {
                    if (visibleCount === 0) {
                        noMatchAlert.classList.remove('d-none');
                    } else {
                        noMatchAlert.classList.add('d-none');
                    }
                }
            }

            // Open and filter on input
            searchInput.addEventListener('input', function() {
                openDropdown();
                filterItems(this.value);
            });

            // Open on focus and select text for fast overwriting
            searchInput.addEventListener('focus', function() {
                openDropdown();
                this.select();
                filterItems('');
            });

            // Toggle button
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDropdown();
                    if (!dropdown.classList.contains('d-none')) {
                        searchInput.focus();
                    }
                });
            }

            // Close on outside click
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Keyboard navigation (Escape to close)
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeDropdown();
                    searchInput.blur();
                }
            });
        })();

        // ==========================================
        // STATE VARIABLES
        // ==========================================
        const markedForRemovalIds = new Set();
        let stagedNewFiles = []; // Array of File objects
        let currentPrimaryId = "{{ $selectedProduct ? ($selectedProduct->images->firstWhere('is_primary', true)?->id ?? ($selectedProduct->images->first()?->id ?? '')) : '' }}";
        const initialPrimaryId = currentPrimaryId;
        const totalExistingImagesCount = {{ $selectedProduct ? $selectedProduct->images->count() : 0 }};

        // ==========================================
        // INDIVIDUAL DELETE / KEEP TOGGLE
        // ==========================================
        function toggleImageDeleteSelection(imageId) {
            const card = document.getElementById('card-' + imageId);
            const statusBadge = document.getElementById('delete-status-badge-' + imageId);
            const deleteBtn = document.getElementById('delete-toggle-btn-' + imageId);
            const undoBtn = document.getElementById('undo-toggle-btn-' + imageId);
            const coverCtrl = document.getElementById('cover-ctrl-' + imageId);

            if (markedForRemovalIds.has(imageId)) {
                // UNMARK (KEEP)
                markedForRemovalIds.delete(imageId);
                
                if (card) card.classList.remove('marked-delete');
                if (statusBadge) statusBadge.style.display = 'none';
                if (deleteBtn) deleteBtn.style.display = 'inline-block';
                if (undoBtn) undoBtn.style.display = 'none';
                if (coverCtrl) coverCtrl.style.opacity = '1';
            } else {
                // MARK TO DELETE
                markedForRemovalIds.add(imageId);

                if (card) card.classList.add('marked-delete');
                if (statusBadge) statusBadge.style.display = 'inline-block';
                if (deleteBtn) deleteBtn.style.display = 'none';
                if (undoBtn) undoBtn.style.display = 'inline-block';
                if (coverCtrl) coverCtrl.style.opacity = '0.4';
            }

            syncRemovalHiddenInputs();
            updateGalleryCounters();
            updatePendingChangesIndicator();
        }

        function toggleSelectAllDeletions() {
            const allWrappers = document.querySelectorAll('.image-card-wrapper');
            const btnText = document.getElementById('selectAllBtnText');

            if (markedForRemovalIds.size === totalExistingImagesCount && totalExistingImagesCount > 0) {
                // Deselect all (Keep all)
                allWrappers.forEach(w => {
                    const id = parseInt(w.getAttribute('data-image-id'));
                    if (markedForRemovalIds.has(id)) {
                        toggleImageDeleteSelection(id);
                    }
                });
                if (btnText) btnText.innerText = 'Select All to Delete';
            } else {
                // Select all (Mark all to delete)
                allWrappers.forEach(w => {
                    const id = parseInt(w.getAttribute('data-image-id'));
                    if (!markedForRemovalIds.has(id)) {
                        toggleImageDeleteSelection(id);
                    }
                });
                if (btnText) btnText.innerText = 'Deselect All (Keep All)';
            }
        }

        function syncRemovalHiddenInputs() {
            const container = document.getElementById('deletedImageIdsContainer');
            if (!container) return;
            container.innerHTML = '';
            markedForRemovalIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_image_ids[]';
                input.value = id;
                container.appendChild(input);
            });
        }

        function updateGalleryCounters() {
            const keepingCount = totalExistingImagesCount - markedForRemovalIds.size;
            const deletingCount = markedForRemovalIds.size;

            const keepingBadge = document.getElementById('countKeepingBadge');
            const deletingBadge = document.getElementById('countDeletingBadge');
            const btnText = document.getElementById('selectAllBtnText');

            if (keepingBadge) {
                keepingBadge.innerText = `Keeping: ${keepingCount}`;
            }

            if (deletingBadge) {
                if (deletingCount > 0) {
                    deletingBadge.style.display = 'inline-block';
                    deletingBadge.innerText = `Deleting: ${deletingCount}`;
                } else {
                    deletingBadge.style.display = 'none';
                }
            }

            if (btnText) {
                btnText.innerText = (deletingCount === totalExistingImagesCount && totalExistingImagesCount > 0)
                    ? 'Deselect All (Keep All)' : 'Select All to Delete';
            }
        }

        // ==========================================
        // PRIMARY COVER SELECTION
        // ==========================================
        function selectPrimaryImage(targetId) {
            currentPrimaryId = targetId.toString();
            document.getElementById('selectedPrimaryInput').value = currentPrimaryId;

            // Update all existing card badges
            document.querySelectorAll('.image-card-wrapper').forEach(wrapper => {
                const imgId = wrapper.getAttribute('data-image-id');
                const coverBadge = document.getElementById('cover-badge-' + imgId);
                const setCoverBtn = document.getElementById('set-cover-btn-' + imgId);
                const isCoverText = document.getElementById('is-cover-text-' + imgId);

                if (imgId === currentPrimaryId) {
                    if (coverBadge) coverBadge.style.display = 'inline-block';
                    if (setCoverBtn) setCoverBtn.style.display = 'none';
                    if (isCoverText) isCoverText.style.display = 'inline-block';
                } else {
                    if (coverBadge) coverBadge.style.display = 'none';
                    if (setCoverBtn) setCoverBtn.style.display = 'inline-block';
                    if (isCoverText) isCoverText.style.display = 'none';
                }
            });

            // Update any new staged items cover state
            document.querySelectorAll('.staged-thumb-card').forEach(card => {
                const newIdx = card.getAttribute('data-new-index');
                const isSelected = currentPrimaryId === ('new_' + newIdx);
                const badge = card.querySelector('.staged-cover-badge');
                const btn = card.querySelector('.staged-set-cover-btn');

                if (badge) badge.style.display = isSelected ? 'inline-block' : 'none';
                if (btn) btn.style.display = isSelected ? 'none' : 'inline-block';
            });

            updatePendingChangesIndicator();
        }

        // ==========================================
        // FILE SELECTION & STAGING
        // ==========================================
        function handleNewFileSelection(input) {
            if (!input.files || input.files.length === 0) return;

            const newFiles = Array.from(input.files);
            stagedNewFiles = stagedNewFiles.concat(newFiles);
            input.value = '';
            renderStagedNewFiles();
            syncFormFileInput();
            updatePendingChangesIndicator();
        }

        function renderStagedNewFiles() {
            const container = document.getElementById('newImagesPreviewContainer');
            const header = document.getElementById('stagedPhotosHeader');
            const countSpan = document.getElementById('stagedCount');

            if (!container) return;

            container.innerHTML = '';
            if (stagedNewFiles.length === 0) {
                if (header) header.style.setProperty('display', 'none', 'important');
                return;
            }

            if (header) header.style.setProperty('display', 'flex', 'important');
            if (countSpan) countSpan.innerText = stagedNewFiles.length;

            stagedNewFiles.forEach((file, index) => {
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 staged-thumb-card';
                col.setAttribute('data-new-index', index);

                const reader = new FileReader();
                reader.onload = function(e) {
                    const isCover = currentPrimaryId === ('new_' + index);
                    col.innerHTML = `
                        <div class="card h-100 border border-primary border-2 shadow-sm position-relative overflow-hidden">
                            <div class="position-relative bg-light d-flex align-items-center justify-content-center" style="height: 110px;">
                                <img src="${e.target.result}" class="w-100 h-100 object-fit-cover" alt="New Photo">
                                <span class="badge bg-primary position-absolute top-0 start-0 m-1 shadow-sm font-monospace" style="font-size: 8px;">New</span>
                                <span class="badge bg-success position-absolute top-0 end-0 m-1 shadow-sm staged-cover-badge" style="font-size: 8px; ${isCover ? '' : 'display: none;'}">Cover</span>
                            </div>
                            <div class="card-body p-2 bg-light d-flex flex-column justify-content-between">
                                <small class="text-truncate d-block fw-bold text-dark mb-1" title="${file.name}" style="font-size: 10px;">${file.name}</small>
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <button type="button" class="btn btn-dark btn-sm py-0 px-2 fw-bold" style="font-size: 10px;" title="Crop & Edit Image" onclick="openCropperEditor(${index})">
                                        <i class="bi bi-crop"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-1 staged-set-cover-btn" style="font-size: 10px; ${isCover ? 'display: none;' : ''}" onclick="selectPrimaryImage('new_${index}')">
                                        Cover
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-1" title="Remove from queue" onclick="removeStagedFile(${index})">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
                container.appendChild(col);
            });
        }

        function syncFormFileInput() {
            const hiddenInput = document.getElementById('hiddenFormFileInput');
            if (!hiddenInput) return;

            const dt = new DataTransfer();
            stagedNewFiles.forEach(file => {
                dt.items.add(file);
            });
            hiddenInput.files = dt.files;
        }

        function removeStagedFile(index) {
            stagedNewFiles.splice(index, 1);
            if (currentPrimaryId === ('new_' + index)) {
                currentPrimaryId = initialPrimaryId;
                document.getElementById('selectedPrimaryInput').value = currentPrimaryId;
            }
            renderStagedNewFiles();
            syncFormFileInput();
            updatePendingChangesIndicator();
        }

        function clearStagedPhotos() {
            stagedNewFiles = [];
            renderStagedNewFiles();
            syncFormFileInput();
            updatePendingChangesIndicator();
        }

        function resetGalleryChanges() {
            // Unmark all removals
            markedForRemovalIds.forEach(id => {
                toggleImageDeleteSelection(id);
            });
            markedForRemovalIds.clear();
            syncRemovalHiddenInputs();
            updateGalleryCounters();

            // Clear staged files
            clearStagedPhotos();

            // Reset primary
            selectPrimaryImage(initialPrimaryId);

            updatePendingChangesIndicator();
        }

        function updatePendingChangesIndicator() {
            const text = document.getElementById('pendingChangesText');
            if (!text) return;

            const additions = stagedNewFiles.length;
            const removals = markedForRemovalIds.size;
            const primaryChanged = currentPrimaryId !== initialPrimaryId;

            if (additions === 0 && removals === 0 && !primaryChanged) {
                text.innerText = 'No unsaved changes. Your product photos are untouched.';
                text.className = 'text-muted small';
            } else {
                const parts = [];
                if (additions > 0) parts.push(`${additions} new photo(s) to add`);
                if (removals > 0) parts.push(`${removals} photo(s) selected to delete`);
                if (primaryChanged) parts.push('cover photo changed');

                text.innerHTML = `<span class="badge bg-warning text-dark me-1"><i class="bi bi-clock-history me-1"></i> Pending</span> ${parts.join(', ')} (Click <strong>Save Changes</strong> to apply or <strong>Cancel</strong> to discard)`;
                text.className = 'text-warning-emphasis small fw-medium';
            }
        }

        // ==========================================
        // SUBMISSION & CONFIRMATION
        // ==========================================
        function validateFormBeforeSave(event) {
            if (markedForRemovalIds.size > 0) {
                event.preventDefault();
                document.getElementById('confirmDeleteCount').innerText = markedForRemovalIds.size;
                const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                modal.show();
                return false;
            }
            return true;
        }

        function proceedWithFormSubmit() {
            const modalEl = document.getElementById('deleteConfirmModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
            
            document.getElementById('galleryManagerForm').submit();
        }

        // ========================================================
        // ADVANCED CROPPER.JS & IMAGE EDITOR INTEGRATION
        // ========================================================
        let cropperInstance = null;
        let cropperActiveIndex = null;
        let cropperFlipH = 1;
        let cropperFlipV = 1;
        let editorModalInstance = null;

        function openCropperEditor(index) {
            cropperActiveIndex = index;
            const file = stagedNewFiles[index];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const imgElement = document.getElementById('editorTargetImage');
                imgElement.src = e.target.result;

                if (!editorModalInstance) {
                    editorModalInstance = new bootstrap.Modal(document.getElementById('imageEditorModal'));
                }
                editorModalInstance.show();

                // Initialize Cropper
                setTimeout(() => {
                    if (cropperInstance) {
                        cropperInstance.destroy();
                    }

                    cropperFlipH = 1;
                    cropperFlipV = 1;
                    resetFilterSliders();

                    cropperInstance = new Cropper(imgElement, {
                        aspectRatio: NaN, // Free by default
                        viewMode: 1,
                        autoCropArea: 0.92,
                        responsive: true,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                    });
                }, 200);
            };
            reader.readAsDataURL(file);
        }

        function cropperRotate(deg) {
            if (cropperInstance) cropperInstance.rotate(deg);
        }

        function cropperFlip(axis) {
            if (!cropperInstance) return;
            if (axis === 'h') {
                cropperFlipH = -cropperFlipH;
                cropperInstance.scaleX(cropperFlipH);
            } else if (axis === 'v') {
                cropperFlipV = -cropperFlipV;
                cropperInstance.scaleY(cropperFlipV);
            }
        }

        function cropperZoom(delta) {
            if (cropperInstance) cropperInstance.zoom(delta);
        }

        function cropperReset() {
            if (cropperInstance) {
                cropperInstance.reset();
                cropperFlipH = 1;
                cropperFlipV = 1;
            }
            resetFilterSliders();
        }

        function setDragMode(mode) {
            if (cropperInstance) cropperInstance.setDragMode(mode);
            const panBtn = document.getElementById('dragModePanBtn');
            const cropBtn = document.getElementById('dragModeCropBtn');
            if (panBtn && cropBtn) {
                if (mode === 'move') {
                    panBtn.classList.add('active');
                    cropBtn.classList.remove('active');
                } else {
                    cropBtn.classList.add('active');
                    panBtn.classList.remove('active');
                }
            }
        }

        function cropperSetRatio(ratio, btnElement) {
            if (cropperInstance) cropperInstance.setAspectRatio(ratio);
            document.querySelectorAll('.ratio-btn').forEach(b => b.classList.remove('active'));
            if (btnElement) btnElement.classList.add('active');
        }

        function resetFilterSliders() {
            document.getElementById('brightnessSlider').value = 100;
            document.getElementById('contrastSlider').value = 100;
            document.getElementById('saturationSlider').value = 100;
            document.getElementById('brightnessVal').innerText = '100%';
            document.getElementById('contrastVal').innerText = '100%';
            document.getElementById('saturationVal').innerText = '100%';
            applyCanvasFilters();
        }

        function updateFilterValues() {
            const b = document.getElementById('brightnessSlider').value;
            const c = document.getElementById('contrastSlider').value;
            const s = document.getElementById('saturationSlider').value;
            document.getElementById('brightnessVal').innerText = b + '%';
            document.getElementById('contrastVal').innerText = c + '%';
            document.getElementById('saturationVal').innerText = s + '%';
            applyCanvasFilters();
        }

        function applyCanvasFilters() {
            const b = document.getElementById('brightnessSlider').value;
            const c = document.getElementById('contrastSlider').value;
            const s = document.getElementById('saturationSlider').value;

            const container = document.querySelector('.cropper-container');
            if (container) {
                container.style.filter = `brightness(${b}%) contrast(${c}%) saturate(${s}%)`;
            }
        }

        function applyPreset(preset) {
            if (preset === 'normal') {
                resetFilterSliders();
            } else if (preset === 'bw') {
                document.getElementById('brightnessSlider').value = 105;
                document.getElementById('contrastSlider').value = 125;
                document.getElementById('saturationSlider').value = 0;
                updateFilterValues();
            } else if (preset === 'warm') {
                document.getElementById('brightnessSlider').value = 105;
                document.getElementById('contrastSlider').value = 110;
                document.getElementById('saturationSlider').value = 130;
                updateFilterValues();
            } else if (preset === 'cool') {
                document.getElementById('brightnessSlider').value = 100;
                document.getElementById('contrastSlider').value = 115;
                document.getElementById('saturationSlider').value = 85;
                updateFilterValues();
            }
        }

        function saveCropperResult() {
            if (!cropperInstance || cropperActiveIndex === null) return;

            const b = document.getElementById('brightnessSlider').value;
            const c = document.getElementById('contrastSlider').value;
            const s = document.getElementById('saturationSlider').value;

            // Get high-res cropped canvas from Cropper.js
            const croppedCanvas = cropperInstance.getCroppedCanvas({
                maxWidth: 2400,
                maxHeight: 2400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!croppedCanvas) return;

            // Render filter adjustments onto final canvas
            const finalCanvas = document.createElement('canvas');
            finalCanvas.width = croppedCanvas.width;
            finalCanvas.height = croppedCanvas.height;
            const ctx = finalCanvas.getContext('2d');
            ctx.filter = `brightness(${b}%) contrast(${c}%) saturate(${s}%)`;
            ctx.drawImage(croppedCanvas, 0, 0);

            const originalFile = stagedNewFiles[cropperActiveIndex];
            const mimeType = originalFile.type || 'image/jpeg';

            finalCanvas.toBlob(function(blob) {
                if (!blob) return;

                const editedFile = new File([blob], originalFile.name, {
                    type: mimeType,
                    lastModified: Date.now()
                });

                // Update staged queue with the cropped and tuned file
                stagedNewFiles[cropperActiveIndex] = editedFile;
                renderStagedNewFiles();
                syncFormFileInput();

                if (editorModalInstance) {
                    editorModalInstance.hide();
                }
            }, mimeType, 0.92);
        }
    </script>
</x-app-layout>
