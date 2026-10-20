@php
$siteName = \App\Models\WebsiteSetting::get('site_name', 'JackSparow TECH');
$siteTagline = \App\Models\WebsiteSetting::get('site_tagline', 'Computer Hardware & Parts');
$announcementEnabled = \App\Models\WebsiteSetting::get('announcement_enabled', '1') === '1';
$announcementText = \App\Models\WebsiteSetting::get('announcement_text', 'Fast Express Shipping on orders over $150');
$announcementSubtext = \App\Models\WebsiteSetting::get('announcement_subtext', '100% Genuine Components & Official Brand
Warranty');
$contactPhone = \App\Models\WebsiteSetting::get('contact_phone', '+1 (800) 555-PC-RIG');
$contactEmail = \App\Models\WebsiteSetting::get('contact_email', 'support@jacksparow-tech.test');
$contactAddress = \App\Models\WebsiteSetting::get('contact_address', '100 Technology Plaza, Silicon Way');
$footerAbout = \App\Models\WebsiteSetting::get('footer_about', 'Your trusted destination for custom gaming computer
parts, next-gen CPUs, GPUs, high-speed RAM, and extreme PC cooling systems.');
$footerCopyright = \App\Models\WebsiteSetting::get('footer_copyright', 'JackSparow TECH E-Commerce. All rights
reserved.');

// 3 Types of Storefront Menus (Cached retrieval with children)
$topMenus = \App\Models\StorefrontMenu::getTree('top');
$mainMenus = \App\Models\StorefrontMenu::getTree('main');
$footerMenus = \App\Models\StorefrontMenu::getTree('footer');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', $siteName . ($siteTagline ? ' - ' . $siteTagline : ' - High-Performance Computer Parts &
        Hardware'))</title>
    @if($siteTagline)
    <meta name="description" content="{{ $siteTagline }}">
    @endif
    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/logo/jacksparrow-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/bootstrap-icons.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Fallback and critical responsive styles */
        .storefront-topbar {
            background-color: #020617 !important;
            color: #cbd5e1 !important;
        }

        .storefront-navbar {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }

        .storefront-footer {
            background-color: #020617 !important;
            color: #94a3b8 !important;
        }

        .brand-logo-badge {
            background: linear-gradient(135deg, #2563eb, #4f46e5) !important;
            color: #ffffff !important;
        }

        @media (min-width: 768px) {
            .header-search-bar {
                display: flex !important;
            }

            .desktop-nav-bar {
                display: flex !important;
            }

            .mobile-nav-toggle {
                display: none !important;
            }

            .mobile-menu-drawer {
                display: none !important;
            }
        }

        @media (max-width: 767.98px) {
            .header-search-bar {
                display: none !important;
            }

            .desktop-nav-bar {
                display: none !important;
            }

            .mobile-nav-toggle {
                display: inline-flex !important;
            }
        }

        /* Animated Toast Keyframes */
        @keyframes toastIconPop {
            0% {
                transform: scale(0.3) rotate(-15deg);
                opacity: 0;
            }

            65% {
                transform: scale(1.22) rotate(4deg);
            }

            100% {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        @keyframes toastCountdown {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        .toast-icon-pop {
            animation: toastIconPop 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .toast-progress-countdown {
            animation: toastCountdown 4.5s linear forwards;
        }
    </style>
</head>

<body class="font-sans antialiased text-gray-900 bg-gray-50 min-h-full flex flex-col"
    x-data="{ mobileMenuOpen: false }">

    <!-- ================= 1. TOP UTILITY BAR (TOP MENU) ================= -->
    @if($announcementEnabled || $topMenus->count() > 0 || $contactPhone || Route::has('admin.dashboard'))
    <div class="storefront-topbar bg-slate-950 text-slate-300 text-xs py-2 px-4 border-b border-slate-800"
        style="background-color: #020617; color: #cbd5e1;">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2 sm:gap-3">
            <!-- Dynamic Top Menus (About Us, Contact, Tracking...) & Announcement -->
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                @if($announcementEnabled && $announcementText)
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold tracking-wide text-[11px] sm:text-xs">
                    <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ $announcementText }}
                </span>
                @if($topMenus->count() > 0)
                <span class="hidden lg:inline text-slate-700">|</span>
                @endif
                @endif

                @if($topMenus->count() > 0)
                <nav class="hidden lg:flex items-center gap-3" aria-label="Top utility navigation">
                    @foreach($topMenus as $tMenu)
                    <a href="{{ $tMenu->resolved_url }}" target="{{ $tMenu->target }}"
                        class="hover:text-blue-400 transition flex items-center gap-1.5 text-xs text-slate-300 font-medium">
                        @if($tMenu->icon)
                        <i class="bi {{ $tMenu->icon }} text-blue-400 text-xs"></i>
                        @endif
                        <span>{{ $tMenu->title }}</span>
                        @if($tMenu->badge)
                        <span
                            class="bg-blue-600 text-white text-[9px] px-1.5 py-0.5 rounded-full font-black uppercase tracking-wider">{{
                            $tMenu->badge }}</span>
                        @endif
                    </a>
                    @if(!$loop->last)
                    <span class="text-slate-700 text-[10px]">&bull;</span>
                    @endif
                    @endforeach
                </nav>
                @endif
            </div>

            <!-- Support & Admin Portal -->
            <div class="flex items-center gap-3 sm:gap-4 text-slate-300 text-[11px] sm:text-xs">
                @if($contactPhone)
                <span class="hidden sm:inline text-slate-400">
                    <i class="bi bi-telephone-fill text-slate-500 me-1"></i>{{ $contactPhone }}
                </span>
                @endif

                @if(Route::has('admin.dashboard') && (auth('backend')->check() || (auth()->check() &&
                auth()->user()->hasRole('admin'))))
                <a href="{{ route('admin.dashboard') }}"
                    class="text-amber-400 hover:text-amber-300 font-bold flex items-center gap-1 border-l border-slate-800 pl-3">
                    <i class="bi bi-shield-lock-fill text-amber-400"></i>
                    <span>Admin Portal</span>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- ================= 2. MAIN HEADER & BRAND ================= -->
    <header class="bg-white/95 backdrop-blur-md border-b border-gray-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-2 sm:gap-4">
                <!-- Brand Logo Mark -->
                <a href="{{ route('home') }}" class="flex items-center shrink-0 group py-1"
                    aria-label="{{ $siteName }}">
                    <img src="{{ asset('assets/images/brand/logo/jacksparrow-logo.png') }}" alt="{{ $siteName }}"
                        class="h-8 sm:h-11 md:h-12 w-auto max-w-[130px] sm:max-w-none object-contain transition duration-200 group-hover:opacity-90">
                </a>

                <!-- Quick Product Search Bar -->
                <form action="{{ route('shop') }}" method="GET"
                    class="header-search-bar hidden md:flex flex-1 max-w-xl mx-4">
                    <div class="relative w-full">
                        <input type="search" name="search" value="{{ request('search') }}"
                            placeholder="Search CPUs, RTX 40 GPUs, DDR5 RAM, NVMe SSDs..."
                            class="w-full pl-11 pr-24 py-2.5 bg-gray-100/90 border border-gray-300 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition shadow-inner">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <i class="bi bi-search fs-6"></i>
                        </div>
                        <button type="submit"
                            class="absolute inset-y-1.5 right-1.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-xs"
                            style="background-color: #2563eb; color: #ffffff;">
                            Search
                        </button>
                    </div>
                </form>

                <!-- Actions: User Profile & Shopping Cart -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    <!-- User Account Dropdown -->
                    @if(auth('frontend')->check())
                    @php $customerUser = auth('frontend')->user(); @endphp
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" type="button"
                            class="flex items-center gap-1.5 sm:gap-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition p-1 sm:p-1.5 rounded-2xl hover:bg-gray-100">
                            <span
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                                {{ strtoupper(substr($customerUser->name, 0, 1)) }}
                            </span>
                            <span class="hidden xl:inline text-left">
                                <span
                                    class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Welcome</span>
                                <span class="block font-bold -mt-0.5 text-xs text-gray-900 truncate max-w-28">{{
                                    $customerUser->name }}</span>
                            </span>
                            <i class="bi bi-chevron-down text-gray-400 text-xs hidden sm:inline"></i>
                        </button>

                        <div x-show="open" x-transition
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100 mb-1">
                                <p class="text-xs font-bold text-gray-900">{{ $customerUser->name }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $customerUser->email }}</p>
                            </div>
                            <a href="{{ route('customer.profile') }}"
                                class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                <i class="bi bi-person-lines-fill text-blue-600"></i> My Profile
                            </a>
                            <a href="{{ route('my-orders') }}"
                                class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                <i class="bi bi-box-seam text-blue-600"></i> My Orders
                            </a>
                            <a href="{{ route('favorites.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-rose-600">
                                <i class="bi bi-heart text-rose-500"></i> My Favorites
                            </a>
                            <a href="{{ route('recently-viewed.index') }}"
                                class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-blue-600">
                                <i class="bi bi-clock-history text-blue-500"></i> Recently Viewed
                            </a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                                    <i class="bi bi-box-arrow-right text-rose-600"></i> Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="flex items-center gap-1 sm:gap-1.5">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-1 sm:gap-1.5 text-xs sm:text-sm font-bold text-gray-700 hover:text-blue-600 transition px-2 sm:px-3 py-1.5 sm:py-2 rounded-xl hover:bg-gray-100"
                            title="Sign In">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span class="hidden sm:inline">Sign In</span>
                        </a>
                        <a href="{{ route('register') }}"
                            class="hidden sm:inline-flex items-center text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition px-3.5 py-2 rounded-xl">
                            Register
                        </a>
                    </div>
                    @endif

                    <!-- Favorites Heart Button with Live Item Count -->
                    @php
                    $favoriteCount = \App\Models\Favorite::getCount();
                    @endphp
                    <a href="{{ route('favorites.index') }}" id="storefront-favorite-button"
                        class="relative inline-flex items-center justify-center p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition shadow-xs"
                        title="View Saved Favorites" aria-label="View Saved Favorites">
                        <i class="bi bi-heart text-sm sm:text-base"></i>
                        <span id="storefront-favorite-badge"
                            class="absolute -top-1 -right-1 bg-rose-600 text-white font-black text-[10px] sm:text-[11px] min-w-[18px] sm:min-w-[20px] h-4.5 sm:h-5 px-1 rounded-full flex items-center justify-center shadow-xs transition-all duration-300 transform {{ $favoriteCount > 0 ? 'scale-100 opacity-100' : 'scale-0 opacity-0 pointer-events-none' }}">
                            <span id="storefront-favorite-count">{{ $favoriteCount }}</span>
                        </span>
                    </a>

                    <!-- Cart Button with Live Item Count -->
                    @php
                    $cartItems = session('cart', []);
                    $cartCount = array_sum(array_column($cartItems, 'quantity'));
                    @endphp
                    <a href="{{ route('cart.index') }}" id="storefront-cart-button"
                        class="relative inline-flex items-center justify-center p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 transition shadow-xs"
                        title="View Shopping Cart" aria-label="View Shopping Cart">
                        <i class="bi bi-cart3 text-sm sm:text-base"></i>
                        <span id="storefront-cart-badge"
                            class="absolute -top-1 -right-1 bg-blue-600 text-white font-black text-[10px] sm:text-[11px] min-w-[18px] sm:min-w-[20px] h-4.5 sm:h-5 px-1 rounded-full flex items-center justify-center shadow-xs transition-all duration-300 transform {{ $cartCount > 0 ? 'scale-100 opacity-100' : 'scale-0 opacity-0 pointer-events-none' }}">
                            <span id="storefront-cart-count">{{ $cartCount }}</span>
                        </span>
                    </a>

                    <!-- Mobile Menu Hamburger Button -->
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                        class="mobile-nav-toggle md:hidden p-1.5 sm:p-2 rounded-xl text-gray-700 hover:bg-gray-100 focus:outline-hidden"
                        aria-label="Toggle navigation menu">
                        <i class="bi" :class="mobileMenuOpen ? 'bi-x-lg text-lg' : 'bi-list text-xl'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= 3. MAIN NAVBAR & HIERARCHICAL MENUS ================= -->
        <nav class="storefront-navbar bg-slate-900 text-slate-200 border-t border-slate-800 shadow-inner"
            aria-label="Main Storefront Navigation" style="background-color: #0f172a; color: #f1f5f9;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Desktop Navbar (Supports Parent & Child Dropdowns) -->
                <div class="desktop-nav-bar hidden md:flex items-center gap-1 py-1.5 text-xs font-semibold">
                    @forelse($mainMenus as $menu)
                    @if($menu->children->count() > 0)
                    <!-- Parent Menu with Dropdown Children -->
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button" @click="open = !open"
                            class="px-3.5 py-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 transition flex items-center gap-1.5">
                            @if($menu->icon)
                            <i class="bi {{ $menu->icon }} text-blue-400"></i>
                            @endif
                            <span>{{ $menu->title }}</span>
                            @if($menu->badge)
                            <span
                                class="bg-blue-600 text-white text-[9px] px-1.5 py-0.2 rounded-full font-bold uppercase">{{
                                $menu->badge }}</span>
                            @endif
                            <i class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
                                :class="open ? 'rotate-180 text-blue-400' : ''"></i>
                        </button>

                        <!-- Dropdown Submenu Menu Card -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-2"
                            class="absolute left-0 mt-1 w-64 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl py-2 z-50 backdrop-blur-md"
                            style="background-color: #0f172a; border-color: #1e293b;">
                            <div
                                class="px-3 py-1.5 text-[10px] uppercase font-bold tracking-wider text-slate-400 border-b border-slate-800/80 mb-1">
                                {{ $menu->title }}
                            </div>
                            @foreach($menu->children as $child)
                            <a href="{{ $child->resolved_url }}" target="{{ $child->target }}"
                                class="flex items-center justify-between px-3.5 py-2 text-xs text-slate-300 hover:text-white hover:bg-slate-800/80 transition rounded-lg mx-1">
                                <div class="flex items-center gap-2">
                                    @if($child->icon)
                                    <i class="bi {{ $child->icon }} text-blue-400"></i>
                                    @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    @endif
                                    <span>{{ $child->title }}</span>
                                </div>
                                @if($child->badge)
                                <span
                                    class="bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[9px] font-extrabold px-1.5 py-0.5 rounded-full uppercase">
                                    {{ $child->badge }}
                                </span>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <!-- Single Link Menu Item -->
                    @php
                    $isActive = request()->is(trim($menu->url, '/')) || ($menu->route_name &&
                    request()->routeIs($menu->route_name));
                    @endphp
                    <a href="{{ $menu->resolved_url }}" target="{{ $menu->target }}"
                        class="px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 {{ $isActive ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                        @if($menu->icon)
                        <i class="bi {{ $menu->icon }} {{ $isActive ? 'text-white' : 'text-blue-400' }}"></i>
                        @endif
                        <span>{{ $menu->title }}</span>
                        @if($menu->badge)
                        <span
                            class="bg-rose-500 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold uppercase">{{
                            $menu->badge }}</span>
                        @endif
                    </a>
                    @endif
                    @empty
                    <a href="{{ route('home') }}"
                        class="px-3 py-1.5 rounded-lg bg-blue-600 text-white font-bold">Home</a>
                    <a href="{{ route('shop') }}"
                        class="px-3 py-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800">Catalog</a>
                    @endforelse
                </div>

                <!-- Mobile Responsive Drawer (Accordions & Top Utility) -->
                <div x-show="mobileMenuOpen" x-transition
                    class="mobile-menu-drawer md:hidden py-4 border-t border-slate-800 space-y-3" style="display: none;"
                    :style="mobileMenuOpen ? 'display: block !important;' : 'display: none !important;'">
                    <!-- Mobile Search Input -->
                    <form action="{{ route('shop') }}" method="GET" class="mb-4">
                        <div class="relative">
                            <input type="search" name="search" placeholder="Search computer parts..."
                                class="w-full pl-10 pr-4 py-2 bg-slate-800 text-white text-xs rounded-xl border border-slate-700 focus:outline-hidden">
                            <i class="bi bi-search absolute left-3 top-2.5 text-slate-400"></i>
                        </div>
                    </form>

                    <!-- Mobile Main Menu Items -->
                    <div class="space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1">Navigation
                        </div>
                        @foreach($mainMenus as $menu)
                        @if($menu->children->count() > 0)
                        <div x-data="{ openSub: false }">
                            <button @click="openSub = !openSub"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-800 rounded-xl">
                                <span class="flex items-center gap-2">
                                    @if($menu->icon) <i class="bi {{ $menu->icon }} text-blue-400"></i> @endif
                                    {{ $menu->title }}
                                </span>
                                <i class="bi bi-chevron-down text-[10px] transition-transform"
                                    :class="openSub ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-show="openSub" class="pl-4 space-y-1 mt-1 border-l-2 border-slate-800 ml-3">
                                @foreach($menu->children as $child)
                                <a href="{{ $child->resolved_url }}"
                                    class="flex items-center justify-between px-3 py-1.5 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg">
                                    <span>{{ $child->title }}</span>
                                    @if($child->badge)
                                    <span
                                        class="text-[9px] bg-blue-600 text-white px-1.5 py-0.2 rounded-full font-bold">{{
                                        $child->badge }}</span>
                                    @endif
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <a href="{{ $menu->resolved_url }}"
                            class="block px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-800 rounded-xl">
                            @if($menu->icon) <i class="bi {{ $menu->icon }} text-blue-400 me-1.5"></i> @endif
                            {{ $menu->title }}
                        </a>
                        @endif
                        @endforeach
                    </div>

                    <!-- Mobile Top Menu Items (About Us, Contact Us...) -->
                    @if($topMenus->count() > 0)
                    <div class="pt-3 border-t border-slate-800 space-y-1">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-2 mb-1">Company
                            &amp; Support</div>
                        @foreach($topMenus as $tMenu)
                        <a href="{{ $tMenu->resolved_url }}"
                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg">
                            @if($tMenu->icon) <i class="bi {{ $tMenu->icon }} text-blue-400"></i> @endif
                            <span>{{ $tMenu->title }}</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </nav>
    </header>

    <!-- ================= FLOATING TOAST NOTIFICATION CONTAINER ================= -->
    <div x-data="storefrontToaster()" x-init="initToasts()"
        @toast.window="add($event.detail.message, $event.detail.type || 'info', $event.detail.title)"
        class="fixed top-5 right-5 z-[999999] flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4 sm:px-0"
        style="z-index: 999999;" aria-live="polite">
        <template x-for="item in items" :key="item.id">
            <div x-show="item.visible"
                x-transition:enter="transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-500 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-12 scale-90 rotate-1"
                x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100 rotate-0"
                x-transition:leave="transition ease-in duration-250 transform"
                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-16 scale-90" @mouseenter="pause(item.id)"
                @mouseleave="resume(item.id)" :class="{
                     'border-emerald-300/80 bg-white/95 text-gray-900 shadow-2xl shadow-emerald-500/20 ring-1 ring-emerald-500/10': item.type === 'success',
                     'border-rose-300/80 bg-white/95 text-gray-900 shadow-2xl shadow-rose-500/20 ring-1 ring-rose-500/10': item.type === 'error',
                     'border-amber-300/80 bg-white/95 text-gray-900 shadow-2xl shadow-amber-500/20 ring-1 ring-amber-500/10': item.type === 'warning',
                     'border-blue-300/80 bg-white/95 text-gray-900 shadow-2xl shadow-blue-500/20 ring-1 ring-blue-500/10': item.type === 'info'
                 }"
                class="pointer-events-auto relative w-full p-4 rounded-2xl border flex items-start gap-3.5 backdrop-blur-xl transition-all duration-300 overflow-hidden"
                role="alert">

                <!-- Toast Icon with Lively Animated Pop -->
                <div class="shrink-0 mt-0.5">
                    <template x-if="item.type === 'success'">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center font-bold shadow-md shadow-emerald-500/30 toast-icon-pop">
                            <i class="bi bi-check-lg text-xl"></i>
                        </div>
                    </template>
                    <template x-if="item.type === 'error'">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white flex items-center justify-center font-bold shadow-md shadow-rose-500/30 toast-icon-pop">
                            <i class="bi bi-x-lg text-base"></i>
                        </div>
                    </template>
                    <template x-if="item.type === 'warning'">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white flex items-center justify-center font-bold shadow-md shadow-amber-500/30 toast-icon-pop">
                            <i class="bi bi-exclamation-triangle-fill text-sm"></i>
                        </div>
                    </template>
                    <template x-if="item.type === 'info'">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center font-bold shadow-md shadow-blue-500/30 toast-icon-pop">
                            <i class="bi bi-info-circle-fill text-base"></i>
                        </div>
                    </template>
                </div>

                <!-- Toast Message Body -->
                <div class="flex-1 min-w-0 pr-1">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full animate-pulse" :class="{
                                  'bg-emerald-500': item.type === 'success',
                                  'bg-rose-500': item.type === 'error',
                                  'bg-amber-500': item.type === 'warning',
                                  'bg-blue-500': item.type === 'info'
                              }"></span>
                        <h4 class="text-xs font-black uppercase tracking-wider" :class="{
                                'text-emerald-700': item.type === 'success',
                                'text-rose-700': item.type === 'error',
                                'text-amber-700': item.type === 'warning',
                                'text-blue-700': item.type === 'info'
                            }" x-text="item.title"></h4>
                    </div>
                    <p class="text-xs font-semibold text-gray-800 mt-1 leading-relaxed break-words"
                        x-text="item.message"></p>
                </div>

                <!-- Close Button -->
                <button type="button" @click="remove(item.id)"
                    class="shrink-0 text-gray-400 hover:text-gray-700 hover:bg-gray-100 p-1 -mr-1 -mt-1 rounded-xl transition cursor-pointer"
                    aria-label="Dismiss Notification">
                    <i class="bi bi-x text-lg leading-none"></i>
                </button>

                <!-- Animated Countdown Progress Bar -->
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-100 overflow-hidden">
                    <div class="h-full toast-progress-countdown" :class="{
                             'bg-emerald-500': item.type === 'success',
                             'bg-rose-500': item.type === 'error',
                             'bg-amber-500': item.type === 'warning',
                             'bg-blue-500': item.type === 'info'
                         }"></div>
                </div>
            </div>
        </template>
    </div>

    <!-- Global High-End Confirmation Modal (Replaces Browser Default Confirm Dialogs) -->
    <div id="storefront-confirm-modal" class="fixed inset-0 z-[9999] overflow-y-auto hidden"
        aria-labelledby="confirm-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div id="confirm-modal-backdrop"
            class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs transition-opacity duration-200 opacity-0"
            onclick="window.closeConfirmModal(false)"></div>

        <!-- Centered Dialog Card -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div id="confirm-modal-card"
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all duration-200 opacity-0 scale-95 translate-y-2 sm:my-8 sm:w-full sm:max-w-md border border-gray-100 p-6 sm:p-7"
                onclick="event.stopPropagation()">
                <!-- Close Button -->
                <button type="button" onclick="window.closeConfirmModal(false)"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition p-1.5 rounded-xl hover:bg-gray-100 cursor-pointer"
                    aria-label="Close dialog">
                    <i class="bi bi-x text-lg leading-none"></i>
                </button>

                <!-- Icon Badge -->
                <div id="confirm-modal-icon-container"
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-rose-50 text-rose-600 border border-rose-100 ring-4 ring-rose-50/60">
                    <i id="confirm-modal-icon" class="bi bi-trash3-fill text-2xl"></i>
                </div>

                <!-- Modal Content -->
                <div class="text-center">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight" id="confirm-modal-title">Please Confirm
                    </h3>
                    <div class="mt-2">
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed" id="confirm-modal-message">Are you
                            sure you want to proceed?</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" id="confirm-modal-btn-cancel" onclick="window.closeConfirmModal(false)"
                        class="w-full inline-flex justify-center items-center rounded-xl bg-gray-100 px-4 py-2.5 text-xs font-bold text-gray-700 hover:bg-gray-200 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" id="confirm-modal-btn-confirm" onclick="window.closeConfirmModal(true)"
                        class="w-full inline-flex justify-center items-center rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-md transition cursor-pointer bg-rose-600 hover:bg-rose-700 shadow-rose-600/25">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Body -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- ================= 4. STOREFRONT FOOTER & DYNAMIC FOOTER MENUS ================= -->
    <footer class="storefront-footer bg-slate-950 text-slate-400 border-t border-slate-800 mt-20 text-sm"
        style="background-color: #020617; color: #94a3b8;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                <!-- Brand Column (Spans 2 on large screens) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('assets/images/brand/logo/jacksparrow-logo-dark.png') }}"
                            alt="JackSparrow - B2B Computer Parts" class="h-10 sm:h-11 w-auto object-contain">
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        {{ $footerAbout }}
                    </p>
                    <div class="text-xs text-slate-400 space-y-1.5 pt-2">
                        <p class="flex items-center gap-2">
                            <i class="bi bi-geo-alt text-blue-400"></i> {{ $contactAddress }}
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="bi bi-telephone text-blue-400"></i> {{ $contactPhone }}
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="bi bi-envelope text-blue-400"></i> {{ $contactEmail }}
                        </p>
                    </div>
                </div>

                <!-- Dynamic Footer Menus (Generated from Storefront Menu Setup) -->
                @forelse($footerMenus as $footerCol)
                <div>
                    <h3
                        class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-4 pb-1 border-b border-slate-800/80">
                        {{ $footerCol->title }}
                    </h3>
                    <ul class="space-y-2.5 text-xs">
                        @forelse($footerCol->children as $fLink)
                        <li>
                            <a href="{{ $fLink->resolved_url }}" target="{{ $fLink->target }}"
                                class="text-slate-400 hover:text-white hover:underline transition flex items-center gap-1.5">
                                @if($fLink->icon)
                                <i class="bi {{ $fLink->icon }} text-slate-500 text-[11px]"></i>
                                @endif
                                <span>{{ $fLink->title }}</span>
                            </a>
                        </li>
                        @empty
                        <li>
                            <a href="{{ $footerCol->resolved_url }}"
                                class="text-slate-400 hover:text-white hover:underline transition">
                                {{ $footerCol->title }}
                            </a>
                        </li>
                        @endforelse
                    </ul>
                </div>
                @empty
                <!-- Fallback footer links if no footer menus are seeded -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-4">Navigation</h3>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('shop') }}" class="hover:text-white transition">Catalog</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white transition">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                @endforelse
            </div>

            <!-- Payment & Security Strip -->
            <div
                class="mt-12 pt-8 border-t border-slate-800/80 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400 font-semibold">
                    <span class="text-slate-500 text-[11px] uppercase tracking-wider me-1">Guaranteed Safe
                        Checkout:</span>
                    <span class="px-2.5 py-1 bg-slate-900 rounded border border-slate-800 text-slate-300">Visa /
                        Mastercard</span>
                    <span class="px-2.5 py-1 bg-slate-900 rounded border border-slate-800 text-slate-300">Cash on
                        Delivery</span>
                    <span class="px-2.5 py-1 bg-slate-900 rounded border border-slate-800 text-slate-300">Bank
                        Transfer</span>
                </div>
                <div class="text-xs text-slate-500 text-center md:text-right">
                    <p>&copy; {{ date('Y') }} {{ $footerCopyright }}</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Storefront Toast Notification Alpine Script & Global Helper -->
    <script>
        function storefrontToaster() {
            return {
                items: [],
                timers: {},
                initToasts() {
                    @if (session('success'))
                        this.add(@json(session('success')), 'success', 'Success');
                    @endif
                    @if (session('error'))
                        this.add(@json(session('error')), 'error', 'Notice');
                    @endif
                    @if (session('warning'))
                        this.add(@json(session('warning')), 'warning', 'Warning');
                    @endif
                    @if (session('info'))
                        this.add(@json(session('info')), 'info', 'Information');
                    @endif
                    @if (session('status'))
                        this.add(@json(session('status')), 'info', 'Status');
                    @endif
                },
                add(message, type = 'info', title = null) {
                    if (!message) return;
                    const defaultTitles = {
                        success: 'Success',
                        error: 'Notice',
                        warning: 'Warning',
                        info: 'Information'
                    };
                    const id = Date.now() + Math.random();
                    const item = {
                        id: id,
                        message: message,
                        type: type,
                        title: title || defaultTitles[type] || 'Notification',
                        visible: true
                    };
                    this.items.push(item);
                    this.timers[id] = setTimeout(() => {
                        this.remove(id);
                    }, 4500);
                },
                pause(id) {
                    if (this.timers[id]) {
                        clearTimeout(this.timers[id]);
                        delete this.timers[id];
                    }
                },
                resume(id) {
                    if (!this.timers[id]) {
                        this.timers[id] = setTimeout(() => {
                            this.remove(id);
                        }, 2000);
                    }
                },
                remove(id) {
                    const idx = this.items.findIndex(i => i.id === id);
                    if (idx !== -1) {
                        this.items[idx].visible = false;
                        setTimeout(() => {
                            this.items = this.items.filter(i => i.id !== id);
                            delete this.timers[id];
                        }, 250);
                    }
                }
            };
        }

        window.toast = function (message, type = 'success', title = null) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { message, type, title }
            }));
        };

        let _confirmModalResolve = null;

        window.confirmModal = function (options = {}) {
            return new Promise((resolve) => {
                _confirmModalResolve = resolve;

                const modal = document.getElementById('storefront-confirm-modal');
                const backdrop = document.getElementById('confirm-modal-backdrop');
                const card = document.getElementById('confirm-modal-card');
                const titleElem = document.getElementById('confirm-modal-title');
                const msgElem = document.getElementById('confirm-modal-message');
                const iconContainer = document.getElementById('confirm-modal-icon-container');
                const iconElem = document.getElementById('confirm-modal-icon');
                const confirmBtn = document.getElementById('confirm-modal-btn-confirm');
                const cancelBtn = document.getElementById('confirm-modal-btn-cancel');

                if (!modal) {
                    resolve(window.confirm(options.message || 'Are you sure?'));
                    return;
                }

                if (titleElem) titleElem.textContent = options.title || 'Please Confirm';
                if (msgElem) msgElem.textContent = options.message || 'Are you sure you want to proceed?';
                if (confirmBtn) confirmBtn.textContent = options.confirmText || 'Confirm';
                if (cancelBtn) cancelBtn.textContent = options.cancelText || 'Cancel';

                const isDanger = (options.type || 'danger') === 'danger';
                if (iconContainer && iconElem && confirmBtn) {
                    if (isDanger) {
                        iconContainer.className = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-rose-50 text-rose-600 border border-rose-100 ring-4 ring-rose-50/60';
                        iconElem.className = 'bi bi-trash3-fill text-2xl';
                        confirmBtn.className = 'w-full inline-flex justify-center items-center rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-md transition cursor-pointer bg-rose-600 hover:bg-rose-700 shadow-rose-600/25';
                    } else {
                        iconContainer.className = 'mx-auto flex h-14 w-14 items-center justify-center rounded-2xl mb-4 bg-amber-50 text-amber-600 border border-amber-100 ring-4 ring-amber-50/60';
                        iconElem.className = 'bi bi-exclamation-triangle-fill text-2xl';
                        confirmBtn.className = 'w-full inline-flex justify-center items-center rounded-xl px-4 py-2.5 text-xs font-bold text-white shadow-md transition cursor-pointer bg-blue-600 hover:bg-blue-700 shadow-blue-600/25';
                    }
                }

                modal.classList.remove('hidden');
                requestAnimationFrame(() => {
                    if (backdrop) {
                        backdrop.classList.remove('opacity-0');
                        backdrop.classList.add('opacity-100');
                    }
                    if (card) {
                        card.classList.remove('opacity-0', 'scale-95', 'translate-y-2');
                        card.classList.add('opacity-100', 'scale-100', 'translate-y-0');
                    }
                });
            });
        };

        window.closeConfirmModal = function (result) {
            const modal = document.getElementById('storefront-confirm-modal');
            const backdrop = document.getElementById('confirm-modal-backdrop');
            const card = document.getElementById('confirm-modal-card');

            if (backdrop && card) {
                backdrop.classList.remove('opacity-100');
                backdrop.classList.add('opacity-0');
                card.classList.remove('opacity-100', 'scale-100', 'translate-y-0');
                card.classList.add('opacity-0', 'scale-95', 'translate-y-2');
            }

            setTimeout(() => {
                if (modal) {
                    modal.classList.add('hidden');
                }
                if (_confirmModalResolve) {
                    _confirmModalResolve(Boolean(result));
                    _confirmModalResolve = null;
                }
            }, 200);
        };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('storefront-confirm-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    window.closeConfirmModal(false);
                }
            }
        });

        window.updateCartBadge = function (count) {
            const badge = document.getElementById('storefront-cart-badge');
            const countElem = document.getElementById('storefront-cart-count');
            const cartBtn = document.getElementById('storefront-cart-button');

            if (countElem) {
                countElem.textContent = count;
            }

            if (badge) {
                if (count > 0) {
                    badge.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
                    badge.classList.add('scale-100', 'opacity-100');
                } else {
                    badge.classList.remove('scale-100', 'opacity-100');
                    badge.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
                }
            }

            if (cartBtn) {
                cartBtn.classList.add('scale-110', 'bg-blue-100');
                setTimeout(() => {
                    cartBtn.classList.remove('scale-110', 'bg-blue-100');
                }, 350);
            }
        };

        window.updateFavoriteBadge = function (count) {
            const badge = document.getElementById('storefront-favorite-badge');
            const countElem = document.getElementById('storefront-favorite-count');
            const favBtn = document.getElementById('storefront-favorite-button');

            if (countElem) {
                countElem.textContent = count;
            }

            if (badge) {
                if (count > 0) {
                    badge.classList.remove('scale-0', 'opacity-0', 'pointer-events-none');
                    badge.classList.add('scale-100', 'opacity-100');
                } else {
                    badge.classList.remove('scale-100', 'opacity-100');
                    badge.classList.add('scale-0', 'opacity-0', 'pointer-events-none');
                }
            }

            if (favBtn) {
                favBtn.classList.add('scale-110', 'bg-rose-100');
                setTimeout(() => {
                    favBtn.classList.remove('scale-110', 'bg-rose-100');
                }, 350);
            }
        };

        window.toggleFavorite = function (productId, btn, callback) {
            const icon = btn ? btn.querySelector('i') : null;
            if (btn) {
                btn.disabled = true;
                btn.classList.add('scale-95');
            }

            fetch('{{ url('/favorites/toggle') }}/' + productId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
                .then(async (response) => {
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('scale-95');
                    }
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && data.success) {
                        if (typeof data.favorite_count !== 'undefined') {
                            window.updateFavoriteBadge(data.favorite_count);
                        }

                        // Synchronize any matching heart buttons across the page
                        document.querySelectorAll('[data-product-id="' + productId + '"]').forEach(el => {
                            const targetIcon = el.querySelector('i');
                            if (targetIcon) {
                                if (data.is_favorited) {
                                    targetIcon.className = 'bi bi-heart-fill text-rose-600';
                                    el.classList.add('text-rose-600');
                                    el.classList.remove('text-slate-400');
                                } else {
                                    targetIcon.className = 'bi bi-heart';
                                    el.classList.remove('text-rose-600');
                                    el.classList.add('text-slate-400');
                                }
                            }
                        });

                        if (typeof window.toast === 'function') {
                            window.toast(data.message, data.is_favorited ? 'success' : 'info', data.is_favorited ? 'Wishlist' : 'Favorites');
                        }

                        if (typeof callback === 'function') {
                            callback(data);
                        }
                    } else {
                        if (typeof window.toast === 'function') {
                            window.toast(data.message || 'Could not update favorites.', 'error', 'Notice');
                        }
                    }
                })
                .catch(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.classList.remove('scale-95');
                    }
                    if (typeof window.toast === 'function') {
                        window.toast('Network error updating favorites.', 'error', 'Error');
                    }
                });
        };

        // Universal AJAX Add-to-Cart without Page Reload
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || !form.action || !form.action.includes('/cart/add/')) {
                return;
            }

            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]') || e.submitter;
            const originalContent = submitBtn ? submitBtn.innerHTML : null;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-75');
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 inline text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Adding...</span>
                `;
            }

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: formData
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && data.success) {
                        if (typeof data.cart_count !== 'undefined') {
                            window.updateCartBadge(data.cart_count);
                        }
                        window.toast(data.message || 'Added to your cart!', 'success', 'Cart Updated');

                        if (submitBtn) {
                            submitBtn.innerHTML = `
                            <i class="bi bi-check-lg mr-1 text-base"></i>
                            <span>Added!</span>
                        `;
                            setTimeout(() => {
                                submitBtn.disabled = false;
                                submitBtn.classList.remove('opacity-75');
                                submitBtn.innerHTML = originalContent;
                            }, 1200);
                        }
                    } else {
                        const errorMsg = data.message || 'Could not add product to cart.';
                        window.toast(errorMsg, 'error', 'Notice');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-75');
                            submitBtn.innerHTML = originalContent;
                        }
                    }
                })
                .catch(() => {
                    window.toast('An error occurred while adding to cart.', 'error', 'Error');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-75');
                        submitBtn.innerHTML = originalContent;
                    }
                });
        });
    </script>
    @stack('scripts')
</body>

</html>