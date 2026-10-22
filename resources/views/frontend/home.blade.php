@extends('layouts.storefront')

@section('title', 'JackSparow TECH - High-Performance Computer Parts & Gaming Hardware')

@section('content')
@php
    $heroBadge = \App\Models\WebsiteSetting::get('hero_badge', 'Next-Gen PC Hardware In Stock');
    $heroTitle = \App\Models\WebsiteSetting::get('hero_title', 'Build Your Ultimate Gaming Rig');
    $heroSubtitle = \App\Models\WebsiteSetting::get('hero_subtitle', 'Explore top-tier desktop processors, NVIDIA RTX 40-Series graphics cards, blazing fast Gen4 NVMe storage, and high-frequency DDR5 memory with official warranty.');
    $heroPrimaryBtnText = \App\Models\WebsiteSetting::get('hero_primary_btn_text', 'Browse All Components');
    $heroPrimaryBtnUrl = \App\Models\WebsiteSetting::get('hero_primary_btn_url', '/shop');
    $heroSecondaryBtnText = \App\Models\WebsiteSetting::get('hero_secondary_btn_text', 'Shop GPUs');
    $heroSecondaryBtnUrl = \App\Models\WebsiteSetting::get('hero_secondary_btn_url', '/shop?category=graphics-cards-gpu');

    $feature1Icon = \App\Models\WebsiteSetting::get('feature_1_icon', '⚡');
    $feature1Title = \App\Models\WebsiteSetting::get('feature_1_title', 'Flagship Processors');
    $feature1Desc = \App\Models\WebsiteSetting::get('feature_1_desc', 'AMD Ryzen 7000X3D & Intel 14th Gen Core i9 ready for max FPS and intense rendering.');

    $feature2Icon = \App\Models\WebsiteSetting::get('feature_2_icon', '🎮');
    $feature2Title = \App\Models\WebsiteSetting::get('feature_2_title', 'RTX 40 SUPER GPUs');
    $feature2Desc = \App\Models\WebsiteSetting::get('feature_2_desc', 'Ray tracing, DLSS 3 frame generation, and massive 16GB VRAM for 4K ray-traced gaming.');

    $feature3Icon = \App\Models\WebsiteSetting::get('feature_3_icon', '🚀');
    $feature3Title = \App\Models\WebsiteSetting::get('feature_3_title', 'Ultra NVMe SSDs');
    $feature3Desc = \App\Models\WebsiteSetting::get('feature_3_desc', 'Up to 7,450 MB/s read speeds with Samsung 990 Pro & WD_BLACK for instant game loading.');

    $feature4Icon = \App\Models\WebsiteSetting::get('feature_4_icon', '🛡️');
    $feature4Title = \App\Models\WebsiteSetting::get('feature_4_title', 'Official Warranty');
    $feature4Desc = \App\Models\WebsiteSetting::get('feature_4_desc', 'Up to 10-year manufacturer warranty with guaranteed authentic retail hardware packaging.');
