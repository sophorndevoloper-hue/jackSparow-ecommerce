@php
    $adminUser = auth('backend')->user() ?? auth()->user();
    $sidebarSections = \App\Models\AdminMenu::getSidebarTree($adminUser);
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
    <div class="sidebar-header">
        <a class="brand-mark" href="{{ route('admin.dashboard') }}" aria-label="JackSparow Admin">
            <span class="brand-icon"><i class="bi bi-cpu-fill" aria-hidden="true"></i></span>
            <span class="brand-copy">
                <span class="brand-title">JackSparow</span>
                <span class="brand-subtitle">Hardware Admin</span>
            </span>
        </a>
    </div>

    <nav class="sidebar-nav">
        @foreach($sidebarSections as $sectionTitle => $menuItems)
            @if(count($menuItems) > 0)
                @if($sectionTitle !== 'Main')
                    <div class="sidebar-section-title px-3 pt-3 pb-1 text-muted text-uppercase small fw-bold">
                        {{ $sectionTitle }}
                    </div>
                @endif

                @foreach($menuItems as $item)
                    @if(!empty($item['sub_items']))
                        {{-- Collapse Sub-Menu (Targets from Menu Setup) --}}
                        <div class="sidebar-item">
                            <a class="nav-link d-flex align-items-center justify-content-between {{ $item['is_active'] ? 'active' : '' }}"
                               data-bs-toggle="collapse" 
                               href="#menu-collapse-{{ $item['slug'] }}" 
                               role="button" 
                               aria-expanded="{{ $item['is_active'] ? 'true' : 'false' }}">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="nav-icon"><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i></span>
                                    <span class="nav-text text-truncate">{{ $item['title'] }}</span>
                                </div>
                                <i class="bi bi-chevron-down chevron-icon ms-1"></i>
                            </a>
                            <div class="collapse {{ $item['is_active'] ? 'show' : '' }} sidebar-submenu mt-1" id="menu-collapse-{{ $item['slug'] }}">
                                @foreach($item['sub_items'] as $sub)
                                    <a class="nav-link submenu-link {{ $sub['is_active'] ? 'active' : '' }}" href="{{ $sub['url'] }}">
                                        <span class="nav-icon submenu-icon"><i class="bi {{ $sub['icon'] }}"></i></span>
                                        <span class="nav-text text-truncate">{{ $sub['title'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        {{-- Single Menu Link (Target from Menu Setup) --}}
                        <a class="nav-link {{ $item['is_active'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                            <span class="nav-icon"><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i></span>
                            <span class="nav-text text-truncate">{{ $item['title'] }}</span>
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Storefront Section --}}
        <div class="sidebar-section-title px-3 pt-3 pb-1 text-muted text-uppercase small fw-bold">Storefront</div>
        <a class="nav-link" href="{{ route('home') }}" target="_blank">
            <span class="nav-icon"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></span>
            <span class="nav-text">Visit Storefront</span>
        </a>
        <a class="nav-link" href="{{ route('shop') }}" target="_blank">
            <span class="nav-icon"><i class="bi bi-shop" aria-hidden="true"></i></span>
            <span class="nav-text">Browse Catalog</span>
        </a>
    </nav>

    {{-- User Profile Card --}}
    <div class="sidebar-user">
        <a href="{{ route('admin.profile.edit') }}" class="d-flex align-items-center text-decoration-none text-reset w-100" title="Manage Profile">
            @if($adminUser && $adminUser->profile && $adminUser->profile->avatar)
                <img src="{{ $adminUser->avatar_url }}" alt="{{ $adminUser->name }}" class="rounded-circle object-fit-cover me-2 border border-secondary-subtle" style="width: 40px; height: 40px;">
            @else
                <div class="avatar-md bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 40px; height: 40px;">
                    {{ substr($adminUser->name ?? 'A', 0, 1) }}
                </div>
            @endif
            <div class="overflow-hidden">
                <strong class="d-block text-truncate">{{ $adminUser?->profile?->full_name ?: ($adminUser->name ?? 'Admin') }}</strong>
                <small class="d-block text-muted text-truncate">
                    @if($adminUser && $adminUser->hasRole('superadmin', 'backend'))
                        Super Administrator
                    @elseif($adminUser && $adminUser->hasRole('admin', 'backend'))
                        Administrator
                    @else
                        Staff User
                    @endif
                </small>
            </div>
        </a>
    </div>

    <div class="sidebar-footer d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <span class="status-dot"></span>
            <span class="sidebar-footer-text">Hardware DB Online</span>
        </div>
        @if(\App\Models\AdminMenu::userHasPermission($adminUser, 'view menus|view settings') || $adminUser?->hasRole('superadmin', 'backend') || $adminUser?->hasRole('admin', 'backend'))
            <a href="{{ route('admin.menus.index') }}" class="text-secondary text-hover-primary text-decoration-none" title="Menu & Navigation Setup">
                <i class="bi bi-sliders" style="font-size: 13px;"></i>
            </a>
        @endif
    </div>
</aside>