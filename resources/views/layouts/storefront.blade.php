<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'JackSparow TECH - High-Performance Computer Parts & Hardware')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 min-h-full flex flex-col">
    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Fast Express Shipping on orders over $150
                </span>
                <span class="hidden sm:inline text-slate-700">|</span>
                <span class="hidden sm:inline text-slate-400">100% Genuine Components & Official Brand Warranty</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Support: +1 (800) 555-PC-RIG</span>
                @if(Route::has('admin.dashboard') && auth()->check() && auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="text-amber-400 hover:text-amber-300 font-bold flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Admin Portal
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                    <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <div>
                        <span class="text-xl font-extrabold tracking-tight text-gray-900 flex items-center gap-1.5">
                            JackSparow <span class="text-blue-600 font-extrabold">TECH</span>
                        </span>
                        <span class="block text-[10px] text-gray-500 font-bold tracking-wider uppercase">Computer Hardware & Parts</span>
                    </div>
                </a>

                <!-- Search Bar -->
                <form action="{{ route('shop') }}" method="GET" class="hidden sm:flex flex-1 max-w-xl mx-4">
                    <div class="relative w-full">
                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search CPUs, GPUs, RAM, Motherboards, NVMe SSDs..."
                            class="w-full pl-11 pr-24 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="absolute inset-y-1.5 right-1.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition shadow-xs">
                            Search
                        </button>
                    </div>
                </form>

                <!-- Actions: User & Cart -->
                <div class="flex items-center gap-3 shrink-0">
                    <!-- Shop Catalog Link -->
                    <a href="{{ route('shop') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-gray-700 hover:text-blue-600 transition px-3 py-2 rounded-xl hover:bg-gray-100">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        Catalog
                    </a>

                    <!-- User Account -->
                    @if(auth('frontend')->check())
                        @php $customerUser = auth('frontend')->user(); @endphp
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" type="button" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition p-1.5 rounded-xl hover:bg-gray-100">
                                <span class="w-8 h-8 rounded-full {{ $customerUser->customer_type === 'special' ? 'bg-amber-100 text-amber-700 font-black ring-2 ring-amber-400' : 'bg-blue-100 text-blue-700 font-bold' }} flex items-center justify-center text-xs">
                                    {{ substr($customerUser->name, 0, 1) }}
                                </span>
                                <span class="hidden lg:inline text-left">
                                    <span class="block text-[11px] font-semibold {{ $customerUser->customer_type === 'special' ? 'text-amber-600' : 'text-gray-500' }}">
                                        {{ $customerUser->customer_type === 'special' ? '⭐ Special VIP' : 'Customer' }}
                                    </span>
                                    <span class="block font-bold -mt-0.5 text-xs text-gray-900 truncate max-w-24">{{ $customerUser->name }}</span>
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="open" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-xs font-bold text-gray-900">{{ $customerUser->name }}</p>
                                    <p class="text-[11px] text-gray-500 truncate">{{ $customerUser->email }}</p>
                                    <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $customerUser->customer_type === 'special' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                        {{ $customerUser->customer_type === 'special' ? '⭐ Special Customer' : 'Simple Customer' }}
                                    </span>
                                </div>
                                <a href="{{ route('my-orders') }}" class="block px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 hover:text-blue-600">My Orders</a>
                                <hr class="my-1 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Log Out</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-1">
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-gray-700 hover:text-blue-600 transition px-3 py-2 rounded-xl hover:bg-gray-100">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Sign In</span>
                            </a>
                            <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 transition px-3 py-2 rounded-xl">
                                Register
                            </a>
                        </div>
                    @endif

                    <!-- Cart Button with Live Count -->
                    @php
                        $cartItems = session('cart', []);
                        $cartCount = array_sum(array_column($cartItems, 'quantity'));
                    @endphp
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center justify-center p-2.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-blue-600 text-white font-black text-[11px] w-5 h-5 rounded-full flex items-center justify-center shadow-xs">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Category Navigation Bar -->
        <nav class="bg-slate-900 text-slate-200 border-t border-slate-800 shadow-inner">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-1 py-2 overflow-x-auto whitespace-nowrap text-xs font-semibold scrollbar-none">
                <a href="{{ route('shop') }}" class="px-3 py-1.5 rounded-lg {{ !request('category') && request()->routeIs('shop') ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    All Parts
                </a>
                <a href="{{ route('shop', ['category' => 'processors-cpu']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'processors-cpu' ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Processors (CPU)</a>
                <a href="{{ route('shop', ['category' => 'graphics-cards-gpu']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'graphics-cards-gpu' ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Graphics Cards (GPU)</a>
                <a href="{{ route('shop', ['category' => 'motherboards']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'motherboards' ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Motherboards</a>
                <a href="{{ route('shop', ['category' => 'memory-ram']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'memory-ram' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">RAM / Memory</a>
                <a href="{{ route('shop', ['category' => 'storage-ssd-hdd']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'storage-ssd-hdd' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Storage (SSD)</a>
                <a href="{{ route('shop', ['category' => 'power-supplies-psu']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'power-supplies-psu' ? 'bg-blue-600 text-white font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Power Supplies</a>
                <a href="{{ route('shop', ['category' => 'cooling-systems']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'cooling-systems' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Cooling</a>
                <a href="{{ route('shop', ['category' => 'pc-cases']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'pc-cases' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">PC Cases</a>
                <a href="{{ route('shop', ['category' => 'monitors']) }}" class="px-3 py-1.5 rounded-lg {{ request('category') === 'monitors' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }} transition">Monitors</a>
            </div>
        </nav>
    </header>

    <!-- Global Flash Notification Banner -->
    @if(session('success'))
        <div class="global-flash-banner bg-emerald-600 text-white px-4 py-3 text-xs sm:text-sm font-semibold shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.closest('.global-flash-banner').remove()" class="text-white/80 hover:text-white text-lg leading-none">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="global-flash-banner bg-rose-600 text-white px-4 py-3 text-xs sm:text-sm font-semibold shadow-sm transition-all duration-300">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button onclick="this.closest('.global-flash-banner').remove()" class="text-white/80 hover:text-white text-lg leading-none">&times;</button>
            </div>
        </div>
    @endif

    @if(session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.global-flash-banner').forEach(function (banner) {
                    setTimeout(function () {
                        banner.style.opacity = '0';
                        banner.style.maxHeight = '0';
                        banner.style.paddingTop = '0';
                        banner.style.paddingBottom = '0';
                        setTimeout(function () { banner.remove(); }, 350);
                    }, 3000);
                });
            });
        </script>
    @endif

    <!-- Main Content Body -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Storefront Footer -->
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-16 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Brand Column -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-black text-sm">
                            JS
                        </div>
                        <span class="text-lg font-bold text-white">JackSparow TECH</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">
                        Your trusted destination for custom gaming computer parts, next-gen CPUs, GPUs, high-speed RAM, and extreme PC cooling systems.
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <p>📍 100 Technology Plaza, Silicon Way</p>
                        <p>📞 Phone: +1 (800) 555-PC-RIG</p>
                        <p>✉️ Email: support@jacksparow-tech.test</p>
                    </div>
                </div>

                <!-- Hardware Categories -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3">Popular Hardware</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('shop', ['category' => 'processors-cpu']) }}" class="hover:text-white transition">Processors (Intel & AMD)</a></li>
                        <li><a href="{{ route('shop', ['category' => 'graphics-cards-gpu']) }}" class="hover:text-white transition">Graphics Cards (RTX 40 Series)</a></li>
                        <li><a href="{{ route('shop', ['category' => 'motherboards']) }}" class="hover:text-white transition">Motherboards (AM5 & Z790)</a></li>
                        <li><a href="{{ route('shop', ['category' => 'memory-ram']) }}" class="hover:text-white transition">High-Speed DDR5 Memory</a></li>
                        <li><a href="{{ route('shop', ['category' => 'storage-ssd-hdd']) }}" class="hover:text-white transition">PCIe 4.0 & 5.0 NVMe SSDs</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3">Customer Service</h3>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('cart.index') }}" class="hover:text-white transition">View Shopping Cart</a></li>
                        <li><a href="{{ route('my-orders') }}" class="hover:text-white transition">Track Your Order</a></li>
                        <li><a href="{{ route('shop') }}" class="hover:text-white transition">PC Builder Hardware Guide</a></li>
                        <li><a href="{{ route('shop') }}" class="hover:text-white transition">Warranty & Compatibility Support</a></li>
                    </ul>
                </div>

                <!-- Payment & Trust -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-3">Guaranteed Safe Checkout</h3>
                    <p class="text-xs text-slate-400 mb-3">
                        We support multiple payment methods including Credit Cards, Cash on Delivery, and Secure Bank Transfers.
                    </p>
                    <div class="flex flex-wrap gap-2 text-xs text-slate-300 font-semibold">
                        <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">Visa / MC</span>
                        <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">Cash on Delivery</span>
                        <span class="px-2.5 py-1 bg-slate-800 rounded border border-slate-700">Bank Transfer</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} JackSparow TECH E-Commerce. All rights reserved.</p>
                <p>Designed for Computer Hardware Enthusiasts & PC Gamers.</p>
            </div>
        </div>
    </footer>
</body>
</html>
