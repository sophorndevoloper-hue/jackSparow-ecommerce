<x-app-layout>
    <style>
        /* Modern Parent Row & Collapse Styles */
        .parent-menu-row {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .parent-menu-row:hover {
            background-color: rgba(59, 130, 246, 0.04) !important;
        }
        .parent-menu-row.is-open {
            background-color: rgba(59, 130, 246, 0.06) !important;
            border-left: 3px solid #2563eb !important;
        }
        html[data-theme="dark"] .parent-menu-row:hover {
            background-color: rgba(59, 130, 246, 0.08) !important;
        }
        html[data-theme="dark"] .parent-menu-row.is-open {
            background-color: rgba(59, 130, 246, 0.12) !important;
            border-left: 3px solid #3b82f6 !important;
        }

        /* Dropdown Trigger Pill */
        /* Parent Group & Direct Child Row Hierarchy Styling */
        .parent-group-row {
            background-color: rgba(59, 130, 246, 0.04);
            border-top: 1px solid rgba(148, 163, 184, 0.25);
        }
        html[data-theme="dark"] .parent-group-row {
            background-color: rgba(30, 41, 59, 0.45);
            border-top-color: rgba(148, 163, 184, 0.15);
        }
        .child-menu-row {
            background-color: rgba(248, 250, 252, 0.4);
            border-left: 2px solid rgba(59, 130, 246, 0.35);
        }
        html[data-theme="dark"] .child-menu-row {
            background-color: rgba(15, 23, 42, 0.25);
            border-left-color: rgba(96, 165, 250, 0.35);
        }
        .child-tree-connector {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            color: #94a3b8;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 700;
            font-size: 14px;
            user-select: none;
            flex-shrink: 0;
        }
        html[data-theme="dark"] .child-tree-connector {
            color: #64748b;
        }

        /* Menu Icons */
        .menu-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(99, 102, 241, 0.06));
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #3b82f6;
            flex-shrink: 0;
        }
        .menu-icon-child {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            background: rgba(100, 116, 139, 0.1);
            border: 1px solid rgba(100, 116, 139, 0.2);
            color: #64748b;
            flex-shrink: 0;
        }
        html[data-theme="dark"] .menu-icon-child {
            background: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.2);
            color: #94a3b8;
        }

        /* Modern Sort Control Pill */
        .sort-pill-box {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 6px;
            overflow: hidden;
            background: var(--bs-body-bg);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .sort-pill-box:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .sort-pill-input {
            width: 44px;
            height: 25px;
            border: none;
            background: transparent;
            text-align: center;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--bs-body-color);
            padding: 0;
        }
        .sort-pill-input:focus {
            outline: none;
        }
        .sort-pill-btn {
            width: 24px;
            height: 25px;
            border: none;
            border-left: 1px solid rgba(148, 163, 184, 0.2);
            background: transparent;
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .sort-pill-btn:hover {
            background-color: rgba(59, 130, 246, 0.15);
            color: #2563eb;
        }
        html[data-theme="dark"] .sort-pill-box {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(148, 163, 184, 0.25);
        }
        html[data-theme="dark"] .sort-pill-btn {
            border-left-color: rgba(148, 163, 184, 0.2);
            color: #94a3b8;
        }

        /* Route Pill Badge */
        .route-pill {
            font-size: 11px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #64748b;
            background-color: rgba(100, 116, 139, 0.08);
            border: 1px solid rgba(100, 116, 139, 0.15);
            padding: 1px 7px;
            border-radius: 4px;
            display: inline-block;
        }
        html[data-theme="dark"] .route-pill {
            color: #94a3b8;
            background-color: rgba(148, 163, 184, 0.1);
            border-color: rgba(148, 163, 184, 0.2);
        }

        /* Permission Pill */
        .perm-badge {
            font-size: 11px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #0284c7;
            background-color: rgba(2, 132, 199, 0.08);
            border: 1px solid rgba(2, 132, 199, 0.25);
            white-space: nowrap;
        }
        html[data-theme="dark"] .perm-badge {
            color: #38bdf8;
            background-color: rgba(56, 189, 248, 0.12);
            border-color: rgba(56, 189, 248, 0.3);
        }

        /* Action Badges with High Contrast in Dark & Light Mode */
        .action-chip {
            font-size: 10px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }
        .action-chip-view {
            color: #1d4ed8;
            background-color: rgba(29, 78, 216, 0.08);
            border: 1px solid rgba(29, 78, 216, 0.2);
        }
        html[data-theme="dark"] .action-chip-view {
            color: #60a5fa !important;
            background-color: rgba(96, 165, 250, 0.14) !important;
            border-color: rgba(96, 165, 250, 0.3) !important;
        }
        .action-chip-create {
            color: #15803d;
            background-color: rgba(21, 128, 61, 0.08);
            border: 1px solid rgba(21, 128, 61, 0.2);
        }
        html[data-theme="dark"] .action-chip-create {
            color: #4ade80 !important;
            background-color: rgba(74, 222, 128, 0.14) !important;
            border-color: rgba(74, 222, 128, 0.3) !important;
        }
        .action-chip-edit {
            color: #b45309;
            background-color: rgba(180, 83, 9, 0.08);
            border: 1px solid rgba(180, 83, 9, 0.2);
        }
        html[data-theme="dark"] .action-chip-edit {
            color: #fbbf24 !important;
            background-color: rgba(251, 191, 36, 0.14) !important;
            border-color: rgba(251, 191, 36, 0.3) !important;
        }
        .action-chip-delete {
            color: #b91c1c;
            background-color: rgba(185, 28, 28, 0.08);
            border: 1px solid rgba(185, 28, 28, 0.2);
        }
        html[data-theme="dark"] .action-chip-delete {
            color: #f87171 !important;
            background-color: rgba(248, 113, 113, 0.14) !important;
            border-color: rgba(248, 113, 113, 0.3) !important;
        }

        /* Status Pills */
        .status-badge-active {
            font-size: 11px;
            font-weight: 600;
            color: #15803d;
            background-color: rgba(21, 128, 61, 0.1);
            border: 1px solid rgba(21, 128, 61, 0.25);
            border-radius: 9999px;
            padding: 3px 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        html[data-theme="dark"] .status-badge-active {
            color: #4ade80;
            background-color: rgba(74, 222, 128, 0.14);
            border-color: rgba(74, 222, 128, 0.3);
        }
        .status-badge-hidden {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            background-color: rgba(100, 116, 139, 0.1);
            border: 1px solid rgba(100, 116, 139, 0.2);
            border-radius: 9999px;
            padding: 3px 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }
        html[data-theme="dark"] .status-badge-hidden {
            color: #94a3b8;
            background-color: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.2);
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
                @if($disabledMenus > 0)
                    <form action="{{ route('admin.menus.reset-visibility') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-success btn-sm shadow-sm" title="Restore all hidden menus so they are visible again in your navigation">
                            <i class="bi bi-eye me-1"></i> Restore All Menus ({{ $disabledMenus }} Hidden)
                        </button>
                    </form>
                @endif
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
                <div class="panel p-3 h-100 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 bg-secondary-subtle text-secondary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                            <i class="bi bi-slash-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">Hidden For You</div>
                            <div class="fs-4 fw-bold text-muted" id="disabled-count-kpi">{{ $disabledMenus }}</div>
                            <small class="text-muted">Hidden in this session</small>
                        </div>
                    </div>
                    @if($disabledMenus > 0)
                        <form action="{{ route('admin.menus.reset-visibility') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 font-monospace" style="font-size: 11px;" title="Restore all hidden menus">
                                <i class="bi bi-eye"></i> Show All
                            </button>
                        </form>
                    @endif
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
                                    <th>Actions</th>
                                    <th>Status</th>
                                    <th class="text-end" style="min-width: 190px;">Controls</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topLevelMenus as $menu)
                                    @php
                                        $childMenus = $sectionMenus->filter(fn ($m) => $m->parent_slug === $menu->slug);
                                        $hasChildren = $childMenus->isNotEmpty();
                                        $isProtected = \App\Models\AdminMenu::isProtected($menu);
                                        $isParentActive = $isProtected || ($menu->is_active_for_session ?? \App\Models\AdminMenu::isMenuActiveForSession($menu));
                                    @endphp

                                    @if($hasChildren)
                                        <!-- Parent Group Header Row (Direct, No Dropdown) -->
                                        <tr class="parent-group-row {{ ! $isParentActive ? 'opacity-75 bg-light' : '' }}" id="parent-row-{{ $menu->slug }}">
                                            <td onclick="event.stopPropagation();">
                                                <form action="{{ route('admin.menus.sort', $menu->id) }}" method="POST" class="sort-menu-form" data-menu-id="{{ $menu->id }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="sort-pill-box">
                                                        <input type="number" 
                                                               name="sort_order" 
                                                               value="{{ $menu->sort_order }}" 
                                                               min="0" 
                                                               max="9999" 
                                                               class="sort-pill-input" 
                                                               title="Sort order for {{ $menu->title }}">
                                                        <button type="submit" class="sort-pill-btn" title="Save sort order">
                                                            <i class="bi bi-check2"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="menu-icon-box">
                                                        <i class="bi {{ $menu->icon }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <strong class="text-dark fs-6">{{ $menu->title }}</strong>
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 10.5px;">
                                                                <i class="bi bi-folder2-open me-1"></i>Group ({{ $childMenus->count() }} items)
                                                            </span>
                                                        </div>
                                                        <span class="text-muted small">Parent Group</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td colspan="2" class="align-middle">
                                                <span class="badge bg-body-secondary text-secondary border font-monospace px-2 py-1" style="font-size: 11px;">
                                                    <i class="bi bi-shield-check text-primary me-1"></i>Inherited from sub-items
                                                </span>
                                            </td>
                                            <td id="status-cell-{{ $menu->id }}">
                                                @if($isProtected)
                                                    <span class="status-badge-active" title="Permanent core system component">
                                                        <i class="bi bi-shield-check"></i> Permanent
                                                    </span>
                                                @elseif($isParentActive)
                                                    <span class="status-badge-active">
                                                        <i class="bi bi-check-circle-fill"></i> Active
                                                    </span>
                                                @else
                                                    <span class="status-badge-hidden">
                                                        <i class="bi bi-eye-slash"></i> Hidden (You)
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end" onclick="event.stopPropagation();">
                                                <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace me-1" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Edit Menu Attributes & Order">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                @if($isProtected)
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace py-1 px-2" style="font-size: 11px; height: 26px; line-height: 18px; display: inline-flex; align-items: center;" title="Core system menu - cannot be hidden">
                                                        <i class="bi bi-shield-lock text-primary me-1"></i> Core
                                                    </span>
                                                @else
                                                    <form action="{{ route('admin.menus.toggle-active', $menu->id) }}" 
                                                          method="POST" 
                                                          class="d-inline toggle-menu-form"
                                                          data-menu-id="{{ $menu->id }}"
                                                          data-is-parent="1"
                                                          data-slug="{{ $menu->slug }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($isParentActive)
                                                            <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2 font-monospace" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Hide this group and all its sub-items">
                                                                <i class="bi bi-eye-slash"></i> Hide Group
                                                            </button>
                                                        @else
                                                            <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 font-monospace" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Show this group and all its sub-items">
                                                                <i class="bi bi-eye"></i> Show Group
                                                            </button>
                                                        @endif
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Direct Child Rows under Parent Group (Always Visible, No Dropdown/Collapse) -->
                                        @foreach($childMenus as $child)
                                            @php
                                                $isChildProtected = \App\Models\AdminMenu::isProtected($child);
                                                $isChildActive = $isChildProtected || ($isParentActive && ($child->is_active_for_session ?? \App\Models\AdminMenu::isMenuActiveForSession($child)));
                                            @endphp
                                            <tr class="child-menu-row {{ ! $isChildActive ? 'opacity-75 bg-light' : '' }}" id="child-row-{{ $child->id }}">
                                                <td onclick="event.stopPropagation();" class="ps-3">
                                                    <form action="{{ route('admin.menus.sort', $child->id) }}" method="POST" class="sort-menu-form" data-menu-id="{{ $child->id }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="sort-pill-box">
                                                            <input type="number" 
                                                                   name="sort_order" 
                                                                   value="{{ $child->sort_order }}" 
                                                                   min="0" 
                                                                   max="9999" 
                                                                   class="sort-pill-input" 
                                                                   title="Sort order for {{ $child->title }}">
                                                            <button type="submit" class="sort-pill-btn" title="Save sort order">
                                                                <i class="bi bi-check2"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2 ps-2">
                                                        <span class="child-tree-connector">↳</span>
                                                        <div class="menu-icon-child">
                                                            <i class="bi {{ $child->icon }}"></i>
                                                        </div>
                                                        <div>
                                                            <strong class="text-dark d-block" style="font-size: 13px;">{{ $child->title }}</strong>
                                                            <code class="route-pill">{{ $child->route_name }}</code>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($child->view_permission)
                                                        <span class="perm-badge">
                                                            <i class="bi bi-key-fill"></i> {{ $child->view_permission }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small font-monospace">Public</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 440px;">
                                                        @forelse($child->actions ?? [] as $action)
                                                            @php
                                                                $type = $action['action_type'] ?? '';
                                                                $pillClass = match($type) {
                                                                    'view' => 'action-chip-view',
                                                                    'create' => 'action-chip-create',
                                                                    'edit' => 'action-chip-edit',
                                                                    'delete' => 'action-chip-delete',
                                                                    default => 'action-chip-view'
                                                                };
                                                                $iconClass = match($type) {
                                                                    'view' => 'bi-eye',
                                                                    'create' => 'bi-plus-circle',
                                                                    'edit' => 'bi-pencil',
                                                                    'delete' => 'bi-trash',
                                                                    default => 'bi-check'
                                                                };
                                                                $permName = $action['permission'] ?? '';
                                                            @endphp
                                                            <span class="action-chip {{ $pillClass }}" title="{{ $permName ? 'Permission: '.$permName : 'Public' }}">
                                                                <i class="bi {{ $iconClass }}"></i>{{ $action['name'] }}
                                                            </span>
                                                        @empty
                                                            <span class="text-muted small font-monospace">—</span>
                                                        @endforelse
                                                    </div>
                                                </td>
                                                <td id="status-cell-{{ $child->id }}">
                                                    @if($isChildProtected)
                                                        <span class="status-badge-active" title="Permanent core system component">
                                                            <i class="bi bi-shield-check"></i> Permanent
                                                        </span>
                                                    @elseif(! $isParentActive)
                                                        <span class="status-badge-hidden">
                                                            <i class="bi bi-eye-slash"></i> Hidden (Parent)
                                                        </span>
                                                    @elseif($isChildActive)
                                                        <span class="status-badge-active">
                                                            <i class="bi bi-check-circle-fill"></i> Active
                                                        </span>
                                                    @else
                                                        <span class="status-badge-hidden">
                                                            <i class="bi bi-eye-slash"></i> Hidden (You)
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end" onclick="event.stopPropagation();">
                                                    <a href="{{ route('admin.menus.edit', $child->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace me-1" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Edit {{ $child->title }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    @if($isChildProtected)
                                                        <span class="badge bg-secondary-subtle text-secondary border font-monospace py-1 px-2" style="font-size: 11px; height: 26px; line-height: 18px; display: inline-flex; align-items: center;" title="Core system menu - cannot be hidden">
                                                            <i class="bi bi-shield-lock text-primary me-1"></i> Core
                                                        </span>
                                                    @else
                                                        <form action="{{ route('admin.menus.toggle-active', $child->id) }}" 
                                                              method="POST" 
                                                              class="d-inline toggle-menu-form"
                                                              data-menu-id="{{ $child->id }}"
                                                              data-is-parent="0"
                                                              data-slug="{{ $child->slug }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            @if($isChildActive)
                                                                <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2 font-monospace" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Hide this item">
                                                                    <i class="bi bi-eye-slash"></i> Hide
                                                                </button>
                                                            @else
                                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 font-monospace" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Show this item">
                                                                    <i class="bi bi-eye"></i> Show
                                                                </button>
                                                            @endif
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <!-- Top-Level Standalone Menu Row (e.g. Categories, Brands, Orders) -->
                                        <tr class="standalone-menu-row {{ ! $isParentActive ? 'opacity-75 bg-light' : '' }}" id="parent-row-{{ $menu->slug }}">
                                            <td onclick="event.stopPropagation();">
                                                <form action="{{ route('admin.menus.sort', $menu->id) }}" method="POST" class="sort-menu-form" data-menu-id="{{ $menu->id }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="sort-pill-box">
                                                        <input type="number" 
                                                               name="sort_order" 
                                                               value="{{ $menu->sort_order }}" 
                                                               min="0" 
                                                               max="9999" 
                                                               class="sort-pill-input" 
                                                               title="Sort order for {{ $menu->title }}">
                                                        <button type="submit" class="sort-pill-btn" title="Save sort order">
                                                            <i class="bi bi-check2"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="menu-icon-box">
                                                        <i class="bi {{ $menu->icon }}"></i>
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark d-block">{{ $menu->title }}</strong>
                                                        <code class="route-pill mt-1">{{ $menu->route_name }}</code>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($menu->view_permission)
                                                    <span class="perm-badge">
                                                        <i class="bi bi-key-fill"></i> {{ $menu->view_permission }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small font-monospace">Public</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 480px;">
                                                    @forelse($menu->actions ?? [] as $action)
                                                        @php
                                                            $type = $action['action_type'] ?? '';
                                                            $pillClass = match($type) {
                                                                'view' => 'action-chip-view',
                                                                'create' => 'action-chip-create',
                                                                'edit' => 'action-chip-edit',
                                                                'delete' => 'action-chip-delete',
                                                                default => 'action-chip-view'
                                                            };
                                                            $iconClass = match($type) {
                                                                'view' => 'bi-eye',
                                                                'create' => 'bi-plus-circle',
                                                                'edit' => 'bi-pencil',
                                                                'delete' => 'bi-trash',
                                                                default => 'bi-check'
                                                            };
                                                            $permName = $action['permission'] ?? '';
                                                        @endphp
                                                        <span class="action-chip {{ $pillClass }}" title="{{ $permName ? 'Permission: '.$permName : 'Public' }}">
                                                            <i class="bi {{ $iconClass }}"></i>{{ $action['name'] }}
                                                        </span>
                                                    @empty
                                                        <span class="text-muted small">—</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td id="status-cell-{{ $menu->id }}">
                                                @if($isProtected)
                                                    <span class="status-badge-active" title="Permanent core system component">
                                                        <i class="bi bi-shield-check"></i> Permanent
                                                    </span>
                                                @elseif($isParentActive)
                                                    <span class="status-badge-active">
                                                        <i class="bi bi-check-circle-fill"></i> Active
                                                    </span>
                                                @else
                                                    <span class="status-badge-hidden">
                                                        <i class="bi bi-eye-slash"></i> Hidden (You)
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end" onclick="event.stopPropagation();">
                                                <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary py-0 px-2 font-monospace me-1" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Edit Menu Attributes & Order">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                @if($isProtected)
                                                    <span class="badge bg-secondary-subtle text-secondary border font-monospace py-1 px-2" style="font-size: 11px; height: 26px; line-height: 18px; display: inline-flex; align-items: center;" title="Core system menu - cannot be hidden">
                                                        <i class="bi bi-shield-lock text-primary me-1"></i> Core
                                                    </span>
                                                @else
                                                    <form action="{{ route('admin.menus.toggle-active', $menu->id) }}" 
                                                          method="POST" 
                                                          class="d-inline toggle-menu-form"
                                                          data-menu-id="{{ $menu->id }}"
                                                          data-is-parent="0"
                                                          data-slug="{{ $menu->slug }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        @if($isParentActive)
                                                            <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2 font-monospace" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Hide this menu from navigation">
                                                                <i class="bi bi-eye-slash"></i> Hide
                                                            </button>
                                                        @else
                                                            <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 font-monospace" style="font-size: 11.5px; height: 26px; line-height: 24px;" title="Show this menu in navigation">
                                                                <i class="bi bi-eye"></i> Show
                                                            </button>
                                                        @endif
                                                    </form>
                                                @endif
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
                            if (isActive) {
                                statusCell.innerHTML = '<span class="status-badge-active"><i class="bi bi-check-circle-fill"></i> Active</span>';
                            } else {
                                statusCell.innerHTML = '<span class="status-badge-hidden"><i class="bi bi-eye-slash"></i> Hidden (You)</span>';
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
                                        childStatusCell.innerHTML = '<span class="status-badge-active"><i class="bi bi-check-circle-fill"></i> Active</span>';
                                    } else {
                                        childStatusCell.innerHTML = '<span class="status-badge-hidden"><i class="bi bi-eye-slash"></i> Hidden (Parent)</span>';
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
                        button.classList.add('text-success');
                        button.innerHTML = '<i class="bi bi-check-lg text-success"></i>';
                        setTimeout(() => {
                            button.classList.remove('text-success');
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
