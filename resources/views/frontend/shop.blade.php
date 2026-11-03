@extends('layouts.storefront')

@section('title', 'Computer Components & Hardware Parts - JackSparow TECH')

@section('content')
<!-- Catalog Header & Breadcrumb -->
<div class="bg-white border-b border-gray-200 py-6">
    <div class="max-w-7xl 2xl:max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-xs text-gray-500 mb-2 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition">Home</a>
            <span>/</span>
            <span class="text-gray-900 font-semibold">Catalog</span>
            @if(request('category'))
                <span>/</span>
                <span class="text-blue-600 font-semibold">{{ ucwords(str_replace('-', ' ', request('category'))) }}</span>
            @endif
        </nav>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    @if(request('category'))
                        {{ ucwords(str_replace('-', ' ', request('category'))) }}
                    @elseif(request('brand'))
                        {{ ucwords(str_replace('-', ' ', request('brand'))) }} Components
                    @elseif(request('collection'))
                        {{ ucwords(str_replace('-', ' ', request('collection'))) }} Collection
                    @else
                        All Computer Components &amp; Hardware
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Showing <strong class="text-gray-900">{{ $products->total() }}</strong> performance components &amp; parts
                </p>
            </div>

            <!-- Active Filters Quick Bar -->
            @if(request('category') || request('brand') || request('search') || request('in_stock') || request('min_price') || request('max_price'))
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active:</span>

                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-300 rounded-full text-xs font-semibold transition">
                            <span>Search: "{{ request('search') }}"</span>
                            <span class="text-gray-500 hover:text-gray-800 text-sm leading-none">&times;</span>
                        </a>
                    @endif

                    @if(request('category'))
                        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-full text-xs font-semibold transition">
                            <span>Category: {{ ucwords(str_replace('-', ' ', request('category'))) }}</span>
                            <span class="text-blue-500 hover:text-blue-800 text-sm leading-none">&times;</span>
                        </a>
                    @endif

                    @if(request('brand'))
                        <a href="{{ request()->fullUrlWithQuery(['brand' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-full text-xs font-semibold transition">
                            <span>Brand: {{ strtoupper(request('brand')) }}</span>
                            <span class="text-blue-500 hover:text-blue-800 text-sm leading-none">&times;</span>
                        </a>
                    @endif

                    @if(request('in_stock'))
                        <a href="{{ request()->fullUrlWithQuery(['in_stock' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold transition">
                            <span>In Stock Only</span>
                            <span class="text-emerald-500 hover:text-emerald-800 text-sm leading-none">&times;</span>
                        </a>
                    @endif

                    <a href="{{ route('shop') }}" class="text-xs text-rose-600 hover:underline font-semibold ml-1">
                        Clear All
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Main Catalog Layout Container -->
<div class="max-w-7xl 2xl:max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8 items-start">

        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 lg:w-72 shrink-0 md:sticky md:top-24 md:self-start md:max-h-[calc(100vh-5rem)] md:overflow-y-auto no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
            <style>
                .sidebar-custom-scroll::-webkit-scrollbar { width: 5px; }
                .sidebar-custom-scroll::-webkit-scrollbar-track { background: transparent; }
                .sidebar-custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }
                .sidebar-custom-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
                .no-scrollbar::-webkit-scrollbar { display: none; }
            </style>
            <form action="{{ route('shop') }}" method="GET" class="bg-white p-4.5 rounded-2xl border border-gray-200/90 shadow-xs space-y-4">
                @if(!empty($sort) && $sort !== 'newest')
                    <input type="hidden" name="sort" value="{{ $sort }}">
                @endif

                {{-- Retain active search query from top navbar --}}
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <!-- Sidebar Header -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h2 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter Hardware
                    </h2>
                    @if(request('category') || request('brand') || request('search') || request('in_stock') || request('min_price') || request('max_price'))
                        <a href="{{ route('shop') }}" class="text-xs font-semibold text-rose-600 hover:underline">Reset All</a>
                    @endif
                </div>

                <!-- Category Filter with In-Filter Search -->
                <div x-data="{ 
                    catSearch: '', 
                    matches(name) { 
                        return !this.catSearch || name.toLowerCase().includes(this.catSearch.toLowerCase().trim()); 
                    } 
                }">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Category</span>
                        <span class="text-[10px] text-gray-400 font-semibold" x-show="!catSearch">{{ $categories->count() }} total</span>
                        <span class="text-[10px] text-blue-600 font-semibold" x-show="catSearch" style="display: none;">searching...</span>
                    </div>

                    <!-- Category Live Search Input (Always Pinned Above List) -->
                    <div class="relative mb-2">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="catSearch" 
                            @keydown.enter.prevent
                            placeholder="Search categories..." 
                            style="padding-left: 2.15rem !important; padding-right: 1.75rem !important;"
                            class="w-full text-xs py-1.5 bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 focus:border-blue-500 rounded-lg focus:ring-1 focus:ring-blue-500 text-gray-800 placeholder-gray-400 transition"
                        >
                        <button 
                            type="button" 
                            x-show="catSearch.length > 0" 
                            @click="catSearch = ''" 
                            class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-600 text-sm font-bold leading-none cursor-pointer"
                            style="display: none;"
                        >&times;</button>
                    </div>

                    <!-- Category Items List (Constrained scrollbox) -->
                    <div 
                        style="max-height: 185px; overflow-y: auto;" 
                        class="sidebar-custom-scroll space-y-0.5 pr-1"
                    >
                        <label 
                            x-show="!catSearch"
                            class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs cursor-pointer transition {{ !request('category') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}"
                        >
                            <span class="flex items-center gap-2 truncate">
                                <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ !request('category') ? 'bg-white' : 'bg-gray-300 group-hover:bg-blue-400' }}"></span>
                                <span class="truncate">All Categories</span>
                            </span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full shrink-0 ml-1 {{ !request('category') ? 'bg-white/20 text-white font-semibold' : 'bg-gray-100 text-gray-500 font-medium group-hover:bg-blue-50 group-hover:text-blue-600' }}">
                                {{ $categories->sum('products_count') }}
                            </span>
                        </label>

                        @foreach($categories as $cat)
                            @php 
                                $isActive = request('category') === $cat->slug; 
                                $hasProducts = $cat->products_count > 0;
                            @endphp
                            <label 
                                x-show="matches('{{ addslashes($cat->name) }}')"
                                class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs cursor-pointer transition {{ $isActive ? 'bg-blue-600 text-white font-bold shadow-xs' : ($hasProducts ? 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600') }}"
                            >
                                <span class="flex items-center gap-2 truncate">
                                    <input type="radio" name="category" value="{{ $cat->slug }}" {{ $isActive ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isActive ? 'bg-white' : ($hasProducts ? 'bg-gray-300 group-hover:bg-blue-400' : 'bg-gray-200') }}"></span>
                                    <span class="truncate">{{ $cat->name }}</span>
                                </span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full shrink-0 ml-1 {{ $isActive ? 'bg-white/20 text-white font-semibold' : ($hasProducts ? 'bg-gray-100 text-gray-600 font-medium group-hover:bg-blue-50 group-hover:text-blue-600' : 'bg-gray-50 text-gray-400 font-normal') }}">
                                    {{ $cat->products_count }}
                                </span>
                            </label>
                        @endforeach

                        <p x-show="catSearch && {{ json_encode($categories->pluck('name')->toArray()) }}.filter(n => n.toLowerCase().includes(catSearch.toLowerCase().trim())).length === 0" class="py-2.5 text-center text-xs text-gray-400 font-medium" style="display: none;">
                            No matching category
                        </p>
                    </div>
                </div>

                <!-- Brand Filter with In-Filter Search -->
                <div x-data="{ 
                    brandSearch: '', 
                    matches(name) { 
                        return !this.brandSearch || name.toLowerCase().includes(this.brandSearch.toLowerCase().trim()); 
                    } 
                }" class="pt-3.5 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Manufacturer / Brand</span>
                        <span class="text-[10px] text-gray-400 font-semibold" x-show="!brandSearch">{{ $brands->count() }} total</span>
                        <span class="text-[10px] text-blue-600 font-semibold" x-show="brandSearch" style="display: none;">searching...</span>
                    </div>

                    <!-- Brand Live Search Input (Always Pinned Above List) -->
                    <div class="relative mb-2">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="brandSearch" 
                            @keydown.enter.prevent
                            placeholder="Search brands..." 
                            style="padding-left: 2.15rem !important; padding-right: 1.75rem !important;"
                            class="w-full text-xs py-1.5 bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 focus:border-blue-500 rounded-lg focus:ring-1 focus:ring-blue-500 text-gray-800 placeholder-gray-400 transition"
                        >
                        <button 
                            type="button" 
                            x-show="brandSearch.length > 0" 
                            @click="brandSearch = ''" 
                            class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-600 text-sm font-bold leading-none cursor-pointer"
                            style="display: none;"
                        >&times;</button>
                    </div>

                    <!-- Brand Items List (Constrained scrollbox) -->
                    <div 
                        style="max-height: 185px; overflow-y: auto;" 
                        class="sidebar-custom-scroll space-y-0.5 pr-1"
                    >
                        <label 
                            x-show="!brandSearch"
                            class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs cursor-pointer transition {{ !request('brand') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }}"
                        >
                            <span class="flex items-center gap-2 truncate">
                                <input type="radio" name="brand" value="" {{ !request('brand') ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ !request('brand') ? 'bg-white' : 'bg-gray-300 group-hover:bg-blue-400' }}"></span>
                                <span class="truncate">All Brands</span>
                            </span>
                        </label>

                        @foreach($brands as $brand)
                            @php 
                                $isActive = request('brand') === $brand->slug; 
                                $hasProducts = $brand->products_count > 0;
                            @endphp
                            <label 
                                x-show="matches('{{ addslashes($brand->name) }}')"
                                class="group flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs cursor-pointer transition {{ $isActive ? 'bg-blue-600 text-white font-bold shadow-xs' : ($hasProducts ? 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600') }}"
                            >
                                <span class="flex items-center gap-2 truncate">
                                    <input type="radio" name="brand" value="{{ $brand->slug }}" {{ $isActive ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $isActive ? 'bg-white' : ($hasProducts ? 'bg-gray-300 group-hover:bg-blue-400' : 'bg-gray-200') }}"></span>
                                    <span class="truncate">{{ $brand->name }}</span>
                                </span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded-full shrink-0 ml-1 {{ $isActive ? 'bg-white/20 text-white font-semibold' : ($hasProducts ? 'bg-gray-100 text-gray-600 font-medium group-hover:bg-blue-50 group-hover:text-blue-600' : 'bg-gray-50 text-gray-400 font-normal') }}">
                                    {{ $brand->products_count }}
                                </span>
                            </label>
                        @endforeach

                        <p x-show="brandSearch && {{ json_encode($brands->pluck('name')->toArray()) }}.filter(n => n.toLowerCase().includes(brandSearch.toLowerCase().trim())).length === 0" class="py-2.5 text-center text-xs text-gray-400 font-medium" style="display: none;">
                            No matching brand
                        </p>
                    </div>
                </div>

                <!-- Stock Availability -->
                <div class="pt-3.5 border-t border-gray-100">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-2">Availability</span>
                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs cursor-pointer transition border {{ request('in_stock') ? 'bg-blue-50/70 border-blue-200 text-blue-900 font-semibold' : 'border-gray-200 text-gray-700 hover:bg-gray-50' }}">
                        <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300 cursor-pointer">
                        <span>In Stock Ready to Ship</span>
                    </label>
                </div>

                <!-- Price Range -->
                <div class="pt-3.5 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Price Range ($)</span>
                        @if(request('min_price') || request('max_price'))
                            <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="text-[10px] text-rose-600 hover:underline font-semibold">Clear</a>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold">$</span>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" min="0" class="w-full text-xs pl-5 pr-2 py-1.5 bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 text-gray-800 placeholder-gray-400 transition">
                        </div>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold">$</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" min="0" class="w-full text-xs pl-5 pr-2 py-1.5 bg-gray-50 hover:bg-white focus:bg-white border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 text-gray-800 placeholder-gray-400 transition">
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-2.5 py-2 bg-gray-900 hover:bg-blue-600 text-white text-xs font-bold rounded-lg transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>Apply Price</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </aside>

        <!-- Main Products View Column -->
        <main class="flex-1 min-w-0 w-full space-y-6">
            <!-- Top Controls Bar -->
            <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="text-xs text-gray-600 font-medium">
                    Showing <strong class="text-gray-900">{{ $products->count() }}</strong> of <strong class="text-gray-900">{{ $products->total() }}</strong> hardware items
                </div>

                <form id="sortForm" method="GET" action="{{ route('shop') }}" class="flex items-center gap-3 m-0">
                    {{-- Preserve existing filters in GET query --}}
                    @foreach(request()->except(['sort', 'page']) as $queryKey => $queryVal)
                        @if(is_array($queryVal))
                            @foreach($queryVal as $itemVal)
                                <input type="hidden" name="{{ $queryKey }}[]" value="{{ $itemVal }}">
                            @endforeach
                        @elseif(!is_null($queryVal) && $queryVal !== '')
                            <input type="hidden" name="{{ $queryKey }}" value="{{ $queryVal }}">
                        @endif
                    @endforeach

                    <label for="sort-select" class="text-xs font-bold text-gray-500 whitespace-nowrap">Sort by:</label>
                    <div class="relative">
                        <select
                            id="sort-select"
                            name="sort"
                            onchange="this.form.submit()"
                            class="text-xs font-semibold py-2 pl-3 pr-8 bg-gray-50 hover:bg-white border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer shadow-2xs transition"
                        >
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                            <option value="bestseller" {{ in_array($sort, ['bestseller', 'best_seller', 'bestsellers', 'best_selling']) ? 'selected' : '' }}>Best Sellers</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                            <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Product Grid: 1 col on mobile, 2 cols on tablet, 3 cols on desktop, 4 cols on large desktop -->
            @php
                $userFavoriteIds = \App\Models\Favorite::getFavoriteIds();
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-4.5">
                @forelse($products as $product)
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

                        <div class="p-3.5 sm:p-4 flex flex-col flex-grow">
                            <!-- Top Brand & Stock Status Row -->
                            <div class="flex items-center justify-between gap-2 mb-2.5 pr-8">
                                <span class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider group-hover:text-blue-600 transition truncate">
                                    {{ $product->brand?->name ?? 'Hardware' }}
                                </span>

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

                            <!-- Product Image Stage (Compact Height) -->
                            <a href="{{ route('product.show', $product->slug) }}" class="block relative my-1">
                                <div class="product-card-img-stage w-full bg-gradient-to-b from-gray-50/80 to-slate-100/50 rounded-xl border border-gray-100 p-2 relative group-hover:bg-blue-50/20 transition-colors" style="height: 135px; max-height: 135px;">
                                    @if($product->sale_price && $product->price > $product->sale_price)
                                        <span class="absolute top-2 left-2 z-10 inline-flex items-center gap-1 bg-rose-600 text-white text-[9px] font-black px-2 py-0.5 rounded shadow-xs uppercase tracking-wider">
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
                            <div class="flex items-center justify-between text-[10px] text-gray-400 font-medium mt-2 mb-1">
                                <span class="truncate">{{ $product->category?->name ?? 'Components' }}</span>
                                <span class="font-mono text-gray-400 text-[9px] shrink-0">SKU: {{ $product->sku }}</span>
                            </div>

                            <!-- Clamped Title for Uniform Row Alignment -->
                            <h3 class="font-bold text-gray-900 text-xs sm:text-sm leading-snug group-hover:text-blue-600 transition h-9 line-clamp-2" title="{{ $product->name }}">
                                <a href="{{ route('product.show', $product->slug) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>
                        </div>

                        <!-- Bottom Price & Action Footer -->
                        <div class="p-3 sm:p-3.5 pt-0 mt-auto border-t border-gray-100 bg-gray-50/50">
                            <div class="flex items-baseline justify-between gap-1.5 pt-2 mb-2">
                                <div class="flex items-baseline gap-1.5">
                                    @if($product->sale_price)
                                        <span class="text-lg font-black text-gray-900 tracking-tight">
                                            ${{ number_format($product->sale_price, 2) }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 line-through">
                                            ${{ number_format($product->price, 2) }}
                                        </span>
                                    @else
                                        <span class="text-lg font-black text-gray-900 tracking-tight">
                                            ${{ number_format($product->price, 2) }}
                                        </span>
                                    @endif
                                </div>

                                @if($product->sale_price && $product->price > 0)
                                    @php $pct = round((($product->price - $product->sale_price) / $product->price) * 100); @endphp
                                    <span class="text-[9px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200/80 px-1.5 py-0.5 rounded">
                                        -{{ $pct }}%
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-5 gap-1.5">
                                <a href="{{ route('product.show', $product->slug) }}" class="col-span-2 py-1.5 px-2 text-xs font-bold text-gray-700 bg-white hover:bg-gray-100 hover:text-gray-900 rounded-lg transition flex items-center justify-center border border-gray-300 shadow-2xs" title="View Full Specs">
                                    Details
                                </a>
                                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="col-span-3">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button
                                        type="submit"
                                        @if($product->stock_quantity <= 0) disabled @endif
                                        class="w-full py-1.5 px-2 text-xs font-bold text-white rounded-lg transition flex items-center justify-center gap-1 shadow-xs active:scale-[0.98] {{ $product->stock_quantity > 0 ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/20 cursor-pointer' : 'bg-gray-300 cursor-not-allowed text-gray-500' }}"
                                    >
                                        @if($product->stock_quantity > 0)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            <span>Add</span>
                                        @else
                                            <span>Sold Out</span>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 space-y-3">
                        <div class="w-14 h-14 mx-auto rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-extrabold text-gray-900 text-base">No Components Match Your Filters</h3>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">Try clearing your filters or selecting a different component category.</p>
                        <div class="pt-2">
                            <a href="{{ route('shop') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl shadow-xs transition">
                                Reset All Filters
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination Bar -->
            <div class="pt-6 border-t border-slate-200">
                {{ $products->links('vendor.pagination.storefront') }}
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sortSelect = document.getElementById('sort-select');
        var sortForm = document.getElementById('sortForm');
        if (sortSelect && sortForm) {
            sortSelect.addEventListener('change', function () {
                sortForm.submit();
            });
        }
    });
</script>
@endsection
