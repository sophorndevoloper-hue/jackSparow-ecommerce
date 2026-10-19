<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Breadcrumb & Heading -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.menus.index') }}">Menu Setup</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit {{ $menu->title }}</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-1">Edit Menu: {{ $menu->title }}</h1>
                    <p class="text-muted mb-0">Adjust navigation label, parent menu, icon, sort order, and visibility.</p>
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

        <form action="{{ route('admin.menus.update', $menu->id) }}" method="POST" class="mt-3">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <!-- Left: Configuration Form -->
                <div class="col-12 col-lg-7">
                    <div class="panel p-4">
                        <h5 class="mb-3"><i class="bi bi-gear-wide-connected me-1 text-primary"></i> Menu Attributes</h5>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="title">Menu Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $menu->title) }}" required>
                            <small class="text-muted">Displayed as the navigation label in the admin sidebar.</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small fw-bold mb-0" for="route_name">
                                    <i class="bi bi-link-45deg me-1 text-primary"></i> System Route Target
                                </label>
                                @if(! empty($menu->route_name))
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0 font-monospace text-decoration-none" style="font-size: 11.5px;" onclick="document.getElementById('route_name').value = '';">
                                        <i class="bi bi-x-circle me-1"></i> Clear Route
                                    </button>
                                @endif
                            </div>
                            <div class="input-group">
                                <span class="input-group-text font-monospace small text-muted"><i class="bi bi-compass"></i></span>
                                <input type="text" 
                                       name="route_name" 
                                       id="route_name" 
                                       class="form-control font-monospace @error('route_name') is-invalid @enderror" 
                                       value="{{ old('route_name', $menu->route_name) }}" 
                                       placeholder="e.g. admin.products.index (leave empty to remove route)">
                            </div>
                            <small class="text-muted">Named Laravel route. Clear this route if you wish to allow deleting this menu item.</small>
                            @error('route_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="view_permission">
                                <i class="bi bi-shield-check me-1 text-primary"></i> View Permission
                            </label>
                            <input list="permissionsList" name="view_permission" id="view_permission" class="form-control font-monospace" value="{{ old('view_permission', $menu->view_permission) }}" placeholder="e.g. view orders (optional)">
                            <datalist id="permissionsList">
                                @foreach($availablePermissions as $perm)
                                    <option value="{{ $perm->name }}">
                                @endforeach
                            </datalist>
                            <small class="text-muted">Optional permission name required to view this menu item.</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold" for="parent_slug">
                                    <i class="bi bi-diagram-2 me-1 text-primary"></i> Parent Menu
                                </label>
                                @php
                                    $hasChildren = \App\Models\AdminMenu::where('parent_slug', $menu->slug)->exists();
                                @endphp
                                <select name="parent_slug" id="parent_slug" class="form-select @error('parent_slug') is-invalid @enderror" {{ $hasChildren ? 'disabled' : '' }}>
                                    <option value="">-- None (Top-Level Menu) --</option>
                                    @foreach($parentMenus as $parent)
                                        <option value="{{ $parent->slug }}" {{ old('parent_slug', $menu->parent_slug) === $parent->slug ? 'selected' : '' }}>
                                            {{ $parent->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($hasChildren)
                                    <input type="hidden" name="parent_slug" value="">
                                    <small class="text-warning d-block mt-1">
                                        <i class="bi bi-info-circle me-1"></i> This menu is currently a parent group with sub-items.
                                    </small>
                                @else
                                    <small class="text-muted">Select a parent menu to nest this item, or choose Top-Level.</small>
                                @endif
                                @error('parent_slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold" for="sort_order">Sort Order <span class="text-danger">*</span></label>
                                <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $menu->sort_order) }}" min="0" required>
                                <small class="text-muted">Lower numbers appear first.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold" for="icon">Bootstrap Icon Class <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi {{ $menu->icon }}" id="iconPreview"></i></span>
                                <input type="text" name="icon" id="icon" class="form-control" value="{{ old('icon', $menu->icon) }}" required oninput="document.getElementById('iconPreview').className = 'bi ' + this.value">
                            </div>
                            <small class="text-muted">Examples: <code>bi-cpu</code>, <code>bi-grid-3x3-gap</code>, <code>bi-patch-check</code>, <code>bi-cart-check</code>, <code>bi-people-fill</code>, <code>bi-sliders</code>.</small>
                        </div>

                        <div class="form-check form-switch mt-4 p-3 border rounded bg-light-subtle">
                            <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" name="is_active" id="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">
                                Active in Sidebar Navigation
                            </label>
                            <small class="d-block text-muted ps-0">
                                When disabled, this menu is instantly hidden from the sidebar for all users.
                            </small>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Save Changes
                                </button>
                                <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                            @if(! \App\Models\AdminMenu::isProtected($menu))
                                <button type="button" class="btn btn-outline-danger" onclick="if(confirm('Are you sure you want to delete menu \'{{ $menu->title }}\'?')) { document.getElementById('delete-menu-form').submit(); }">
                                    <i class="bi bi-trash me-1"></i> Delete Menu
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right: Associated Actions & Capabilities -->
                <div class="col-12 col-lg-5">
                    <div class="panel p-4 mb-4">
                        <h5 class="mb-3"><i class="bi bi-diagram-3 me-1 text-primary"></i> Associated Action Controls</h5>
                        <p class="text-muted small">Granular actions and permissions configured under this menu:</p>

                        <div class="d-flex flex-column gap-3">
                            @forelse($menu->actions ?? [] as $action)
                                @php
                                    $type = $action['action_type'] ?? '';
                                    $badgeColor = match($type) {
                                        'view' => 'primary',
                                        'create' => 'success',
                                        'edit' => 'warning',
                                        'delete' => 'danger',
                                        default => 'info'
                                    };
                                    $iconClass = match($type) {
                                        'view' => 'bi-eye',
                                        'create' => 'bi-plus-circle',
                                        'edit' => 'bi-pencil',
                                        'delete' => 'bi-trash',
                                        default => 'bi-check-circle'
                                    };
                                @endphp
                                <div class="p-3 border rounded bg-light-subtle">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="fw-bold"><i class="bi {{ $iconClass }} text-{{ $badgeColor }} me-1"></i> {{ $action['name'] }}</span>
                                        <span class="badge bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }}-subtle font-monospace" style="font-size: 11px;">
                                            {{ $action['permission'] ?? '' }}
                                        </span>
                                    </div>
                                    @if(!empty($action['route_name']))
                                        <div class="small text-muted font-monospace mb-1">
                                            <i class="bi bi-link-45deg me-1"></i><code>{{ $action['route_name'] }}</code>
                                        </div>
                                    @endif
                                    <p class="text-muted small mb-0">{{ $action['description'] ?? 'Granular system capability.' }}</p>
                                </div>
                            @empty
                                <div class="alert alert-secondary small mb-0">
                                    No granular actions configured for this menu item.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if(! $menu->isParentDropdown() && ! empty($menu->route_name))
                        <div class="panel p-4 bg-light-subtle">
                            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle text-primary me-1"></i> Route Target</h6>
                            <p class="small text-muted mb-2">This menu points to the named Laravel route:</p>
                            <code class="d-block p-2 bg-body border rounded font-monospace small mb-3">{{ $menu->route_name }}</code>
                            <small class="text-muted">To manage user role permissions for these actions, visit <a href="{{ route('admin.users.index') }}" class="text-primary fw-bold">Admin Users</a> or <a href="{{ route('admin.roles.index') }}" class="text-primary fw-bold">Roles</a>.</small>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        @if(! \App\Models\AdminMenu::isProtected($menu))
            <form id="delete-menu-form" action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</x-app-layout>