@endphp

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-gray-950 via-slate-900 to-indigo-950 text-white overflow-hidden py-16 sm:py-24 border-b border-gray-800">
    <!-- Subtle Circuit / Grid Accent Background -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                @if($heroBadge)
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-semibold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ $heroBadge }}
                    </div>
                @endif
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                    {{ $heroTitle }}
                </h1>
                <p class="text-base sm:text-lg text-gray-300 max-w-2xl leading-relaxed">
                    {{ $heroSubtitle }}
                </p>
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @if($heroPrimaryBtnText)
                        <a href="{{ $heroPrimaryBtnUrl }}" class="px-7 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-500/40 transition flex items-center gap-2">
                            <span>{{ $heroPrimaryBtnText }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                    @if($heroSecondaryBtnText)
                        <a href="{{ $heroSecondaryBtnUrl }}" class="px-6 py-3.5 bg-gray-800/80 hover:bg-gray-700 text-gray-200 font-semibold text-sm rounded-xl border border-gray-700 transition">
                            {{ $heroSecondaryBtnText }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Hero Feature Cards -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-gray-800/50 backdrop-blur-md border border-gray-700/60 space-y-3 hover:border-blue-500/50 hover:bg-gray-800/80 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-black/20">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-lg">
                        {{ $feature1Icon }}
                    </div>
                    <h3 class="font-bold text-white text-base">{{ $feature1Title }}</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">{{ $feature1Desc }}</p>
                </div>

                <div class="p-5 rounded-2xl bg-gray-800/50 backdrop-blur-md border border-gray-700/60 space-y-3 hover:border-indigo-500/50 hover:bg-gray-800/80 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-black/20">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-lg">
                        {{ $feature2Icon }}
                    </div>
                    <h3 class="font-bold text-white text-base">{{ $feature2Title }}</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">{{ $feature2Desc }}</p>
                </div>

                <div class="p-5 rounded-2xl bg-gray-800/50 backdrop-blur-md border border-gray-700/60 space-y-3 hover:border-emerald-500/50 hover:bg-gray-800/80 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-black/20">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-lg">
                        {{ $feature3Icon }}
                    </div>
                    <h3 class="font-bold text-white text-base">{{ $feature3Title }}</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">{{ $feature3Desc }}</p>
                </div>

                <div class="p-5 rounded-2xl bg-gray-800/50 backdrop-blur-md border border-gray-700/60 space-y-3 hover:border-amber-500/50 hover:bg-gray-800/80 transition-all duration-300 transform hover:-translate-y-1 shadow-lg shadow-black/20">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-lg">
                        {{ $feature4Icon }}
                    </div>
                    <h3 class="font-bold text-white text-base">{{ $feature4Title }}</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">{{ $feature4Desc }}</p>
                </div>
            </div>
        </div>

        <!-- High-Tech Metrics & Trust Strip -->
        <div class="mt-12 pt-8 border-t border-gray-800/80 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
            <div class="space-y-1">
                <span class="block text-2xl font-black text-white">5,000+</span>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Custom PCs Shipped</span>
            </div>
            <div class="space-y-1">
                <span class="block text-2xl font-black text-blue-400">100%</span>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Factory Sealed Genuine</span>
            </div>
            <div class="space-y-1">
                <span class="block text-2xl font-black text-emerald-400">&lt; 24h</span>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Fast Courier Dispatch</span>
            </div>
            <div class="space-y-1">
                <span class="block text-2xl font-black text-amber-400">10-Year</span>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Official Warranty Coverage</span>
            </div>
        </div>
    </div>
</section>

<!-- Featured Hardware Products Grid -->
<section id="featured-products" class="bg-white border-b border-gray-200 py-16 scroll-mt-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    Enthusiast Grade Hardware &bull; {{ $featuredProducts->total() }} Featured {{ Str::plural('Product', $featuredProducts->total()) }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mt-1">Featured Components</h2>
                <p class="text-sm text-gray-500 mt-1">Curated top-tier flagship components and high-performance hardware</p>
            </div>
            <a href="{{ route('shop') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                Explore Full Inventory &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($featuredProducts as $product)
                <div class="group bg-white rounded-2xl border border-gray-200/90 hover:border-blue-500 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 flex flex-col justify-between overflow-hidden relative">
                    <!-- Floating Favorite Button -->
                    <button
                        type="button"
                        onclick="window.toggleFavorite({{ $product->id }}, this)"
                        data-product-id="{{ $product->id }}"
                        class="favorite-toggle-btn absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-white/95 backdrop-blur-sm border border-slate-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:scale-110 flex items-center justify-center transition shadow-2xs cursor-pointer"
                        title="Save to favorites"
                        aria-label="Save to favorites"
                    >
                        <i class="bi {{ in_array($product->id, $userFavoriteIds, true) ? 'bi-heart-fill text-rose-600' : 'bi-heart' }} text-xs"></i>
                    </button>

                    <div class="p-4 sm:p-5 flex flex-col flex-grow">
                        <!-- Top Badges Row: Brand, Marketing Tag & Stock Status -->
                        <div class="flex items-center justify-between gap-2 mb-3 pr-8">
                            <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                <span class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider group-hover:text-blue-600 transition truncate">
                                    {{ $product->brand?->name ?? 'Hardware' }}
                                </span>
                                @if(!empty($product->showcase_badge))
                                    @php
                                        $badgeUpper = strtoupper($product->showcase_badge);
                                        $badgeColor = match(true) {
                                            str_contains($badgeUpper, 'HOT') => 'bg-rose-600 text-white',
                                            str_contains($badgeUpper, 'NEW') => 'bg-emerald-600 text-white',
                                            str_contains($badgeUpper, 'SALE') || str_contains($badgeUpper, 'OFF') => 'bg-amber-600 text-white',
                                            str_contains($badgeUpper, 'BEST') || str_contains($badgeUpper, 'TOP') => 'bg-blue-600 text-white',
                                            default => 'bg-indigo-600 text-white',
                                        };
                                    @endphp
                                    <span class="{{ $badgeColor }} text-[9px] font-black uppercase px-2 py-0.5 rounded-full tracking-wide shadow-xs">
                                        {{ $product->showcase_badge }}
                                    </span>
                                @else
                                    <span class="bg-blue-600 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-full tracking-wide shadow-xs">
                                        FEATURED
                                    </span>
                                @endif
                            </div>

                            <!-- Stock Pill with live status dot -->
                            <div class="shrink-0">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200/80 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Out of Stock
                                    </span>
                                @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Only {{ $product->stock_quantity }} Left
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        In Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Product Image Stage with Uniform Aspect Ratio -->
                        <a href="{{ route('product.show', $product->slug) }}" class="block relative my-1">
                            <div class="product-card-img-stage w-full bg-gradient-to-b from-gray-50/80 to-slate-100/50 rounded-xl border border-gray-100 p-3 relative group-hover:bg-blue-50/20 transition-colors" style="height: 175px; max-height: 175px;">
                                @if($product->sale_price)
                                    <span class="absolute top-2.5 left-2.5 z-10 inline-flex items-center gap-1 bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-xs uppercase tracking-wide">
                                        SAVE ${{ number_format($product->price - $product->sale_price, 0) }}
                                    </span>
                                @endif

                                @if($product->primary_image_url)
                                    <img 
                                        src="{{ $product->primary_image_url }}" 
                                        alt="{{ $product->name }}" 
                                        class="p-1 group-hover:scale-108 transition-transform duration-300 ease-out"
                                        style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                        loading="lazy"
                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';"
                                    >
                                @else
                                    <img 
                                        src="{{ asset('assets/images/placeholder-product.svg') }}" 
                                        alt="{{ $product->name }}" 
                                        class="p-1 group-hover:scale-108 transition-transform duration-300 ease-out opacity-80"
                                        style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                        loading="lazy"
                                    >
                                @endif
                            </div>
                        </a>

                        <!-- Category & SKU Meta -->
                        <div class="flex items-center justify-between text-[11px] text-gray-400 font-medium mt-3 mb-1">
                            <span class="truncate">{{ $product->category?->name ?? 'Components' }}</span>
                            <span class="font-mono text-gray-400 text-[10px] shrink-0">SKU: {{ $product->sku }}</span>
                        </div>

                        <!-- Clamped Title for Uniform Row Alignment -->
                        <h3 class="font-bold text-gray-900 text-sm leading-snug group-hover:text-blue-600 transition h-10 line-clamp-2" title="{{ $product->name }}">
                            <a href="{{ route('product.show', $product->slug) }}">
                                {{ $product->showcase_custom_title ?? $product->name }}
                            </a>
                        </h3>

                        <!-- Specifications Micro-Tags -->
                        @if(!empty($product->specifications))
                            <div class="mt-2.5 flex flex-wrap gap-1.5 min-h-[26px]">
                                @php $specCount = 0; @endphp
                                @foreach($product->specifications as $key => $val)
                                    @if($specCount < 2)
                                        <span class="text-[10px] font-medium bg-gray-100 text-gray-700 border border-gray-200/70 px-2 py-0.5 rounded-md">
                                            <span class="text-gray-400">{{ $key }}:</span> <strong class="text-gray-800 font-semibold">{{ $val }}</strong>
                                        </span>
                                        @php $specCount++; @endphp
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="min-h-[26px]"></div>
                        @endif
                    </div>

                    <!-- Bottom Price & Action Footer -->
                    <div class="p-4 sm:p-5 pt-0 mt-auto border-t border-gray-100 bg-gray-50/50">
                        <div class="flex items-baseline justify-between gap-2 pt-3 mb-3">
                            <div class="flex items-baseline gap-2">
                                @if($product->sale_price)
                                    <span class="text-xl font-black text-gray-900 tracking-tight">
                                        ${{ number_format($product->sale_price, 2) }}
                                    </span>
                                    <span class="text-xs text-gray-400 line-through">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                @else
                                    <span class="text-xl font-black text-gray-900 tracking-tight">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                @endif
                            </div>

                            @if($product->sale_price && $product->price > 0)
                                @php $pct = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                                <span class="text-[10px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200/80 px-1.5 py-0.5 rounded-md">
                                    -{{ $pct }}%
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            <a href="{{ route('product.show', $product->slug) }}" class="col-span-2 py-2 px-3 text-xs font-bold text-gray-700 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-xl transition flex items-center justify-center border border-gray-300 shadow-2xs" title="View Full Specs">
                                Details
                            </a>
                            <form action="{{ route('cart.add', $product->id) }}" method="POST" class="col-span-3">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button
                                    type="submit"
                                    @if($product->stock_quantity <= 0) disabled @endif
                                    class="w-full py-2 px-3 text-xs font-bold text-white rounded-xl transition flex items-center justify-center gap-1.5 shadow-xs active:scale-[0.98] {{ $product->stock_quantity > 0 ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20' : 'bg-gray-300 cursor-not-allowed text-gray-500' }}"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    No components found. Run database seeders to populate hardware inventory.
                </div>
            @endforelse
        </div>

        @if($featuredProducts->hasPages())
            <div class="mt-12 pt-6 border-t border-gray-100">
                {{ $featuredProducts->links('vendor.pagination.storefront') }}
            </div>
        @endif
    </div>
</section>

@if($latestDeals->isNotEmpty())
<!-- Flash Deals & Clearance Showcase -->
<section class="bg-gradient-to-b from-gray-50 via-slate-50/60 to-white py-16 sm:py-20 border-b border-gray-200/80 overflow-hidden relative" x-data="{
    canScrollLeft: false,
    canScrollRight: true,
    scrollProgress: 20,
    currentIndex: 1,
    totalItems: {{ $latestDeals->count() }},
    init() {
        this.updateButtons();
    },
    updateButtons() {
        const el = this.$refs.dealTrack;
        if (!el) return;
        this.canScrollLeft = el.scrollLeft > 10;
        this.canScrollRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 10;
        const maxScroll = el.scrollWidth - el.clientWidth;
        const pct = maxScroll > 0 ? (el.scrollLeft / maxScroll) : 0;
        this.scrollProgress = Math.max(15, Math.min(100, Math.round(pct * 100)));
        const cardWidth = el.firstElementChild?.clientWidth || 220;
        this.currentIndex = Math.min(this.totalItems, Math.max(1, Math.round(el.scrollLeft / cardWidth) + 1));
    },
    scrollLeft() {
        const el = this.$refs.dealTrack;
        const step = (el.firstElementChild?.clientWidth || 220) + 16;
        el.scrollBy({ left: -step, behavior: 'smooth' });
    },
    scrollRight() {
        const el = this.$refs.dealTrack;
        const step = (el.firstElementChild?.clientWidth || 220) + 16;
        el.scrollBy({ left: step, behavior: 'smooth' });
    }
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header: Title & View All Link -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 border border-rose-200/80 text-rose-600 text-xs font-black uppercase tracking-wider mb-2">
                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                    Limited-Time Hardware Discounts
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Flash Deals &amp; Clearance</span>
                    <span class="text-2xl">🔥</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">Exclusive limited-time markdowns and clearance components with instant savings</p>
            </div>

            <div class="flex items-center">
                <a href="{{ route('shop') }}" class="text-xs sm:text-sm font-bold text-rose-600 hover:text-rose-700 inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-rose-200/90 hover:bg-rose-50 hover:border-rose-300 transition shadow-xs group">
                    <span>View All Deals</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </a>
            </div>
        </div>

        @if($topBrands->whereNotNull('logo')->isNotEmpty())
            <!-- Quick Brand Logos Strip -->
            <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-4 no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400 shrink-0 me-1">Top Brands:</span>
                @foreach($topBrands->whereNotNull('logo')->take(8) as $quickBrand)
                    <a href="{{ route('shop', ['brand' => $quickBrand->slug]) }}" class="shrink-0 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-gray-200/90 hover:border-rose-400 hover:shadow-xs text-xs font-bold text-gray-700 hover:text-rose-600 transition group" title="Shop {{ $quickBrand->name }}">
                        @if($quickBrand->logo_url)
                            <img src="{{ $quickBrand->logo_url }}" alt="{{ $quickBrand->name }}" class="h-3.5 w-auto max-w-[40px] object-contain group-hover:scale-105 transition-transform">
                        @endif
                        <span>{{ $quickBrand->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Carousel Track with Relative Container & Floating Arrows -->
        <div class="relative group/track">
            <!-- Floating Left Navigation Arrow -->
            <button 
                @click="scrollLeft()" 
                :disabled="!canScrollLeft"
                type="button" 
                class="hidden md:flex absolute -left-4 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-white/95 backdrop-blur-md border border-gray-200 text-gray-800 hover:text-rose-600 hover:border-rose-400 hover:scale-110 shadow-xl items-center justify-center transition-all duration-200 cursor-pointer disabled:opacity-30 disabled:scale-100 disabled:cursor-not-allowed disabled:hover:text-gray-800 disabled:hover:border-gray-200" 
                aria-label="Previous slide"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Floating Right Navigation Arrow -->
            <button 
                @click="scrollRight()" 
                :disabled="!canScrollRight"
                type="button" 
                class="hidden md:flex absolute -right-4 top-1/2 -translate-y-1/2 z-30 w-11 h-11 rounded-full bg-white/95 backdrop-blur-md border border-gray-200 text-gray-800 hover:text-rose-600 hover:border-rose-400 hover:scale-110 shadow-xl items-center justify-center transition-all duration-200 cursor-pointer disabled:opacity-30 disabled:scale-100 disabled:cursor-not-allowed disabled:hover:text-gray-800 disabled:hover:border-gray-200" 
                aria-label="Next slide"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- The Scrollable Track -->
            <div 
                x-ref="dealTrack" 
                @scroll.passive="updateButtons()"
                class="flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory py-2 no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0" 
                style="scrollbar-width: none; -ms-overflow-style: none;"
            >
                @foreach($latestDeals as $dealProd)
                    <div class="deal-card-5col w-[220px] sm:w-[240px] md:w-[calc(33.333%-11px)] lg:w-[calc(20%-12.8px)] shrink-0 snap-start bg-white rounded-3xl border border-gray-200/90 hover:border-rose-500 hover:shadow-xl hover:shadow-rose-500/10 transition-all duration-300 flex flex-col justify-between overflow-hidden relative group/card">
                        <!-- Floating Favorite Button -->
                        <button
                            type="button"
                            onclick="window.toggleFavorite({{ $dealProd->id }}, this)"
                            data-product-id="{{ $dealProd->id }}"
                            class="favorite-toggle-btn absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-white/95 backdrop-blur-sm border border-gray-200 text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:scale-110 flex items-center justify-center transition shadow-2xs cursor-pointer"
                            title="Save to favorites"
                            aria-label="Save to favorites"
                        >
                            <i class="bi {{ in_array($dealProd->id, $userFavoriteIds, true) ? 'bi-heart-fill text-rose-600' : 'bi-heart' }} text-xs"></i>
                        </button>

                        <div class="p-3.5 sm:p-4 flex flex-col flex-grow">
                            <!-- Top Brand & Stock Pill Row -->
                            <div class="flex items-center justify-between gap-2 mb-2.5 pr-8">
                                <span class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider group-hover/card:text-rose-600 transition truncate">
                                    {{ $dealProd->brand?->name ?? 'Hardware' }}
                                </span>

                                <div class="shrink-0">
                                    @if($dealProd->stock_quantity <= 0)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200/80 px-2 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Out of Stock
                                        </span>
                                    @elseif($dealProd->stock_quantity <= $dealProd->low_stock_threshold)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200/80 px-2 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Only {{ $dealProd->stock_quantity }} Left
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200/80 px-2 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            In Stock
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Product Image Stage (Compact Height) -->
                            <a href="{{ route('product.show', $dealProd->slug) }}" class="block relative my-1">
                                <div class="product-card-img-stage w-full rounded-xl border border-gray-100 bg-gradient-to-b from-slate-50 to-gray-100/70 p-2 relative group-hover/card:bg-rose-50/20 transition-colors" style="height: 135px; max-height: 135px;">
                                    @if($dealProd->sale_price && $dealProd->price > $dealProd->sale_price)
                                        <span class="absolute top-2 left-2 z-10 inline-flex items-center gap-1 bg-gradient-to-r from-rose-600 to-red-500 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-xs uppercase tracking-wider">
                                            SAVE ${{ number_format($dealProd->price - $dealProd->sale_price, 0) }}
                                        </span>
                                    @endif

                                    @if($dealProd->primary_image_url)
                                        <img 
                                            src="{{ $dealProd->primary_image_url }}" 
                                            alt="{{ $dealProd->name }}" 
                                            class="p-1 group-hover/card:scale-108 transition-transform duration-300 ease-out"
                                            style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                            loading="lazy"
                                            onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';"
                                        >
                                    @else
                                        <img 
                                            src="{{ asset('assets/images/placeholder-product.svg') }}" 
                                            alt="{{ $dealProd->name }}" 
                                            class="p-1 group-hover/card:scale-108 transition-transform duration-300 ease-out opacity-80"
                                            style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain;"
                                            loading="lazy"
                                        >
                                    @endif
                                </div>
                            </a>

                            <!-- Category & SKU Meta -->
                            <div class="flex items-center justify-between text-[10px] text-gray-400 font-medium mt-2 mb-1">
                                <span class="truncate">{{ $dealProd->category?->name ?? 'Components' }}</span>
                                <span class="font-mono text-gray-400 text-[9px] shrink-0">SKU: {{ $dealProd->sku }}</span>
                            </div>

                            <!-- Clamped Title -->
                            <h3 class="font-bold text-gray-900 text-xs sm:text-sm leading-snug group-hover/card:text-rose-600 transition h-9 line-clamp-2" title="{{ $dealProd->name }}">
                                <a href="{{ route('product.show', $dealProd->slug) }}">
                                    {{ $dealProd->showcase_custom_title ?? $dealProd->name }}
                                </a>
                            </h3>
                        </div>

                        <!-- Bottom Price & Action Footer -->
                        <div class="p-3 sm:p-3.5 pt-0 mt-auto border-t border-gray-100 bg-gray-50/50">
                            <div class="flex items-baseline justify-between gap-1.5 pt-2 mb-2">
                                <div class="flex items-baseline gap-1.5">
                                    @if($dealProd->sale_price)
                                        <span class="text-lg font-black text-gray-900 tracking-tight">
                                            ${{ number_format($dealProd->sale_price, 2) }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 line-through">
                                            ${{ number_format($dealProd->price, 2) }}
                                        </span>
                                    @else
                                        <span class="text-lg font-black text-gray-900 tracking-tight">
                                            ${{ number_format($dealProd->price, 2) }}
                                        </span>
                                    @endif
                                </div>

                                @if($dealProd->sale_price && $dealProd->price > 0)
                                    @php $pct = round((($dealProd->price - $dealProd->sale_price) / $dealProd->price) * 100); @endphp
                                    <span class="text-[9px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200/80 px-1.5 py-0.5 rounded">
                                        -{{ $pct }}%
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-5 gap-1.5">
                                <a href="{{ route('product.show', $dealProd->slug) }}" class="col-span-2 py-1.5 px-2 text-xs font-bold text-gray-700 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-lg transition flex items-center justify-center border border-gray-300 shadow-2xs" title="View Full Specs">
                                    Details
                                </a>
                                <form action="{{ route('cart.add', $dealProd->id) }}" method="POST" class="col-span-3">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button
                                        type="submit"
                                        @if($dealProd->stock_quantity <= 0) disabled @endif
                                        class="w-full py-1.5 px-2 text-xs font-bold text-white bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 rounded-lg transition flex items-center justify-center gap-1 shadow-sm shadow-rose-600/25 active:scale-[0.98] cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Carousel Track Progress Bar Indicator -->
        <div class="mt-6 flex items-center justify-center gap-3">
            <div class="w-28 h-1.5 rounded-full bg-gray-200 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-rose-500 to-red-600 rounded-full transition-all duration-300" :style="`width: ${scrollProgress}%;`"></div>
            </div>
            <span class="text-[11px] font-bold text-gray-400">
                <span x-text="currentIndex">1</span> of {{ $latestDeals->count() }} deals
            </span>
        </div>
    </div>
</section>
@endif

@if($topBrands->isNotEmpty())
<!-- Authorized Component Brands Showcase -->
<section id="partner-brands" class="bg-white py-16 border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    Certified Hardware Partners
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mt-1">Authorized Component Brands</h2>
                <p class="text-sm text-gray-500 mt-1">Authentic desktop components and hardware directly from world-renowned manufacturers</p>
            </div>
            <div>
                <a href="{{ route('shop') }}" class="text-xs sm:text-sm font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 group">
                    <span>Explore All Brands</span>
                    <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
            @foreach($topBrands as $brand)
                <a 
                    href="{{ route('shop', ['brand' => $brand->slug]) }}" 
                    class="group relative bg-white hover:bg-slate-50/80 rounded-2xl p-4 border border-slate-200/80 hover:border-blue-500 hover:shadow-lg hover:shadow-blue-500/5 transition-all duration-300 flex flex-col items-center justify-between text-center h-36"
                    title="Browse {{ $brand->name }} components"
                >
                    <div class="h-16 w-full flex items-center justify-center p-1.5 overflow-hidden">
                        @if($brand->logo_url)
                            <img 
                                src="{{ $brand->logo_url }}" 
                                alt="{{ $brand->name }} Logo" 
                                class="max-h-10 max-w-[100px] w-auto h-auto object-contain transition-all duration-300 group-hover:scale-105"
                                style="max-height: 40px; max-width: 100px; width: auto; height: auto;"
                                loading="lazy"
                            >
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 text-slate-700 font-bold text-sm flex items-center justify-center shadow-xs group-hover:border-blue-400 group-hover:text-blue-600 transition-colors">
                                {{ strtoupper(substr($brand->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <div class="w-full pt-2 border-t border-slate-100/80">
                        <span class="block text-xs font-semibold text-gray-900 group-hover:text-blue-600 transition-colors truncate">
                            {{ $brand->name }}
                        </span>
                        <span class="block text-[11px] text-gray-400 group-hover:text-gray-500 transition-colors mt-0.5 truncate">
                            @if($brand->products_count > 0)
                                {{ $brand->products_count }} {{ Str::plural('item', $brand->products_count) }}
                            @else
                                Authorized
                            @endif
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#featured-products nav[role="navigation"] a');
        if (!pageLink) return;
        
        e.preventDefault();
        const url = pageLink.href;
        const container = document.getElementById('featured-products');
        if (!container) {
            window.location.href = url;
            return;
        }

        container.classList.add('opacity-50', 'pointer-events-none', 'transition-opacity');
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.text();
        })
        .then(function(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContainer = doc.getElementById('featured-products');
            if (newContainer) {
                container.innerHTML = newContainer.innerHTML;
                window.history.pushState({}, '', url);
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.location.href = url;
            }
        })
        .catch(function() {
            window.location.href = url;
        })
        .finally(function() {
            container.classList.remove('opacity-50', 'pointer-events-none');
        });
    });
});
</script>
@endsection

