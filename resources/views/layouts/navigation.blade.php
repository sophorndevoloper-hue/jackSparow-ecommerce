<nav class="navbar admin-navbar navbar-expand bg-white">
    <div class="container-fluid px-3 px-lg-4">
        <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <form class="d-none d-md-flex ms-3 flex-grow-1" role="search">
            <input class="form-control search-input" type="search" placeholder="Search users, orders, reports" aria-label="Search">
        </form>

        <div class="navbar-actions ms-auto">
            <form action="{{ route('admin.cache.clear') }}" method="POST" class="d-inline">
                @csrf
                <button class="icon-button" type="submit" aria-label="Clear System Cache" title="Clear System Cache">
                    <i class="bi bi-arrow-repeat text-warning" aria-hidden="true"></i>
                </button>
            </form>

            <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
                <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>
            <div class="dropdown">
                <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <span class="notification-dot"></span>
                    <i class="bi bi-bell" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu">
                    <div class="dropdown-header fw-bold text-body">Notifications</div>
                    <a class="dropdown-item" href="users.html">
                        <span class="notification-title">New user registered</span>
                        <span class="notification-time">4 minutes ago</span>
                    </a>
                    <a class="dropdown-item" href="charts.html">
                        <span class="notification-title">Revenue target reached</span>
                        <span class="notification-time">32 minutes ago</span>
                    </a>
                    <a class="dropdown-item" href="settings.html">
                        <span class="notification-title">Security review completed</span>
                        <span class="notification-time">1 hour ago</span>
                    </a>
                </div>
            </div>

            @php
                $adminUser = auth('backend')->user() ?? auth()->user();
                $displayName = $adminUser?->profile?->full_name ?: ($adminUser?->name ?? 'Admin');
                $hasCustomAvatar = $adminUser && $adminUser->profile && $adminUser->profile->avatar;
                $initial = strtoupper(substr($displayName, 0, 1));
            @endphp

            <div class="dropdown">
                <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if($hasCustomAvatar)
                        <img class="avatar-img avatar-sm object-fit-cover rounded-circle border border-primary-subtle" 
                             src="{{ $adminUser->avatar_url }}" 
                             alt="{{ $displayName }}" 
                             style="width: 34px; height: 34px; min-width: 34px;">
                    @else
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold shadow-sm" 
                             style="width: 34px; height: 34px; min-width: 34px; font-size: 13px;">
                            {{ $initial }}
                        </div>
                    @endif
                    <span class="d-none d-sm-inline">{{ $displayName }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}"><i class="bi bi-person me-2"></i> {{ __('Profile') }}</a></li>
                    @canany(['view users', 'view roles', 'view settings', 'edit settings'])
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header small text-muted text-uppercase">System Settings</li>
                        @can('view users')
                            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2"></i> Users & Permissions</a></li>
                        @endcan
                        @can('view roles')
                            <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-lock me-2"></i> System Roles</a></li>
                        @endcan
                        @can('view settings')
                            <li><a class="dropdown-item" href="{{ route('admin.menus.index') }}"><i class="bi bi-sliders me-2"></i> Menu Setup</a></li>
                        @endcan
                    @endcanany
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.cache.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-warning">
                                <i class="bi bi-arrow-repeat me-2"></i> Clear System Cache
                            </button>
                        </form>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                                onclick="event.preventDefault();
                                        this.closest('form').submit();"><i class="bi bi-box-arrow-right me-2"></i> {{ __('Log Out') }}</a></li>
                    </form>
                </ul>
            </div>
        </div>
    </div>
</nav>