@extends('layouts.storefront')

@section('title', $product->name . ' - JackSparow TECH')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-xs text-gray-500 flex items-center gap-2">
            <a href="{{ route('home') }}" class="hover:text-blue-600">Home</a>
            <span>/</span>
            <a href="{{ route('shop') }}" class="hover:text-blue-600">Catalog</a>
            <span>/</span>
            <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="hover:text-blue-600">{{ $product->category->name }}</a>
            <span>/</span>
            <span class="text-gray-800 font-semibold truncate max-w-xs">{{ $product->name }}</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden p-6 sm:p-10 mb-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Product Visual Presentation -->
            <div class="lg:col-span-5 flex flex-col justify-center">
                <div class="h-80 sm:h-96 bg-gray-50 rounded-2xl border border-gray-200 flex flex-col items-center justify-center p-8 relative overflow-hidden">
                    @if($product->sale_price)
                        <span class="absolute top-4 left-4 bg-rose-600 text-white text-xs font-extrabold px-3 py-1 rounded-md shadow-xs">
                            SAVE ${{ number_format($product->price - $product->sale_price, 2) }}
                        </span>
                    @endif

                    <div class="w-28 h-28 rounded-3xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4 shadow-sm">
                        <svg class="w-14 h-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    </div>
                    <span class="text-xs font-mono font-bold text-gray-500 uppercase tracking-widest">{{ $product->sku }}</span>
                    <span class="text-xs text-gray-400 mt-1">Retail Boxed Hardware Component</span>
                </div>
            </div>

            <!-- Product Details & Purchase Form -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
                <div>
                    <!-- Category & Brand Header -->
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <a href="{{ route('shop', ['brand' => $product->brand?->slug]) }}" class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md hover:bg-blue-100 transition">
                            {{ $product->brand?->name ?? 'Genuine Part' }}
                        </a>
                        <span class="text-gray-300">•</span>
                        <a href="{{ route('shop', ['category' => $product->category->slug]) }}" class="text-xs text-gray-500 hover:text-gray-700">
                            {{ $product->category->name }}
                        </a>
                        <span class="text-gray-300">•</span>
                        <span class="text-xs font-mono text-gray-400">SKU: {{ $product->sku }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight leading-snug">
                        {{ $product->name }}
                    </h1>

                    <p class="text-sm text-gray-600 mt-3 leading-relaxed">
                        {{ $product->short_description }}
                    </p>

                    <!-- Price & Stock Section -->
                    <div class="mt-6 p-4 rounded-2xl bg-gray-50 border border-gray-100 flex flex-wrap items-baseline justify-between gap-4">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Unit Price</span>
                            <div class="flex items-baseline gap-2 mt-0.5">
                                @if($product->sale_price)
                                    <span class="text-3xl font-extrabold text-gray-900">${{ number_format($product->sale_price, 2) }}</span>
                                    <span class="text-sm text-gray-400 line-through">${{ number_format($product->price, 2) }}</span>
                                @else
                                    <span class="text-3xl font-extrabold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Availability</span>
                            <div class="mt-1">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                        Out of Stock
                                    </span>
                                @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                                        Low Stock ({{ $product->stock_quantity }} units left)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                        In Stock ({{ $product->stock_quantity }} available)
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Warranty & Guarantees -->
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div class="flex items-center gap-2 text-gray-600">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Warranty: <strong>{{ $product->warranty_period ?? 'Standard 1 Year' }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                            <span>Fast Dispatch within 24h</span>
                        </div>
                    </div>
                </div>

                <!-- Add to Cart Form -->
                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="pt-4 border-t border-gray-100 flex flex-wrap items-center gap-4">
                    @csrf
                    <div class="w-28">
                        <label for="quantity" class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Quantity</label>
                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            value="1"
                            min="1"
                            max="{{ $product->stock_quantity }}"
                            @if($product->stock_quantity <= 0) disabled @endif
                            class="w-full text-center text-sm font-bold py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                        >
                    </div>

                    <div class="flex-1 pt-5">
                        <button
                            type="submit"
                            @if($product->stock_quantity <= 0) disabled @endif
                            class="w-full py-3.5 px-6 font-bold text-sm text-white rounded-xl shadow-md transition flex items-center justify-center gap-2 {{ $product->stock_quantity > 0 ? 'bg-blue-600 hover:bg-blue-500 shadow-blue-600/30' : 'bg-gray-300 text-gray-500 cursor-not-allowed' }}"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>{{ $product->stock_quantity > 0 ? 'Add to Cart' : 'Currently Out of Stock' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Technical Specifications Table & Full Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-16">
        <div class="lg:col-span-7 bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Detailed Technical Specifications
            </h2>

            @if(!empty($product->specifications))
                <div class="divide-y divide-gray-100 text-sm">
                    @foreach($product->specifications as $key => $value)
                        <div class="py-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="font-semibold text-gray-500 text-xs sm:text-sm">{{ $key }}</dt>
                            <dd class="sm:col-span-2 text-gray-900 font-medium text-xs sm:text-sm">{{ $value }}</dd>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-500">Standard computer part specifications apply.</p>
            @endif
        </div>

        <div class="lg:col-span-5 bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8">
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Product Description
            </h2>
            <div class="prose prose-sm text-gray-600 text-sm leading-relaxed space-y-3">
                <p>{{ $product->description }}</p>
            </div>
        </div>
    </div>

    <!-- Related Components in Category -->
    @if($relatedProducts->count() > 0)
        <div>
            <h2 class="text-xl font-extrabold text-gray-900 tracking-tight mb-6">Similar Components in {{ $product->category->name }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ $related->brand?->name }}</span>
                            <h3 class="mt-1 font-bold text-sm text-gray-900 line-clamp-2">
                                <a href="{{ route('product.show', $related->slug) }}" class="hover:text-blue-600 transition">
                                    {{ $related->name }}
                                </a>
                            </h3>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="font-extrabold text-base text-gray-900">${{ number_format($related->effective_price, 2) }}</span>
                            <a href="{{ route('product.show', $related->slug) }}" class="text-xs font-semibold text-blue-600 hover:underline">View &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

