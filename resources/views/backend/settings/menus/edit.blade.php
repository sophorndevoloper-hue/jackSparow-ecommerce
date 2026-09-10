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
                    <p class="text-muted mb-0">Adjust navigation label, section category, icon, sort order, and visibility.</p>
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

                        <input type="hidden" name="route_name" value="{{ $menu->route_name }}">
                        <input type="hidden" name="view_permission" value="{{ $menu->view_permission }}">

                        @if(! $menu->isParentDropdown() && ! empty($menu->route_name))
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted"><i class="bi bi-link-45deg me-1"></i> System Route Target</label>
                                <div class="p-2 bg-light-subtle border rounded font-monospace small text-primary d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-lock-fill text-muted me-2"></i>{{ $menu->route_name }}</span>
                                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 10px;">Fixed System Route</span>
                                </div>
                                <small class="text-muted">Managed by system routing.</small>
                            </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold" for="section">Section Category <span class="text-danger">*</span></label>
                                <select name="section" id="section" class="form-select" required>
                                    <option value="Main" {{ old('section', $menu->section) === 'Main' ? 'selected' : '' }}>Main</option>
                                    <option value="Inventory" {{ old('section', $menu->section) === 'Inventory' ? 'selected' : '' }}>Inventory</option>
                                    <option value="Sales & Fulfillment" {{ old('section', $menu->section) === 'Sales & Fulfillment' ? 'selected' : '' }}>Sales & Fulfillment</option>
                                    <option value="People" {{ old('section', $menu->section) === 'People' ? 'selected' : '' }}>People</option>
                                    <option value="System Access" {{ old('section', $menu->section) === 'System Access' ? 'selected' : '' }}>System Access</option>
                                </select>
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

                        <div class="mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                            <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
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
    </div>
</x-app-layout>
