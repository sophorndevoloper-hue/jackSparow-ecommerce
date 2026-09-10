<x-app-layout>
    <style>
        .menu-action-chip {
            background-color: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 8px;
            padding: 6px 10px;
            display: inline-flex;
            flex-direction: column;
            gap: 4px;
            text-align: left;
            min-width: 140px;
            transition: all 0.15s ease;
        }
        .menu-action-chip .action-title {
            font-weight: 600;
            font-size: 11.5px;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .menu-action-chip .action-perm {
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 10px;
            color: #2563eb;
            background-color: rgba(37, 99, 235, 0.08);
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid rgba(37, 99, 235, 0.2);
            display: inline-block;
            width: fit-content;
        }
        html[data-theme="dark"] .menu-action-chip {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html[data-theme="dark"] .menu-action-chip .action-title {
            color: #f8fafc !important;
        }
        html[data-theme="dark"] .menu-action-chip .action-perm {
            color: #93c5fd !important;
            background-color: rgba(37, 99, 235, 0.2) !important;
            border-color: rgba(96, 165, 250, 0.3) !important;
        }

        /* Modern Parent Row & Collapse Styles */
        .parent-menu-row {
            transition: background-color 0.25s ease, border-color 0.25s ease, opacity 0.25s ease;
        }
        .parent-menu-row:hover {
            background-color: rgba(37, 99, 235, 0.04) !important;
        }
        .parent-menu-row.is-open {
            background-color: rgba(37, 99, 235, 0.05) !important;
            border-left: 3px solid #2563eb !important;
        }
        html[data-theme="dark"] .parent-menu-row:hover {
            background-color: rgba(59, 130, 246, 0.08) !important;
        }
        html[data-theme="dark"] .parent-menu-row.is-open {
            background-color: rgba(59, 130, 246, 0.1) !important;
            border-left: 3px solid #3b82f6 !important;
        }
        .dropdown-toggle-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .dropdown-toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.18) !important;
        }
        .parent-chevron {
            display: inline-block;
            transition: transform 0.32s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .parent-menu-row.is-open .parent-chevron,
        .dropdown-toggle-btn[aria-expanded="true"] .parent-chevron {
            transform: rotate(90deg);
        }
        
        /* Smooth Collapse Animation */
        .collapse {
            transition: height 0.32s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .collapsing {
            transition: height 0.32s cubic-bezier(0.4, 0, 0.2, 1) !important;
            overflow: hidden;
        }
        .child-panel-container {
            background-color: rgba(248, 250, 252, 0.85);
            border-left: 3px solid #3b82f6;
            border-radius: 0 10px 10px 0;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.03), 0 4px 12px rgba(0, 0, 0, 0.03);
            transform-origin: top center;
            transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, opacity;
        }
        .collapse:not(.show):not(.collapsing) .child-panel-container {
            opacity: 0;
            transform: translateY(-6px);
        }
        .collapsing .child-panel-container {
            opacity: 0.8;
            transform: translateY(-3px);
        }
        .collapse.show .child-panel-container {
            opacity: 1;
            transform: translateY(0);
        }
        html[data-theme="dark"] .child-panel-container {
            background-color: rgba(15, 23, 42, 0.6);
            border-left-color: #60a5fa;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.2), 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-sliders" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">System Access &amp; Controls</p>
                    <h1 class="h3 mb-1">Menu &amp; Action Setup</h1>
                    <p class="text-muted mb-0">Configure dashboard menus, toggle navigation visibility, and inspect required action permissions.</p>
                </div>
            </div>
            <div class="heading-actions d-flex flex-wrap align-items-center gap-2">
                @can('edit settings')
                    <form action="{{ route('admin.menus.sync') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm shadow-sm" title="Auto-discover and synchronize all codebase menus, actions, and Spatie permissions">
                            <i class="bi bi-arrow-repeat me-1"></i> Sync Menus &amp; Permissions
                        </button>
                    </form>
                @endcan
                <form action="{{ route('admin.cache.clear') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-stars me-1"></i> Clear Cache
                    </button>
                </form>
                @can('view roles')
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-shield-lock me-1"></i> System Roles
                    </a>
                @endcan
                @can('view users')
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-people me-1"></i> Admin Users
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

        <!-- Summary KPI Cards -->
        <div class="row g-3 my-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-menu-app"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Menus</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totalMenus }}</div>
                        <small class="text-muted">Registered in database</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-success-subtle text-success p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Active For You</div>
                        <div class="fs-4 fw-bold text-success" id="active-count-kpi">{{ $activeMenus }}</div>
                        <small class="text-muted">Visible in your navigation</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-secondary-subtle text-secondary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-slash-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Hidden For You</div>
                        <div class="fs-4 fw-bold text-muted" id="disabled-count-kpi">{{ $disabledMenus }}</div>
                        <small class="text-muted">Hidden in this session</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 h-100 d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-info-subtle text-info p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Action Controls</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totalActions }}</div>
                        <small class="text-muted">Configured capabilities</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Access Coverage Guide -->
        <div class="panel p-3 mb-4 bg-light-subtle">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-primary fs-4"></i>
                    <div>
                        <strong class="d-block text-dark">Backend Role Coverage &amp; Action Mapping</strong>
                        <small class="text-muted">Every action defined below is dynamically secured through Spatie role permissions.</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                            <i class="bi bi-person-badge text-primary me-1"></i> {{ $role->name }}: {{ $role->permissions->count() }} perms
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Menu Section Accordions & Cards -->
        <div class="space-y-4">
            @foreach($groupedMenus as $sectionName => $sectionMenus)
                @php
                    $topLevelMenus = $sectionMenus->filter(fn ($m) => empty($m->parent_slug));
                @endphp
                <div class="panel mb-4 shadow-sm border">
                    <div class="p-3 border-bottom bg-light-subtle d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-folder2 text-primary fs-5"></i>
                            <h5 class="mb-0 fw-bold text-dark">{{ $sectionName }} Section</h5>
                        </div>
                        <span class="badge bg-secondary font-monospace">{{ $sectionMenus->count() }} {{ str('item')->plural($sectionMenus->count()) }}</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 110px;"><i class="bi bi-arrow-down-up me-1"></i> Sort</th>
                                    <th>Menu Title &amp; Route</th>
                                    <th>View Permission</th>
                                    <th>Actions &amp; Permission Mapping</th>
                                    <th>Status</th>
                                    <th class="text-end" style="min-width: 190px;">Controls</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topLevelMenus as $menu)
                                    @php
                                        $childMenus = $sectionMenus->filter(fn ($m) => $m->parent_slug === $menu->slug);
                                        $hasChildren = $childMenus->isNotEmpty();
                                        $isParentActive = $menu->is_active_for_session ?? \App\Models\AdminMenu::isMenuActiveForSession($menu);
                                    @endphp

                                    <!-- Top-Level Menu Row -->
                                    <tr class="parent-menu-row {{ ! $isParentActive ? 'opacity-75 bg-light' : '' }}" id="parent-row-{{ $menu->slug }}">
                                        <td>
                                            <form action="{{ route('admin.menus.sort', $menu->id) }}" method="POST" class="d-flex align-items-center gap-1 sort-menu-form" data-menu-id="{{ $menu->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number" 
                                                       name="sort_order" 
                                                       value="{{ $menu->sort_order }}" 
                                                       min="0" 
                                                       max="9999" 
                                                       class="form-control form-control-sm text-center font-monospace px-1 py-0 sort-input" 
                                                       style="width: 58px; height: 28px; font-size: 12px;" 
                                                       title="Sort order number for {{ $menu->title }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2 sort-save-btn" style="height: 28px;" title="Save sort order">
                                                    <i class="bi bi-check2"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded bg-light border p-2 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi {{ $menu->icon }} fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <strong class="text-dark">{{ $menu->title }}</strong>
                                                        @if($hasChildren)
                                                            <button type="button" 
                                                                    class="btn btn-sm btn-outline-primary dropdown-toggle-btn py-0 px-2 font-monospace shadow-none" 
                                                                    style="font-size: 11px;"
                                                                    data-bs-toggle="collapse" 
                                                                    data-bs-target="#collapse-{{ $menu->slug }}" 
                                                                    aria-expanded="false" 
                                                                    title="Click to expand or collapse {{ $menu->title }} sub-items">
                                                                <i class="bi bi-chevron-right me-1 parent-chevron"></i>Dropdown ({{ $childMenus->count() }} items)
                                                            </button>
                                                        @endif
                                                    </div>
                                                    @if($menu->route_name)
                                                        <code class="text-muted small font-monospace">{{ $menu->route_name }}</code>
                                                    @else
                                                        <span class="text-muted small">Collapsible dropdown container</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($menu->view_permission)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">
                                                    <i class="bi bi-eye me-1"></i> {{ $menu->view_permission }}
                                                </span>
                                            @elseif($hasChildren)
                                                <span class="text-muted small">Inherited from sub-items</span>
                                            @else
                                                <span class="text-muted small">Public (All authenticated)</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-2" style="max-width: 540px;">
                                                @forelse($menu->actions ?? [] as $action)
                                                    @php
                                                        $permName = $action['permission'] ?? '';
                                                        $type = $action['action_type'] ?? '';
                                                        $badgeColor = match($type) {
                                                            'view' => 'primary',
                                                            'create' => 'success',
                                                            'edit' => 'warning',
                                                            'delete' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                        $iconClass = match($type) {
                                                            'view' => 'bi-eye',
                                                            'create' => 'bi-plus-circle',
                                                            'edit' => 'bi-pencil',
                                                            'delete' => 'bi-trash',
                                                            default => 'bi-check-circle-fill text-success'
                                                        };
                                                    @endphp
                                                    <div class="menu-action-chip">
                                                        <span class="action-title">
                                                            <i class="bi {{ $iconClass }} text-{{ $badgeColor }}" style="font-size: 11px;"></i>
                                                            {{ $action['name'] }}
                                                        </span>
                                                        <span class="action-perm">{{ $permName }}</span>
                                                    </div>
                                                @empty
                                                    @if($hasChildren)
                                                        <span class="text-muted small"><i class="bi bi-diagram-2 me-1"></i>Pure dropdown parent (actions on sub-items)</span>
                                                    @else
                                                        <span class="text-muted small">No child actions</span>
                                                    @endif
                                                @endforelse
                                            </div>
                                        </td>
                                        <td id="status-cell-{{ $menu->id }}">
                                            @if($isParentActive)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                                    <i class="bi bi-eye-slash me-1"></i> Hidden (You)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Menu Attributes & Order">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <!-- Async Toggle Button (Hides parent & all children without full reload) -->
                                            <form action="{{ route('admin.menus.toggle-active', $menu->id) }}" 
                                                  method="POST" 
                                                  class="d-inline toggle-menu-form"
                                                  data-menu-id="{{ $menu->id }}"
                                                  data-is-parent="{{ $hasChildren ? '1' : '0' }}"
                                                  data-slug="{{ $menu->slug }}">
                                                @csrf
                                                @method('PATCH')
                                                @if($isParentActive)
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Hide this menu (and all its sub-items) from your sidebar navigation">
                                                        <i class="bi bi-eye-slash"></i> Hide
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Show this menu (and its sub-items) in your sidebar navigation">
                                                        <i class="bi bi-eye"></i> Show
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Collapsible Nested Children Container -->
                                    @if($hasChildren)
                                        <tr class="p-0 border-0">
                                            <td colspan="6" class="p-0 border-0">
                                                <div class="collapse" id="collapse-{{ $menu->slug }}">
                                                    <div class="child-panel-container my-2 ms-4 me-3 p-3">
                                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                                            <span class="small fw-bold text-primary text-uppercase font-monospace">
                                                                <i class="bi bi-diagram-3 me-1"></i> {{ $menu->title }} Sub-Items ({{ $childMenus->count() }})
                                                            </span>
                                                            <small class="text-muted">Sub-menu navigation items under {{ $menu->title }}</small>
                                                        </div>

                                                        <div class="table-responsive">
                                                             <table class="table table-sm align-middle mb-0">
                                                                <thead>
                                                                    <tr class="text-muted small">
                                                                        <th style="width: 105px;"><i class="bi bi-arrow-down-up me-1"></i> Sort</th>
                                                                        <th>Sub-Item Title &amp; Route</th>
                                                                        <th>Permission</th>
                                                                        <th>Actions</th>
                                                                        <th>Status</th>
                                                                        <th class="text-end">Controls</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($childMenus as $child)
                                                                        @php
                                                                            $isChildActive = $isParentActive && ($child->is_active_for_session ?? \App\Models\AdminMenu::isMenuActiveForSession($child));
                                                                        @endphp
                                                                        <tr class="{{ ! $isChildActive ? 'opacity-75 bg-light' : '' }}" id="child-row-{{ $child->id }}">
                                                                            <td>
                                                                                <form action="{{ route('admin.menus.sort', $child->id) }}" method="POST" class="d-flex align-items-center gap-1 sort-menu-form" data-menu-id="{{ $child->id }}">
                                                                                    @csrf
                                                                                    @method('PATCH')
                                                                                    <input type="number" 
                                                                                           name="sort_order" 
                                                                                           value="{{ $child->sort_order }}" 
                                                                                           min="0" 
                                                                                           max="9999" 
                                                                                           class="form-control form-control-sm text-center font-monospace px-1 py-0 sort-input" 
                                                                                           style="width: 52px; height: 24px; font-size: 11px;" 
                                                                                           title="Sort order number for {{ $child->title }}">
                                                                                    <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-1 sort-save-btn" style="height: 24px; font-size: 11px;" title="Save sort order">
                                                                                        <i class="bi bi-check2"></i>
                                                                                    </button>
                                                                                </form>
                                                                            </td>
                                                                            <td>
                                                                                <div class="d-flex align-items-center gap-2">
                                                                                    <div class="rounded bg-light border p-1 text-primary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                                                                        <i class="bi {{ $child->icon }}" style="font-size: 13px;"></i>
                                                                                    </div>
                                                                                    <div>
                                                                                        <strong class="text-dark d-block" style="font-size: 13px;">{{ $child->title }}</strong>
                                                                                        <code class="text-muted font-monospace" style="font-size: 11px;">{{ $child->route_name }}</code>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                @if($child->view_permission)
                                                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 10.5px;">
                                                                                        <i class="bi bi-eye me-1"></i> {{ $child->view_permission }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-muted small">Public</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                <div class="d-flex flex-wrap gap-1" style="max-width: 420px;">
                                                                                    @forelse($child->actions ?? [] as $action)
                                                                                        @php
                                                                                            $type = $action['action_type'] ?? '';
                                                                                            $badgeColor = match($type) {
                                                                                                'view' => 'primary',
                                                                                                'create' => 'success',
                                                                                                'edit' => 'warning',
                                                                                                'delete' => 'danger',
                                                                                                default => 'secondary'
                                                                                            };
                                                                                            $iconClass = match($type) {
                                                                                                'view' => 'bi-eye',
                                                                                                'create' => 'bi-plus-circle',
                                                                                                'edit' => 'bi-pencil',
                                                                                                'delete' => 'bi-trash',
                                                                                                default => 'bi-check'
                                                                                            };
                                                                                        @endphp
                                                                                        <span class="badge bg-{{ $badgeColor }}-subtle text-{{ $badgeColor }} border border-{{ $badgeColor }}-subtle font-monospace py-1 px-2" style="font-size: 10px;">
                                                                                            <i class="bi {{ $iconClass }} me-1"></i>{{ $action['name'] }}
                                                                                        </span>
                                                                                    @empty
                                                                                        <span class="text-muted small">—</span>
                                                                                    @endforelse
                                                                                </div>
                                                                            </td>
                                                                            <td id="status-cell-{{ $child->id }}">
                                                                                @if(! $isParentActive)
                                                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 10px;">
                                                                                        <i class="bi bi-eye-slash me-1"></i> Hidden (Parent)
                                                                                    </span>
                                                                                @elseif($isChildActive)
                                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">
                                                                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 10px;">
                                                                                        <i class="bi bi-eye-slash me-1"></i> Hidden (You)
                                                                                    </span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-end">
                                                                                <a href="{{ route('admin.menus.edit', $child->id) }}" class="btn btn-outline-primary py-0 px-2 me-1" style="font-size: 11px;" title="Edit Sub-item">
                                                                                    <i class="bi bi-pencil"></i> Edit
                                                                                </a>
                                                                                <form action="{{ route('admin.menus.toggle-active', $child->id) }}" 
                                                                                      method="POST" 
                                                                                      class="d-inline toggle-menu-form"
                                                                                      data-menu-id="{{ $child->id }}"
                                                                                      data-is-parent="0"
                                                                                      data-parent-slug="{{ $menu->slug }}">
                                                                                    @csrf
                                                                                    @method('PATCH')
                                                                                    @if($isChildActive)
                                                                                        <button type="submit" class="btn btn-outline-warning py-0 px-2" style="font-size: 11px;" title="Hide this sub-item from your navigation">
                                                                                            <i class="bi bi-eye-slash"></i> Hide
                                                                                        </button>
                                                                                    @else
                                                                                        <button type="submit" class="btn btn-outline-success py-0 px-2" style="font-size: 11px;" title="Show this sub-item in your navigation">
                                                                                            <i class="bi bi-eye"></i> Show
                                                                                        </button>
                                                                                    @endif
                                                                                </form>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Listen for Bootstrap collapse events to update parent active state and rotate chevron
            document.querySelectorAll('.collapse').forEach(function (collapseEl) {
                const targetId = collapseEl.getAttribute('id');
                if (!targetId || !targetId.startsWith('collapse-')) return;

                const parentSlug = targetId.replace('collapse-', '');
                const parentRow = document.getElementById('parent-row-' + parentSlug);

                collapseEl.addEventListener('show.bs.collapse', function () {
                    if (parentRow) parentRow.classList.add('is-open');
                });

                collapseEl.addEventListener('hide.bs.collapse', function () {
                    if (parentRow) parentRow.classList.remove('is-open');
                });
            });

            // Asynchronous instant toggle for Show / Hide without page reload
            document.addEventListener('submit', function (e) {
                const form = e.target.closest('.toggle-menu-form');
                if (!form) return;

                e.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                if (!button) return;

                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px;" role="status" aria-hidden="true"></span>';

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const menuId = data.menu_id;
                        const isActive = data.is_active;

                        // Update Top KPI numbers
                        const activeKpi = document.getElementById('active-count-kpi');
                        const disabledKpi = document.getElementById('disabled-count-kpi');
                        if (activeKpi && data.active_count !== undefined) activeKpi.textContent = data.active_count;
                        if (disabledKpi && data.disabled_count !== undefined) disabledKpi.textContent = data.disabled_count;

                        // Update Triggering Button
                        if (isActive) {
                            button.className = button.className.replace('btn-outline-success', 'btn-outline-warning');
                            button.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
                            if (button.title) button.title = button.title.replace('Show', 'Hide');
                        } else {
                            button.className = button.className.replace('btn-outline-warning', 'btn-outline-success');
                            button.innerHTML = '<i class="bi bi-eye"></i> Show';
                            if (button.title) button.title = button.title.replace('Hide', 'Show');
                        }
                        button.disabled = false;

                        // Update Status Badge for this menu item
                        const statusCell = document.getElementById('status-cell-' + menuId);
                        if (statusCell) {
                            if (data.is_parent) {
                                if (isActive) {
                                    statusCell.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i> Active</span>';
                                } else {
                                    statusCell.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><i class="bi bi-eye-slash me-1"></i> Hidden (You)</span>';
                                }
                            } else {
                                if (isActive) {
                                    statusCell.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;"><i class="bi bi-check-circle-fill me-1"></i> Active</span>';
                                } else {
                                    statusCell.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 10px;"><i class="bi bi-eye-slash me-1"></i> Hidden (You)</span>';
                                }
                            }
                        }

                        // Update Row Opacity
                        const parentRow = document.getElementById('parent-row-' + data.menu_slug);
                        if (parentRow) {
                            if (isActive) parentRow.classList.remove('opacity-75', 'bg-light');
                            else parentRow.classList.add('opacity-75', 'bg-light');
                        }

                        const childRow = document.getElementById('child-row-' + menuId);
                        if (childRow) {
                            if (isActive) childRow.classList.remove('opacity-75', 'bg-light');
                            else childRow.classList.add('opacity-75', 'bg-light');
                        }

                        // If it was a parent menu, cascade updates to all child sub-items
                        if (data.is_parent && data.child_ids) {
                            data.child_ids.forEach(childId => {
                                const childStatusCell = document.getElementById('status-cell-' + childId);
                                if (childStatusCell) {
                                    if (isActive) {
                                        childStatusCell.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;"><i class="bi bi-check-circle-fill me-1"></i> Active</span>';
                                    } else {
                                        childStatusCell.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 10px;"><i class="bi bi-eye-slash me-1"></i> Hidden (Parent)</span>';
                                    }
                                }
                                const cRow = document.getElementById('child-row-' + childId);
                                if (cRow) {
                                    if (isActive) cRow.classList.remove('opacity-75', 'bg-light');
                                    else cRow.classList.add('opacity-75', 'bg-light');
                                }
                                const childForm = document.querySelector(`.toggle-menu-form[data-menu-id="${childId}"]`);
                                if (childForm) {
                                    const childBtn = childForm.querySelector('button[type="submit"]');
                                    if (childBtn) {
                                        if (isActive) {
                                            childBtn.className = childBtn.className.replace('btn-outline-success', 'btn-outline-warning');
                                            childBtn.innerHTML = '<i class="bi bi-eye-slash"></i> Hide';
                                        } else {
                                            childBtn.className = childBtn.className.replace('btn-outline-warning', 'btn-outline-success');
                                            childBtn.innerHTML = '<i class="bi bi-eye"></i> Show';
                                        }
                                    }
                                }
                            });
                        }
                    }
                })
                .catch(err => {
                    console.error('Toggle error:', err);
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    form.submit();
                });
            });

            // Asynchronous instant update for Sort Order
            document.addEventListener('submit', function (e) {
                const form = e.target.closest('.sort-menu-form');
                if (!form) return;

                e.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                const input = form.querySelector('input[name="sort_order"]');
                if (!button || !input) return;

                const originalHtml = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px;" role="status" aria-hidden="true"></span>';

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        button.className = button.className.replace('btn-outline-primary', 'btn-success text-white');
                        button.innerHTML = '<i class="bi bi-check-lg"></i>';
                        setTimeout(() => {
                            button.className = button.className.replace('btn-success text-white', 'btn-outline-primary');
                            button.innerHTML = '<i class="bi bi-check2"></i>';
                            button.disabled = false;
                        }, 1200);
                    } else {
                        button.innerHTML = originalHtml;
                        button.disabled = false;
                    }
                })
                .catch(err => {
                    console.error('Sort update error:', err);
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    form.submit();
                });
            });
        });
    </script>
</x-app-layout>
