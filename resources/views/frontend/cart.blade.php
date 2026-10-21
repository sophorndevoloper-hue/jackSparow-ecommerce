@extends('layouts.storefront')

@section('title', 'Shopping Cart - JackSparow TECH')

@section('content')
<div class="relative overflow-hidden bg-slate-950 border-b border-slate-800/80 py-8 text-white">
    <!-- Ambient decorative glow -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2" aria-label="Breadcrumb">
                    <a href="{{ route('shop') }}" class="hover:text-blue-400 transition flex items-center gap-1.5">
                        <i class="bi bi-cpu"></i> Hardware Catalog
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-blue-400">Shopping Cart</span>
                </nav>
                <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-white tracking-tight flex flex-wrap items-center gap-2 sm:gap-3">
                    <span>Your Shopping Cart</span>
                    <span class="text-[11px] sm:text-xs px-2.5 py-0.5 sm:py-1 rounded-full bg-blue-500/15 text-blue-400 border border-blue-500/30 font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-shield-check text-emerald-400"></i> Official Hardware Warranty
                    </span>
                </h1>
                <p class="text-xs text-slate-400 mt-1">Review your selected computer components before placing your order</p>
            </div>

            <!-- Step Navigation Indicator -->
            <div class="hidden sm:flex items-center gap-2.5 bg-slate-900/90 border border-slate-800 px-4 py-2.5 rounded-2xl shadow-inner backdrop-blur-sm">
                <div class="flex items-center gap-2 text-xs font-bold text-white">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                    <span class="text-blue-300">Cart</span>
                </div>
                <div class="w-6 h-px bg-slate-700"></div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-5 h-5 rounded-full bg-slate-800 text-slate-500 flex items-center justify-center text-[10px] font-bold">2</span>
                    <span>Checkout</span>
                </div>
                <div class="w-6 h-px bg-slate-700"></div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-5 h-5 rounded-full bg-slate-800 text-slate-500 flex items-center justify-center text-[10px] font-bold">3</span>
                    <span>Done</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-slate-50/70 min-h-[calc(100vh-280px)] py-10 relative overflow-hidden" style="background: radial-gradient(ellipse 80% 60% at 50% 0%, #f8fafc 0%, #f1f5f9 100%);">
    <!-- Ambient subtle background lighting -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute bottom-1/3 left-10 w-96 h-96 bg-indigo-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    @if(count($cart) > 0)
        <div id="cart-container" class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            <!-- Cart Items List Card -->
            <div class="md:col-span-7 xl:col-span-8 bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-bold text-gray-900 text-base">
                        Cart Items (<span id="cart-items-count">{{ count($cart) }}</span>)
                    </h2>
                    <form id="clear-cart-form" action="{{ route('cart.clear') }}" method="POST">
                        @csrf
                        <button
                            type="button"
                            data-cart-action="clear"
                            onclick="window.cartStepper ? window.cartStepper.clearAll() : null"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-700 transition flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                        >
                            <i class="bi bi-trash3 text-xs"></i>
                            <span>Clear Entire Cart</span>
                        </button>
                    </form>
                </div>

                <div id="cart-items-list" class="divide-y divide-gray-100">
                    @foreach($cart as $id => $item)
                        <div
                            id="cart-row-{{ $id }}"
                            data-name="{{ $item['name'] }}"
                            class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all duration-300"
                        >
                            <!-- Product Image & Details -->
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-white border border-gray-200 flex items-center justify-center shrink-0 overflow-hidden p-1">
                                    @if(!empty($item['image_url']))
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';">
                                    @elseif(!empty($item['image']))
                                        <img src="{{ asset('storage/' . ltrim($item['image'], '/')) }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';">
                                    @else
                                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                    @endif
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
                                        <span class="mx-1">•</span>
                                        <span class="text-slate-400 font-medium">Stock: {{ $item['stock_quantity'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Stepper Controls & Line Price -->
                            <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                                <!-- Modern Stepper [-] [ Qty ] [+] -->
                                <div class="flex items-center">
                                    <div class="inline-flex items-center rounded-xl border border-gray-200 bg-gray-50/80 p-1 shadow-2xs hover:border-gray-300 transition focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500">
                                        <!-- Minus Button -->
                                        <button
                                            type="button"
                                            id="btn-minus-{{ $id }}"
                                            data-cart-action="minus"
                                            data-cart-id="{{ $id }}"
                                            onclick="window.cartStepper ? window.cartStepper.change({{ $id }}, -1) : null"
                                            {{ $item['quantity'] <= 1 ? 'disabled' : '' }}
                                            class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900 active:scale-95 flex items-center justify-center transition disabled:opacity-40 disabled:cursor-not-allowed shadow-2xs cursor-pointer"
                                            title="Decrease quantity"
                                            aria-label="Decrease quantity"
                                        >
                                            <i class="bi bi-dash text-sm font-bold"></i>
                                        </button>

                                        <!-- Quantity Input -->
                                        <input
                                            type="number"
                                            id="cart-qty-{{ $id }}"
                                            value="{{ $item['quantity'] }}"
                                            min="1"
                                            max="{{ $item['stock_quantity'] }}"
                                            onchange="window.cartStepper ? window.cartStepper.onInput({{ $id }}, this) : null"
                                            class="w-12 text-center text-xs font-black text-gray-900 bg-transparent border-0 focus:ring-0 p-0 select-all [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        >

                                        <!-- Plus Button -->
                                        <button
                                            type="button"
                                            id="btn-plus-{{ $id }}"
                                            data-cart-action="plus"
                                            data-cart-id="{{ $id }}"
                                            onclick="window.cartStepper ? window.cartStepper.change({{ $id }}, 1) : null"
                                            {{ $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' }}
                                            class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:text-gray-900 active:scale-95 flex items-center justify-center transition disabled:opacity-40 disabled:cursor-not-allowed shadow-2xs cursor-pointer"
                                            title="Increase quantity"
                                            aria-label="Increase quantity"
                                        >
                                            <i class="bi bi-plus text-sm font-bold"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Line Total Price -->
                                <div class="text-right min-w-24">
                                    <span id="line-total-{{ $id }}" class="text-sm font-extrabold text-gray-900">
                                        ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                    </span>
                                    <div id="loading-spinner-{{ $id }}" class="hidden text-[10px] text-blue-600 font-semibold flex items-center justify-end gap-1 mt-0.5">
                                        <svg class="animate-spin h-3 w-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Saving...</span>
                                    </div>
                                </div>

                                <!-- Remove Item Button -->
                                <button
                                    type="button"
                                    data-cart-action="remove"
                                    data-cart-id="{{ $id }}"
                                    onclick="window.cartStepper ? window.cartStepper.removeItem({{ $id }}) : null"
                                    class="text-gray-400 hover:text-rose-600 p-1.5 rounded-xl hover:bg-rose-50 transition cursor-pointer"
                                    title="Remove item"
                                    aria-label="Remove item"
                                >
                                    <i class="bi bi-trash3 text-base"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Card Footer Strip -->
                <div class="p-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs">
                    <a href="{{ route('shop') }}" class="font-semibold text-blue-600 hover:underline flex items-center gap-1">
                        &larr; Continue Shopping Hardware
                    </a>
                    <span class="text-gray-500">Prices include official hardware warranty</span>
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="md:col-span-5 xl:col-span-4 bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8 space-y-6">
                <h2 class="font-extrabold text-gray-900 text-lg">Order Summary</h2>

                <div class="space-y-3 text-sm text-gray-600 border-b border-gray-100 pb-4">
                    <div class="flex justify-between">
                        <span>Parts Subtotal</span>
                        <span id="summary-subtotal" class="font-semibold text-gray-900">
                            ${{ number_format($subtotal, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>Estimated Tax (8%)</span>
                        <span id="summary-tax" class="font-semibold text-gray-900">
                            ${{ number_format($tax, 2) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span>Express Shipping</span>
                        <span id="summary-shipping" class="{{ $shipping == 0 ? 'font-bold text-emerald-600 uppercase text-xs tracking-wider' : 'font-semibold text-gray-900' }}">
                            @if($shipping == 0)
                                Free (Over $150)
                            @else
                                ${{ number_format($shipping, 2) }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="flex justify-between items-baseline text-lg font-extrabold text-gray-900">
                    <span>Total Amount</span>
                    <span id="summary-total" class="text-2xl text-blue-600">
                        ${{ number_format($total, 2) }}
                    </span>
                </div>

                @if(auth('frontend')->check())
                    <a href="{{ route('checkout.index') }}" class="w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-600/30 transition flex items-center justify-center gap-2">
                        <span>Proceed to Checkout</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('checkout.index') }}" class="w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-md shadow-blue-600/30 transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span>Sign In to Checkout</span>
                    </a>
                    <p class="text-[11px] text-gray-500 text-center -mt-1">
                        🔒 Sign in or register as a customer before placing your order
                    </p>
                @endif

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
</div>

<!-- Dynamic Cart Stepper & AJAX Logic -->
<script>
    window.cartStepper = {
        csrfToken: '{{ csrf_token() }}',
        updateUrlTemplate: '{{ url('/cart/update') }}',
        removeUrlTemplate: '{{ url('/cart/remove') }}',
        clearUrl: '{{ route('cart.clear') }}',
        stockMap: {
            @foreach($cart as $id => $item)
                {{ $id }}: {{ (int) $item['stock_quantity'] }},
            @endforeach
        },

        change(productId, delta) {
            const input = document.getElementById('cart-qty-' + productId);
            if (!input) return;
            let current = parseInt(input.value, 10) || 1;
            let maxStock = this.stockMap[productId] || 999;
            let next = current + delta;

            if (next < 1) {
                return;
            }
            if (next > maxStock) {
                if (typeof window.toast === 'function') {
                    window.toast('Maximum available stock is ' + maxStock + ' units.', 'warning', 'Stock Limit');
                }
                return;
            }

            input.value = next;
            this.updateServer(productId, next);
        },

        onInput(productId, input) {
            let val = parseInt(input.value, 10) || 1;
            let maxStock = this.stockMap[productId] || 999;

            if (val < 1) {
                val = 1;
                input.value = 1;
            } else if (val > maxStock) {
                val = maxStock;
                input.value = maxStock;
                if (typeof window.toast === 'function') {
                    window.toast('Maximum available stock is ' + maxStock + ' units.', 'warning', 'Stock Limit');
                }
            }
            this.updateServer(productId, val);
        },

        updateServer(productId, quantity) {
            const spinner = document.getElementById('loading-spinner-' + productId);
            const btnMinus = document.getElementById('btn-minus-' + productId);
            const btnPlus = document.getElementById('btn-plus-' + productId);
            const maxStock = this.stockMap[productId] || 999;

            if (btnMinus) btnMinus.disabled = (quantity <= 1);
            if (btnPlus) btnPlus.disabled = (quantity >= maxStock);
            if (spinner) spinner.classList.remove('hidden');

            fetch(this.updateUrlTemplate + '/' + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify({ quantity })
            })
            .then(async (response) => {
                if (spinner) spinner.classList.add('hidden');
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.success) {
                    const lineElem = document.getElementById('line-total-' + productId);
                    if (lineElem && data.formatted_item_total) {
                        lineElem.textContent = data.formatted_item_total;
                    }
                    window.cartStepper.applyTotals(data);
                    if (typeof window.toast === 'function') {
                        window.toast(data.message || 'Quantity updated.', 'success', 'Cart Updated');
                    }
                } else {
                    const msg = data.message || 'Could not update quantity.';
                    if (typeof window.toast === 'function') {
                        window.toast(msg, 'error', 'Notice');
                    }
                    if (data.current_quantity) {
                        const input = document.getElementById('cart-qty-' + productId);
                        if (input) input.value = data.current_quantity;
                    }
                }
            })
            .catch(() => {
                if (spinner) spinner.classList.add('hidden');
                if (typeof window.toast === 'function') {
                    window.toast('Network error updating cart.', 'error', 'Error');
                }
            });
        },

        applyTotals(data) {
            if (data.formatted_subtotal) {
                const el = document.getElementById('summary-subtotal');
                if (el) el.textContent = data.formatted_subtotal;
            }
            if (data.formatted_tax) {
                const el = document.getElementById('summary-tax');
                if (el) el.textContent = data.formatted_tax;
            }
            if (data.formatted_shipping) {
                const el = document.getElementById('summary-shipping');
                if (el) {
                    el.textContent = data.formatted_shipping;
                    if (data.shipping === 0) {
                        el.className = 'font-bold text-emerald-600 uppercase text-xs tracking-wider';
                    } else {
                        el.className = 'font-semibold text-gray-900';
                    }
                }
            }
            if (data.formatted_total) {
                const el = document.getElementById('summary-total');
                if (el) el.textContent = data.formatted_total;
            }
            if (typeof data.cart_count !== 'undefined') {
                const countEl = document.getElementById('cart-items-count');
                if (countEl) countEl.textContent = data.cart_count;
                if (typeof window.updateCartBadge === 'function') {
                    window.updateCartBadge(data.cart_count);
                }
            }
        },

        async removeItem(productId) {
            const row = document.getElementById('cart-row-' + productId);
            const productName = row ? (row.getAttribute('data-name') || 'this component') : 'this component';

            let confirmed = false;
            if (typeof window.confirmModal === 'function') {
                confirmed = await window.confirmModal({
                    title: 'Remove Component?',
                    message: 'Are you sure you want to remove "' + productName + '" from your shopping cart?',
                    confirmText: 'Remove Item',
                    cancelText: 'Keep Item',
                    type: 'danger'
                });
            } else {
                confirmed = window.confirm('Are you sure you want to remove "' + productName + '" from your shopping cart?');
            }

            if (!confirmed) return;

            if (row) row.style.opacity = '0.4';

            fetch(this.removeUrlTemplate + '/' + productId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    if (row) row.remove();
                    if (data.is_empty) {
                        location.reload();
                        return;
                    }
                    window.cartStepper.applyTotals(data);
                    if (typeof window.toast === 'function') {
                        window.toast(data.message || 'Item removed.', 'info', 'Cart Updated');
                    }
                } else {
                    if (row) row.style.opacity = '1';
                    if (typeof window.toast === 'function') {
                        window.toast(data.message || 'Failed to remove item.', 'error', 'Error');
                    }
                }
            })
            .catch(() => {
                if (row) row.style.opacity = '1';
                if (typeof window.toast === 'function') {
                    window.toast('Network error removing item.', 'error', 'Error');
                }
            });
        },

        async clearAll() {
            let confirmed = false;
            if (typeof window.confirmModal === 'function') {
                confirmed = await window.confirmModal({
                    title: 'Empty the entire cart?',
                    message: 'Are you sure you want to remove all components from your cart? Your build selection will be cleared.',
                    confirmText: 'Yes, Empty Cart',
                    cancelText: 'Keep My Items',
                    type: 'danger'
                });
            } else {
                confirmed = window.confirm('Are you sure you want to remove all components from your cart?');
            }

            if (!confirmed) return;

            fetch(this.clearUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    if (typeof window.updateCartBadge === 'function') {
                        window.updateCartBadge(0);
                    }
                    location.reload();
                } else {
                    document.getElementById('clear-cart-form').submit();
                }
            })
            .catch(() => {
                document.getElementById('clear-cart-form').submit();
            });
        }
    };

    // Global alias for compatibility
    var cartStepper = window.cartStepper;

    // Delegated click handler to guarantee clicks work across the entire cart
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-cart-action]');
        if (!btn) return;
        const action = btn.getAttribute('data-cart-action');
        const id = btn.getAttribute('data-cart-id');
        if (action === 'minus' && id) window.cartStepper.change(id, -1);
        if (action === 'plus' && id) window.cartStepper.change(id, 1);
        if (action === 'remove' && id) window.cartStepper.removeItem(id);
        if (action === 'clear') window.cartStepper.clearAll();
    });
</script>
@endsection
