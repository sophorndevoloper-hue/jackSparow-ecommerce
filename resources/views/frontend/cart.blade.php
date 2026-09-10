@extends('layouts.storefront')

@section('title', 'Shopping Cart - JackSparow TECH')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Your Shopping Cart</h1>
        <p class="text-sm text-gray-500 mt-1">Review your selected computer components before placing your order</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    @if(count($cart) > 0)
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            <!-- Cart Items List -->
            <div class="md:col-span-7 xl:col-span-8 bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 text-base">Cart Items ({{ count($cart) }})</h2>
                    <form action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('Empty the entire cart?')" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                            Clear Entire Cart
                        </button>
                    </form>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($cart as $id => $item)
                        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0 text-blue-600">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                </div>
                                <div>
                                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ $item['brand_name'] ?? 'Component' }}</span>
                                    <h3 class="font-bold text-sm text-gray-900">
                                        <a href="{{ route('product.show', $item['slug']) }}" class="hover:text-blue-600 transition">
                                            {{ $item['name'] }}
                                        </a>
                                    </h3>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span class="font-mono text-gray-400">SKU: {{ $item['sku'] }}</span>
                                        <span class="mx-1">•</span>
                                        <span>Unit: ${{ number_format($item['price'], 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity and Controls -->
                            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                                <form action="{{ route('cart.update', $id) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input
                                        type="number"
                                        name="quantity"
                                        value="{{ $item['quantity'] }}"
                                        min="1"
                                        max="{{ $item['stock_quantity'] }}"
                                        class="w-16 text-center text-xs font-bold py-1.5 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <button type="submit" class="px-2 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition" title="Update quantity">
                                        Update
                                    </button>
                                </form>

                                <div class="text-right min-w-24">
                                    <span class="text-sm font-extrabold text-gray-900">
                                        ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </span>
                                </div>

                                <form action="{{ route('cart.remove', $id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-gray-400 hover:text-rose-600 p-1 rounded transition" title="Remove item">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                    <a href="{{ route('shop') }}" class="font-semibold text-blue-600 hover:underline flex items-center gap-1">
                        &larr; Continue Shopping
                    </a>
                    <span class="text-gray-500">Prices include standard hardware warranty</span>
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="md:col-span-5 xl:col-span-4 bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8 space-y-6">
                <h2 class="font-extrabold text-gray-900 text-lg">Order Summary</h2>

                <div class="space-y-3 text-sm text-gray-600 border-b border-gray-100 pb-4">
                    <div class="flex justify-between">
                        <span>Parts Subtotal</span>
                        <span class="font-semibold text-gray-900">${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Estimated Tax (8%)</span>
                        <span class="font-semibold text-gray-900">${{ number_format($tax, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Express Shipping</span>
                        @if($shipping == 0)
                            <span class="font-bold text-emerald-600 uppercase text-xs tracking-wider">Free (Over $150)</span>
                        @else
                            <span class="font-semibold text-gray-900">${{ number_format($shipping, 2) }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex justify-between items-baseline text-lg font-extrabold text-gray-900">
                    <span>Total Amount</span>
                    <span class="text-2xl text-blue-600">${{ number_format($total, 2) }}</span>
                </div>

                <a href="{{ route('checkout.index') }}" class="w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-600/30 transition flex items-center justify-center gap-2">
                    <span>Proceed to Checkout</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>

                <div class="pt-2 text-center text-xs text-gray-400 space-y-1">
                    <p>🔒 256-Bit SSL Encrypted Checkout</p>
                    <p>📦 Packed in anti-static ESD safety packaging</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-12 text-center max-w-lg mx-auto space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h2 class="text-xl font-extrabold text-gray-900">Your Cart is Currently Empty</h2>
            <p class="text-xs text-gray-500 max-w-sm mx-auto">
                Explore our catalog of graphics cards, processors, motherboards, and memory kits to assemble your custom PC build.
            </p>
            <div class="pt-2">
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-xs transition">
                    <span>Start Shopping Hardware</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

