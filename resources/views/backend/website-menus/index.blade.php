<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-menu-button-wide" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Website Setup &amp; Storefront CMS</p>
                    <h1 class="h3 mb-1">Storefront Website Menus</h1>
                    <p class="text-muted mb-0">Configure top utility links, primary navbar with parent &amp; child dropdowns, and footer navigation columns.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @can('edit website settings')
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createMenuModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Menu Item
                    </button>
                @endcan
                <a href="{{ route('admin.website-products.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-grid-3x3-gap-fill me-1"></i> Website Products
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

        <!-- Location Nav Tabs -->
        <div class="panel p-0 overflow-hidden mb-4 shadow-sm border mt-3">
            <ul class="nav nav-tabs nav-fill bg-light px-3 pt-3 border-bottom" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 py-3 {{ $location === 'main' ? 'active text-primary' : 'text-secondary' }}"
                       href="{{ route('admin.storefront-menus.index', ['location' => 'main']) }}">
                        <i class="bi bi-compass fs-5"></i>
                        <span>Main Navbar Menu</span>
                        <span class="badge bg-primary rounded-pill ms-1">{{ $counts['main'] }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 py-3 {{ $location === 'top' ? 'active text-primary' : 'text-secondary' }}"
                       href="{{ route('admin.storefront-menus.index', ['location' => 'top']) }}">
                        <i class="bi bi-layout-headline fs-5"></i>
                        <span>Top Utility Menu (Bar)</span>
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $counts['top'] }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link fw-semibold d-flex align-items-center justify-content-center gap-2 py-3 {{ $location === 'footer' ? 'active text-primary' : 'text-secondary' }}"
                       href="{{ route('admin.storefront-menus.index', ['location' => 'footer']) }}">
                        <i class="bi bi-layout-text-window-reverse fs-5"></i>
                        <span>Footer Columns Menu</span>
                        <span class="badge bg-secondary rounded-pill ms-1">{{ $counts['footer'] }}</span>
                    </a>
                </li>
            </ul>

            <div class="p-4 bg-white">
                <!-- Location Info Banner -->
                <div class="alert alert-light border d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fs-4 text-primary">
                            @if($location === 'main')
                                <i class="bi bi-diagram-3"></i>
                            @elseif($location === 'top')
                                <i class="bi bi-megaphone"></i>
                            @else
                                <i class="bi bi-grid-3x3"></i>
                            @endif
                        </span>
                        <div>
                            <strong class="d-block text-dark">
                                @if($location === 'main')
                                    Main Navigation Menu (Supports 2-Level Parent &amp; Child Dropdowns)
                                @elseif($location === 'top')
                                    Top Announcement Bar Menu (About Us, Contact Us, Track Order...)
                                @else
                                    Footer Columns Navigation (Parent headers with nested child links)
                                @endif
                            </strong>
                            <small class="text-muted">
                                @if($location === 'main')
                                    Parent menus with child links will render as elegant dropdown menus on desktop and collapsible accordions on mobile.
                                @elseif($location === 'top')
                                    Renders beside the promo announcement in the high-contrast top bar.
                                @else
                                    Top-level parent items form the footer column headers, and child items form the links in each column.
                                @endif
                            </small>
                        </div>
                    </div>
                    @can('edit website settings')
                        <button type="button" class="btn btn-sm btn-primary shrink-0" data-bs-toggle="modal" data-bs-target="#createMenuModal">
                            <i class="bi bi-plus-lg me-1"></i> Add {{ ucfirst($location) }} Item
                        </button>
                    @endcan
                </div>

                <!-- Menus Tree Table -->
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;" class="text-center">Order</th>
                                <th>Menu Item / Hierarchy</th>
                                <th>Target URL</th>
                                <th>Icon</th>
                                <th>Badge</th>
                                <th>Window</th>
                                <th>Status</th>
                                <th style="width: 160px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menus as $parent)
                                <!-- Parent Row -->
                                <tr class="table-group-divider bg-light-subtle">
                                    <td class="text-center font-monospace fw-bold text-secondary">{{ $parent->sort_order }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Parent</span>
                                            @if($parent->icon)
                                                <span class="text-secondary"><i class="bi {{ $parent->icon }}"></i></span>
                                            @endif
                                            <strong class="text-dark fs-6">{{ $parent->title }}</strong>
                                            @if($parent->allChildren->count() > 0)
                                                <span class="badge bg-light text-muted border rounded-pill small">
                                                    {{ $parent->allChildren->count() }} child {{ Str::plural('item', $parent->allChildren->count()) }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-primary small">{{ $parent->url }}</code>
                                    </td>
                                    <td>
                                        @if($parent->icon)
                                            <span class="text-muted small"><i class="bi {{ $parent->icon }} me-1"></i>{{ $parent->icon }}</span>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($parent->badge)
                                            <span class="badge bg-danger rounded-pill">{{ $parent->badge }}</span>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $parent->target === '_blank' ? 'New Tab' : 'Same' }}</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.storefront-menus.toggle-active', $parent) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Click to toggle status">
                                                @if($parent->is_active)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Active</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle"><i class="bi bi-x-circle me-1"></i>Disabled</span>
                                                @endif
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            @can('edit website settings')
                                                <!-- Add Child Button -->
                                                <button type="button"
                                                        class="btn btn-outline-primary"
                                                        title="Add Child Submenu"
                                                        onclick="openAddChildModal({{ $parent->id }}, '{{ addslashes($parent->title) }}')">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                                <!-- Edit Button -->
                                                <button type="button"
                                                        class="btn btn-outline-secondary"
                                                        title="Edit Item"
                                                        onclick="openEditModal({{ json_encode($parent) }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <!-- Delete Button -->
                                                <form action="{{ route('admin.storefront-menus.destroy', $parent) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu item? Any submenus under it will also be deleted.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                <!-- Children Rows -->
                                @foreach($parent->allChildren as $child)
                                    <tr>
                                        <td class="text-center font-monospace small text-muted ps-4">&boxur; {{ $child->sort_order }}</td>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-muted small">&boxur;</span>
                                                @if($child->icon)
                                                    <span class="text-secondary small"><i class="bi {{ $child->icon }}"></i></span>
                                                @endif
                                                <span class="text-dark fw-semibold">{{ $child->title }}</span>
                                                <small class="text-muted">(under {{ $parent->title }})</small>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-muted small">{{ $child->url }}</code>
                                        </td>
                                        <td>
                                            @if($child->icon)
                                                <span class="text-muted small"><i class="bi {{ $child->icon }} me-1"></i>{{ $child->icon }}</span>
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($child->badge)
                                                <span class="badge bg-warning text-dark rounded-pill">{{ $child->badge }}</span>
                                            @else
                                                <span class="text-muted small">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $child->target === '_blank' ? 'New Tab' : 'Same' }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.storefront-menus.toggle-active', $child) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Click to toggle status">
                                                    @if($child->is_active)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>Active</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-muted border border-secondary-subtle"><i class="bi bi-x-circle me-1"></i>Disabled</span>
                                                    @endif
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                @can('edit website settings')
                                                    <button type="button"
                                                            class="btn btn-outline-secondary"
                                                            title="Edit Item"
                                                            onclick="openEditModal({{ json_encode($child) }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('admin.storefront-menus.destroy', $child) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu item?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        No menu items configured for {{ ucfirst($location) }} Menu yet.
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createMenuModal">
                                                <i class="bi bi-plus-circle me-1"></i> Create First Menu Item
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CREATE MENU MODAL -->
    <div class="modal fade" id="createMenuModal" tabindex="-1" aria-labelledby="createMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.storefront-menus.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="createMenuModalLabel">
                            <i class="bi bi-plus-circle-fill text-primary me-2"></i>Add Storefront Menu Item
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_location" class="form-label fw-semibold small text-secondary">Menu Location</label>
                            <select class="form-select" id="create_location" name="location" required>
                                <option value="main" {{ $location === 'main' ? 'selected' : '' }}>Main Navbar Menu</option>
                                <option value="top" {{ $location === 'top' ? 'selected' : '' }}>Top Utility Bar Menu</option>
                                <option value="footer" {{ $location === 'footer' ? 'selected' : '' }}>Footer Columns Menu</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="create_parent_id" class="form-label fw-semibold small text-secondary">Parent Menu (Optional)</label>
                            <select class="form-select" id="create_parent_id" name="parent_id">
                                <option value="">None (Top-Level Parent Menu)</option>
                                @foreach($parentOptions as $pOpt)
                                    <option value="{{ $pOpt->id }}">{{ $pOpt->title }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose a parent to make this item a dropdown submenu link.</div>
                        </div>

                        <div class="mb-3">
                            <label for="create_title" class="form-label fw-semibold small text-secondary">Menu Label / Title</label>
                            <input type="text" class="form-control" id="create_title" name="title" required placeholder="e.g. Graphics Cards (GPUs)">
                        </div>

                        <div class="mb-3">
                            <label for="create_url" class="form-label fw-semibold small text-secondary">Target URL / Path</label>
                            <input type="text" class="form-control" id="create_url" name="url" required placeholder="e.g. /shop, /about-us, or https://...">
                            <div class="form-text">Enter internal relative path (e.g. <code>/shop</code>) or full URL.</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="create_icon" class="form-label fw-semibold small text-secondary">Icon Class / Emoji</label>
                                <input type="text" class="form-control" id="create_icon" name="icon" placeholder="e.g. bi-cpu or ⚡">
                            </div>
                            <div class="col-6">
                                <label for="create_badge" class="form-label fw-semibold small text-secondary">Badge Tag (Optional)</label>
                                <input type="text" class="form-control" id="create_badge" name="badge" placeholder="e.g. HOT, NEW, SALE">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="create_target" class="form-label fw-semibold small text-secondary">Window Target</label>
                                <select class="form-select" id="create_target" name="target">
                                    <option value="_self">Same Tab (_self)</option>
                                    <option value="_blank">New Tab (_blank)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="create_sort_order" class="form-label fw-semibold small text-secondary">Sort Order</label>
                                <input type="number" class="form-control" id="create_sort_order" name="sort_order" value="0" min="0">
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="create_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold" for="create_is_active">Active &amp; Visible on Storefront</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Menu Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT MENU MODAL -->
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editMenuForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="editMenuModalLabel">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Edit Storefront Menu Item
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_parent_id" class="form-label fw-semibold small text-secondary">Parent Menu</label>
                            <select class="form-select" id="edit_parent_id" name="parent_id">
                                <option value="">None (Top-Level Parent Menu)</option>
                                @foreach($parentOptions as $pOpt)
                                    <option value="{{ $pOpt->id }}">{{ $pOpt->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="edit_title" class="form-label fw-semibold small text-secondary">Menu Label / Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="edit_url" class="form-label fw-semibold small text-secondary">Target URL / Path</label>
                            <input type="text" class="form-control" id="edit_url" name="url" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="edit_icon" class="form-label fw-semibold small text-secondary">Icon Class / Emoji</label>
                                <input type="text" class="form-control" id="edit_icon" name="icon">
                            </div>
                            <div class="col-6">
                                <label for="edit_badge" class="form-label fw-semibold small text-secondary">Badge Tag</label>
                                <input type="text" class="form-control" id="edit_badge" name="badge">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="edit_target" class="form-label fw-semibold small text-secondary">Window Target</label>
                                <select class="form-select" id="edit_target" name="target">
                                    <option value="_self">Same Tab (_self)</option>
                                    <option value="_blank">New Tab (_blank)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="edit_sort_order" class="form-label fw-semibold small text-secondary">Sort Order</label>
                                <input type="number" class="form-control" id="edit_sort_order" name="sort_order" min="0">
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label fw-semibold" for="edit_is_active">Active &amp; Visible on Storefront</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i> Update Menu Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddChildModal(parentId, parentTitle) {
            const modalEl = document.getElementById('createMenuModal');
            const parentSelect = document.getElementById('create_parent_id');
            if (parentSelect) {
                parentSelect.value = parentId;
            }
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        function openEditModal(item) {
            const form = document.getElementById('editMenuForm');
            form.action = `/admin/storefront-menus/${item.id}`;

            document.getElementById('edit_title').value = item.title || '';
            document.getElementById('edit_url').value = item.url || '';
            document.getElementById('edit_icon').value = item.icon || '';
            document.getElementById('edit_badge').value = item.badge || '';
            document.getElementById('edit_target').value = item.target || '_self';
            document.getElementById('edit_sort_order').value = item.sort_order || 0;
            document.getElementById('edit_is_active').checked = !!item.is_active;

            const parentSelect = document.getElementById('edit_parent_id');
            if (parentSelect) {
                parentSelect.value = item.parent_id || '';
                // Disable selecting oneself
                Array.from(parentSelect.options).forEach(opt => {
                    opt.disabled = (parseInt(opt.value) === item.id);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('editMenuModal'));
            modal.show();
        }
    </script>
</x-app-layout>

