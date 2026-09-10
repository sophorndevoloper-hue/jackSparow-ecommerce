@extends('layouts.storefront')

@section('title', 'Computer Components & Hardware Parts - JackSparow TECH')

@section('content')
<!-- Catalog Header & Breadcrumb -->
<div class="bg-white border-b border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    @else
                        All Computer Components & Hardware
                    @endif
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Showing <strong class="text-gray-900">{{ $products->total() }}</strong> performance components & parts
                </p>
            </div>

            <!-- Active Filters Quick Bar -->
            @if(request('category') || request('brand') || request('search') || request('in_stock') || request('min_price') || request('max_price'))
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active:</span>

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

                    @if(request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="inline-flex items-center gap-1.5 px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-800 border border-gray-300 rounded-full text-xs font-semibold transition">
                            <span>Search: "{{ request('search') }}"</span>
                            <span class="text-gray-500 hover:text-gray-800 text-sm leading-none">&times;</span>
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
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8 items-start">

        <!-- Sidebar Filters -->
        <aside class="w-full md:w-64 lg:w-72 shrink-0">
            <form action="{{ route('shop') }}" method="GET" class="bg-white p-5 rounded-2xl border border-gray-200 shadow-xs space-y-6">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif

                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h2 class="font-extrabold text-gray-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter Hardware
                    </h2>
                    @if(request('category') || request('brand') || request('in_stock') || request('min_price') || request('max_price'))
                        <a href="{{ route('shop') }}" class="text-xs font-semibold text-rose-600 hover:underline">Reset</a>
                    @endif
                </div>

                <!-- Categories Filter -->
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Category</span>
                    <div class="space-y-1 max-h-60 overflow-y-auto pr-1">
                        <label class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs cursor-pointer transition {{ !request('category') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                <span class="w-2 h-2 rounded-full {{ !request('category') ? 'bg-white' : 'bg-gray-300' }}"></span>
                                <span>All Categories</span>
                            </span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full {{ !request('category') ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                                {{ $categories->sum('products_count') }}
                            </span>
                        </label>

                        @foreach($categories as $cat)
                            @php $isActive = request('category') === $cat->slug; @endphp
                            <label class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs cursor-pointer transition {{ $isActive ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-100' }}">
                                <span class="flex items-center gap-2 truncate">
                                    <input type="radio" name="category" value="{{ $cat->slug }}" {{ $isActive ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $isActive ? 'bg-white' : 'bg-gray-300' }}"></span>
                                    <span class="truncate">{{ $cat->name }}</span>
                                </span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full shrink-0 ml-1 {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $cat->products_count }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Brands Filter -->
                <div class="pt-4 border-t border-gray-100">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Manufacturer / Brand</span>
                    <div class="space-y-1 max-h-60 overflow-y-auto pr-1">
                        <label class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs cursor-pointer transition {{ !request('brand') ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-100' }}">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="brand" value="" {{ !request('brand') ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                <span class="w-2 h-2 rounded-full {{ !request('brand') ? 'bg-white' : 'bg-gray-300' }}"></span>
                                <span>All Brands</span>
                            </span>
                        </label>

                        @foreach($brands as $brand)
                            @php $isActive = request('brand') === $brand->slug; @endphp
                            <label class="group flex items-center justify-between px-3 py-2 rounded-xl text-xs cursor-pointer transition {{ $isActive ? 'bg-blue-600 text-white font-bold shadow-xs' : 'text-gray-700 hover:bg-gray-100' }}">
                                <span class="flex items-center gap-2 truncate">
                                    <input type="radio" name="brand" value="{{ $brand->slug }}" {{ $isActive ? 'checked' : '' }} onchange="this.form.submit()" class="hidden">
                                    <span class="w-2 h-2 rounded-full shrink-0 {{ $isActive ? 'bg-white' : 'bg-gray-300' }}"></span>
                                    <span class="truncate">{{ $brand->name }}</span>
                                </span>
                                <span class="text-[11px] px-2 py-0.5 rounded-full shrink-0 ml-1 {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $brand->products_count }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Stock Availability -->
                <div class="pt-4 border-t border-gray-100">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Availability</span>
                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs cursor-pointer hover:bg-gray-50 transition border border-gray-200">
                        <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                        <span class="font-semibold text-gray-800">In Stock Ready to Ship</span>
                    </label>
                </div>

                <!-- Price Range -->
                <div class="pt-4 border-t border-gray-100">
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">Price Range ($)</span>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min $" class="w-full text-xs px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max $" class="w-full text-xs px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full mt-2.5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Filter Price
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

                <div class="flex items-center gap-3">
                    <label for="sort-select" class="text-xs font-bold text-gray-500 whitespace-nowrap">Sort by:</label>
                    <select
                        id="sort-select"
                        onchange="const u = new URL(window.location); u.searchParams.set('sort', this.value); window.location = u.toString();"
                        class="text-xs font-semibold py-2 pl-3 pr-8 bg-gray-50 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Arrivals</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                    </select>
                </div>
            </div>

            <!-- Product Grid: 1 col on mobile, 2 cols on tablet, 3 cols on desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($products as $product)
                    <div class="group bg-white rounded-2xl border border-gray-200 hover:border-blue-500 hover:shadow-lg transition duration-200 flex flex-col justify-between overflow-hidden">
                        <div class="p-5">
                            <!-- Top Brand & Stock Indicator -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-wider">
                                    {{ $product->brand?->name ?? 'Genuine' }}
                                </span>
                                @if($product->stock_quantity <= 0)
                                    <span class="text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                        Out of Stock
                                    </span>
                                @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                        Only {{ $product->stock_quantity }} Left
                                    </span>
                                @else
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        In Stock ({{ $product->stock_quantity }})
                                    </span>
                                @endif
                            </div>

                            <!-- Hardware Visual Card -->
                            <a href="{{ route('product.show', $product->slug) }}" class="block">
                                <div class="h-44 bg-gradient-to-b from-gray-50 to-gray-100 rounded-xl border border-gray-100 flex flex-col items-center justify-center p-4 relative overflow-hidden group-hover:scale-[1.01] transition">
                                    @if($product->sale_price)
                                        <span class="absolute top-2.5 left-2.5 bg-rose-600 text-white text-[11px] font-extrabold px-2 py-0.5 rounded-md shadow-xs">
                                            SAVE ${{ number_format($product->price - $product->sale_price, 0) }}
                                        </span>
                                    @endif

                                    <div class="w-16 h-16 rounded-2xl bg-white text-blue-600 flex items-center justify-center mb-2 shadow-xs">
                                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                    </div>
                                    <span class="text-[11px] font-mono font-bold text-gray-500 uppercase">{{ $product->sku }}</span>
                                    <span class="text-[10px] text-gray-400 mt-0.5">{{ $product->category->name }}</span>
                                </div>

                                <h3 class="mt-4 font-extrabold text-gray-900 text-sm leading-snug group-hover:text-blue-600 transition line-clamp-2">
                                    {{ $product->name }}
                                </h3>
                            </a>

                            <!-- Hardware Specs Snapshot -->
                            @if(!empty($product->specifications))
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @php $count = 0; @endphp
                                    @foreach($product->specifications as $k => $v)
                                        @if($count < 2)
                                            <span class="text-[11px] bg-gray-50 border border-gray-200 text-gray-700 px-2 py-0.5 rounded-md font-medium">
                                                {{ $k }}: <strong class="text-gray-900">{{ $v }}</strong>
                                            </span>
                                            @php $count++; @endphp
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Price & Action Section -->
                        <div class="p-5 pt-0 mt-auto">
                            <div class="flex items-baseline gap-2 mb-3">
                                @if($product->sale_price)
                                    <span class="text-2xl font-extrabold text-gray-900">${{ number_format($product->sale_price, 2) }}</span>
                                    <span class="text-xs text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="text-2xl font-extrabold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('product.show', $product->slug) }}" class="text-center py-2.5 px-3 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition">
                                    View Specs
                                </a>
                                <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">
                                    <button
                                        type="submit"
                                        @if($product->stock_quantity <= 0) disabled @endif
                                        class="w-full py-2.5 px-3 text-xs font-bold text-white rounded-xl transition flex items-center justify-center gap-1.5 {{ $product->stock_quantity > 0 ? 'bg-blue-600 hover:bg-blue-500 shadow-sm' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add to Cart</span>
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
            <div class="pt-4">
                {{ $products->links() }}
            </div>
        </main>
    </div>
</div>
@endsection
