<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Breadcrumb & Heading -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-plus-circle" aria-hidden="true"></i></span>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">Menu Setup</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Menu</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">Add New Menu</h1>
                    <p class="text-muted mb-0">Create a new dashboard navigation menu or parent group.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Menu Setup
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2 small my-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.menus.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-4">
                <!-- Left: Configuration Form -->
                <div class="col-12 col-lg-7">
                    <div class="panel p-4">
                        <h5 class="mb-3"><i class="bi bi-gear-wide-connected me-1 text-primary"></i> Menu Attributes</h5>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="title">Menu Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Purchase Orders, Live Chat" required>
                            <small class="text-muted">Displayed as the navigation label in the admin sidebar.</small>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="slug">Menu Slug (Unique ID)</label>
                            <input type="text" name="slug" id="slug" class="form-control font-monospace @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="e.g. purchase_orders (optional)">
                            <small class="text-muted">Optional. Unique system identifier. Leave blank to auto-generate from Title.</small>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="route_name">
                                <i class="bi bi-link-45deg me-1"></i> System Route Target
                            </label>
                            <input type="text" name="route_name" id="route_name" class="form-control font-monospace @error('route_name') is-invalid @enderror" value="{{ old('route_name') }}" placeholder="e.g. admin.products.index">
                            <small class="text-muted">Named Laravel route. Leave blank if this menu is a parent group with collapsible sub-items.</small>
                            @error('route_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold" for="parent_slug">
                                    <i class="bi bi-diagram-2 me-1 text-primary"></i> Parent Menu
                                </label>
                                <select name="parent_slug" id="parent_slug" class="form-select @error('parent_slug') is-invalid @enderror">
                                    <option value="">-- None (Top-Level Menu) --</option>
                                    @foreach($parentMenus as $parent)
                                        <option value="{{ $parent->slug }}" {{ old('parent_slug') === $parent->slug ? 'selected' : '' }}>
                                            {{ $parent->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select a parent to nest this as a sub-menu, or leave as Top-Level.</small>
                                @error('parent_slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold" for="sort_order">Sort Order <span class="text-danger">*</span></label>
                                <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $defaultSortOrder) }}" min="0" required>
                                <small class="text-muted">Lower numbers appear first.</small>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="icon">Bootstrap Icon Class <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi {{ old('icon', 'bi-circle') }}" id="iconPreview"></i></span>
                                <input type="text" name="icon" id="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', 'bi-circle') }}" required oninput="document.getElementById('iconPreview').className = 'bi ' + (this.value || 'bi-circle')">
                            </div>
                            <small class="text-muted">Examples: <code>bi-box-seam</code>, <code>bi-cart-check</code>, <code>bi-sliders</code>, <code>bi-shield-lock</code>, <code>bi-people-fill</code>.</small>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="view_permission">
                                <i class="bi bi-shield-check me-1"></i> View Permission
                            </label>
                            <input list="permissionsList" name="view_permission" id="view_permission" class="form-control font-monospace @error('view_permission') is-invalid @enderror" value="{{ old('view_permission') }}" placeholder="e.g. view products (optional)">
                            <datalist id="permissionsList">
                                @foreach($availablePermissions as $perm)
                                    <option value="{{ $perm->name }}">
                                @endforeach
                            </datalist>
                            <small class="text-muted">Optional permission name required to view this menu item in the navigation.</small>
                            @error('view_permission')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mt-4 p-3 border rounded bg-light-subtle">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">
                                Active in Sidebar Navigation
                            </label>
                            <small class="d-block text-muted ps-0">
                                When enabled, this menu item will be immediately visible to authorized users in the admin sidebar.
                            </small>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" id="createMenuSubmitBtn" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-plus-lg me-1"></i> Create Menu
                            </button>
                            <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right: Information & Guidelines -->
                <div class="col-12 col-lg-5">
                    <div class="panel p-4 mb-4">
                        <h5 class="mb-3"><i class="bi bi-info-circle me-1 text-primary"></i> Navigation Architecture Guide</h5>
                        
                        <div class="d-flex flex-column gap-3">
                            <div class="p-3 border rounded bg-light-subtle">
                                <h6 class="fw-bold mb-1"><i class="bi bi-folder2 text-primary me-1"></i> Parent Groups vs. Links</h6>
                                <p class="text-muted small mb-0">
                                    If you leave <strong>Parent Menu</strong> empty and do not provide a <strong>Route Target</strong>, this menu functions as a <strong>Parent Group</strong> container for nested child sub-menus.
                                </p>
                            </div>

                            <div class="p-3 border rounded bg-light-subtle">
                                <h6 class="fw-bold mb-1"><i class="bi bi-arrow-return-right text-primary me-1"></i> Nested Sub-Menus</h6>
                                <p class="text-muted small mb-0">
                                    Select an existing parent menu to nest this item as a sub-menu. In the admin sidebar, it will appear cleanly inside the parent's collapsible group.
                                </p>
                            </div>

                            <div class="p-3 border rounded bg-light-subtle">
                                <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock text-primary me-1"></i> Permissions &amp; Roles</h6>
                                <p class="text-muted small mb-0">
                                    Specifying a <strong>View Permission</strong> restricts visibility to staff users who have that Spatie permission assigned. Super Administrators and Administrators always have access.
                                </p>
                            </div>

                            <div class="p-3 border rounded bg-light-subtle">
                                <h6 class="fw-bold mb-1"><i class="bi bi-arrow-down-up text-primary me-1"></i> Sorting</h6>
                                <p class="text-muted small mb-0">
                                    Items are displayed in ascending order based on <strong>Sort Order</strong>. You can fine-tune sort orders anytime with inline controls on the main Menu Setup page.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form[action="{{ route('admin.menus.store') }}"]');
            const submitBtn = document.getElementById('createMenuSubmitBtn');
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');
            let userEditedSlug = Boolean(slugInput && slugInput.value.trim());

            if (slugInput) {
                slugInput.addEventListener('input', function () {
                    userEditedSlug = this.value.trim().length > 0;
                });
            }

            if (titleInput && slugInput) {
                titleInput.addEventListener('input', function () {
                    if (!userEditedSlug) {
                        slugInput.value = this.value
                            .toLowerCase()
                            .trim()
                            .replace(/[^a-z0-9]+/g, '_')
                            .replace(/^_+|_+$/g, '');
                    }
                });
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function (e) {
                    if (submitBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating Menu...';
                });
            }
        });
    </script>
</x-app-layout>

