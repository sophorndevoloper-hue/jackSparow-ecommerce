@extends('layouts.storefront')

@section('title', 'JackSparow TECH - High-Performance Computer Parts & Gaming Hardware')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-gray-950 via-slate-900 to-indigo-950 text-white overflow-hidden py-16 sm:py-24 border-b border-gray-800">
    <!-- Subtle Circuit / Grid Accent Background -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#3b82f6_1px,transparent_1px)] [background-size:16px_16px]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-semibold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Next-Gen PC Hardware In Stock
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                    Build Your Ultimate <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-cyan-400">Gaming Rig</span>
                </h1>
                <p class="text-base sm:text-lg text-gray-300 max-w-2xl leading-relaxed">
                    Explore top-tier desktop processors, NVIDIA RTX 40-Series graphics cards, blazing fast Gen4 NVMe storage, and high-frequency DDR5 memory with official warranty.
                </p>
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <a href="{{ route('shop') }}" class="px-7 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-500/40 transition flex items-center gap-2">
                        <span>Browse All Components</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('shop', ['category' => 'graphics-cards-gpu']) }}" class="px-6 py-3.5 bg-gray-800/80 hover:bg-gray-700 text-gray-200 font-semibold text-sm rounded-xl border border-gray-700 transition">
                        Shop GPUs
                    </a>
                </div>
            </div>

            <!-- Hero Feature Cards -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-gray-800/60 backdrop-blur-sm border border-gray-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">
                        ⚡
                    </div>
                    <h3 class="font-bold text-white text-base">Flagship Processors</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">AMD Ryzen 7000X3D & Intel 14th Gen Core i9 ready for max FPS and intense rendering.</p>
                </div>

                <div class="p-5 rounded-2xl bg-gray-800/60 backdrop-blur-sm border border-gray-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                        🎮
                    </div>
                    <h3 class="font-bold text-white text-base">RTX 40 SUPER GPUs</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">Ray tracing, DLSS 3 frame generation, and massive 16GB VRAM for 4K ray-traced gaming.</p>
                </div>

                <div class="p-5 rounded-2xl bg-gray-800/60 backdrop-blur-sm border border-gray-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                        🚀
                    </div>
                    <h3 class="font-bold text-white text-base">Ultra NVMe SSDs</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">Up to 7,450 MB/s read speeds with Samsung 990 Pro & WD_BLACK for instant game loading.</p>
                </div>

                <div class="p-5 rounded-2xl bg-gray-800/60 backdrop-blur-sm border border-gray-700/60 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">
                        🛡️
                    </div>
                    <h3 class="font-bold text-white text-base">Official Warranty</h3>
                    <p class="text-xs text-gray-400 leading-relaxed">Up to 10-year manufacturer warranty with guaranteed authentic retail hardware packaging.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hardware Categories Showcase -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Shop by Component</h2>
            <p class="text-sm text-gray-500 mt-1">Select a category to build or upgrade your desktop computer</p>
        </div>
        <a href="{{ route('shop') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
            View All Categories &rarr;
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($categories as $category)
            <a href="{{ route('shop', ['category' => $category->slug]) }}" class="group relative p-5 bg-white rounded-2xl border border-gray-200 hover:border-blue-500 hover:shadow-md transition duration-200 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </span>
                    <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                        {{ $category->products_count }} parts
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition text-sm">{{ $category->name }}</h3>
                    <p class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $category->description }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>

<!-- Featured Hardware Products Grid -->
<section class="bg-white border-y border-gray-200 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Enthusiast Grade Hardware</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mt-0.5">Featured Components</h2>
            </div>
            <a href="{{ route('shop') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1">
                Explore Full Inventory &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($featuredProducts as $product)
                <div class="group bg-gray-50 rounded-2xl border border-gray-200 hover:border-blue-400 hover:shadow-lg transition flex flex-col justify-between overflow-hidden">
                    <div class="p-5">
                        <!-- Top Badges: Brand & Stock -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                {{ $product->brand?->name ?? 'Hardware' }}
                            </span>
                            @if($product->stock_quantity <= 0)
                                <span class="text-[11px] font-semibold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                    Out of Stock
                                </span>
                            @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                    Only {{ $product->stock_quantity }} Left
                                </span>
                            @else
                                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                    In Stock
                                </span>
                            @endif
                        </div>

                        <!-- Hardware Icon / Image Visual Container -->
                        <div class="h-44 bg-white rounded-xl border border-gray-100 flex items-center justify-center p-4 relative overflow-hidden group-hover:scale-[1.02] transition">
                            @if($product->sale_price)
                                <span class="absolute top-2.5 left-2.5 bg-rose-600 text-white text-[11px] font-extrabold px-2 py-0.5 rounded-md shadow-xs">
                                    SAVE ${{ number_format($product->price - $product->sale_price, 0) }}
                                </span>
                            @endif

                            <div class="text-center">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-2 shadow-xs">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-400 tracking-wider uppercase">{{ $product->sku }}</span>
                            </div>
                        </div>

                        <!-- Product Title -->
                        <h3 class="mt-4 font-bold text-gray-900 text-sm leading-snug group-hover:text-blue-600 transition">
                            <a href="{{ route('product.show', $product->slug) }}">
                                {{ $product->name }}
                            </a>
                        </h3>

                        <!-- Hardware Specs Summary Badges -->
                        @if(!empty($product->specifications))
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                @php $specCount = 0; @endphp
                                @foreach($product->specifications as $key => $val)
                                    @if($specCount < 2)
                                        <span class="text-[11px] font-medium bg-white text-gray-600 border border-gray-200 px-2 py-0.5 rounded-md">
                                            {{ $key }}: <strong class="text-gray-900">{{ $val }}</strong>
                                        </span>
                                        @php $specCount++; @endphp
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Bottom Action & Price -->
                    <div class="p-5 pt-0 mt-auto">
                        <div class="flex items-baseline gap-2 mb-3">
                            @if($product->sale_price)
                                <span class="text-xl font-extrabold text-gray-900">${{ number_format($product->sale_price, 2) }}</span>
                                <span class="text-xs text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                            @else
                                <span class="text-xl font-extrabold text-gray-900">${{ number_format($product->price, 2) }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('product.show', $product->slug) }}" class="text-center py-2 px-3 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 rounded-xl border border-gray-300 transition">
                                Details
                            </a>
                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button
                                    type="submit"
                                    @if($product->stock_quantity <= 0) disabled @endif
                                    class="w-full py-2 px-3 text-xs font-bold text-white rounded-xl transition flex items-center justify-center gap-1 {{ $product->stock_quantity > 0 ? 'bg-blue-600 hover:bg-blue-700 shadow-xs' : 'bg-gray-300 cursor-not-allowed text-gray-500' }}"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add
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
    </div>
</section>

<!-- Top Hardware Brands Strip -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="text-center mb-8">
        <h2 class="text-xs font-bold uppercase tracking-wider text-gray-400">Authorized Dealer & Official Warranty Partners</h2>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
        @foreach($topBrands as $brand)
            <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-xs transition text-center group">
                <span class="text-sm font-extrabold text-gray-800 group-hover:text-blue-600 tracking-tight">{{ $brand->name }}</span>
            </a>
        @endforeach
    </div>
</section>
@endsection

