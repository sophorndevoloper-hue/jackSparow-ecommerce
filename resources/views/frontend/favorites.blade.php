@extends('layouts.storefront')

@section('title', 'My Saved Favorites - JackSparow TECH')

@section('content')
<!-- Dark Modern Tech Header Banner -->
<div class="relative overflow-hidden bg-slate-950 border-b border-slate-800/80 py-8 text-white">
    <!-- Ambient decorative glow -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-blue-400 transition flex items-center gap-1.5">
                        <i class="bi bi-house-door"></i> Home
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-rose-400 font-bold">Saved Favorites</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                    <span>Saved Hardware Favorites</span>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-heart-fill"></i> Wishlist
                    </span>
                </h1>
                <p class="text-xs text-slate-400 mt-1">Keep track of your favorite computer components, monitor prices, and add directly to your cart</p>
            </div>

            <!-- Quick Links -->
            <div class="flex items-center gap-3">
                <a href="{{ route('recently-viewed.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900/90 hover:bg-slate-800 border border-slate-700/80 text-slate-300 hover:text-white font-semibold text-xs rounded-xl transition shadow-inner">
                    <i class="bi bi-clock-history text-blue-400"></i>
                    <span>Recently Viewed</span>
                </a>
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/25 transition">
                    <i class="bi bi-cpu"></i>
                    <span>Catalog</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Canvas with Ambient Background -->
<div class="bg-slate-50/70 min-h-[calc(100vh-280px)] py-10 relative overflow-hidden" style="background: radial-gradient(ellipse 80% 60% at 50% 0%, #f8fafc 0%, #f1f5f9 100%);">
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-rose-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute bottom-1/3 left-10 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-6">

        @if($products->total() > 0)
            <!-- Toolbar Strip -->
            <div class="flex flex-wrap items-center justify-between gap-4 bg-white/95 backdrop-blur-sm p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center font-black text-sm">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-sm">My Saved Items (<span id="favorite-items-count">{{ $products->total() }}</span>)</h2>
                        <span class="text-xs text-slate-400">Click the heart to remove an item, or add components to cart</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <form action="{{ route('favorites.clear') }}" method="POST" id="clear-favorites-form">
                        @csrf
                        <button
                            type="button"
                            onclick="clearAllFavorites()"
                            class="text-xs font-semibold text-rose-600 hover:text-rose-700 transition flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-rose-50 cursor-pointer"
                        >
                            <i class="bi bi-trash3 text-xs"></i>
                            <span>Clear All Favorites</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Product Grid -->
            <div id="favorites-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div id="favorite-card-{{ $product->id }}" class="bg-white/95 backdrop-blur-sm rounded-3xl border border-slate-200/80 p-5 flex flex-col justify-between hover:border-slate-300 hover:shadow-md transition-all duration-300 relative group">
                        <!-- Top Floating Favorite Heart Button -->
                        <button
                            type="button"
                            onclick="removeFavoriteCard({{ $product->id }}, this)"
                            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/95 backdrop-blur-sm border border-slate-200 text-rose-600 hover:bg-rose-50 hover:border-rose-200 hover:scale-110 flex items-center justify-center transition-all shadow-xs cursor-pointer z-10"
                            title="Remove from favorites"
                            aria-label="Remove from favorites"
                        >
                            <i class="bi bi-heart-fill text-sm"></i>
                        </button>

                        <div>
                            <!-- Product Image Container -->
                            <a href="{{ route('product.show', $product->slug) }}" class="block relative h-48 bg-slate-50/70 rounded-2xl border border-slate-100 p-4 mb-4 flex items-center justify-center overflow-hidden">
                                @if($product->primary_image_url)
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';">
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                    </div>
                                @endif

                                @if($product->sale_price)
                                    <span class="absolute top-3 left-3 bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded-md shadow-2xs">
                                        SALE
                                    </span>
                                @endif
                            </a>

                            <!-- Metadata -->
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $product->brand?->name ?? 'Component' }}</span>
                                <span class="text-slate-300">•</span>
                                <span class="text-[11px] text-slate-500">{{ $product->category->name }}</span>
                            </div>

                            <!-- Product Name -->
                            <h3 class="font-bold text-sm text-slate-900 leading-snug line-clamp-2 hover:text-blue-600 transition">
                                <a href="{{ route('product.show', $product->slug) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>

                            <div class="text-xs text-slate-400 font-mono mt-1">
                                SKU: {{ $product->sku }}
                            </div>

                            <!-- Stock Indicator -->
                            <div class="mt-2.5">
                                @if($product->stock_quantity <= 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Out of Stock
                                    </span>
                                @elseif($product->stock_quantity <= $product->low_stock_threshold)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> Low Stock ({{ $product->stock_quantity }} left)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> In Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Price & Add to Cart -->
                        <div class="mt-5 pt-4 border-t border-slate-100">
                            <div class="flex items-baseline justify-between mb-3">
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Price</span>
                                    <div class="flex items-baseline gap-1.5">
                                        @if($product->sale_price)
                                            <span class="font-black text-lg text-slate-900">${{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-xs text-slate-400 line-through">${{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="font-black text-lg text-slate-900">${{ number_format($product->price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Add to Cart Form -->
                            <form action="{{ route('cart.add', $product->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button
                                    type="submit"
                                    @if($product->stock_quantity <= 0) disabled @endif
                                    class="w-full py-2.5 px-4 font-bold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer
                                    {{ $product->stock_quantity > 0 ? 'bg-blue-600 hover:bg-blue-500 text-white shadow-blue-600/25' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}"
                                >
                                    <i class="bi bi-cart-plus text-sm"></i>
                                    <span>{{ $product->stock_quantity > 0 ? 'Add to Cart' : 'Out of Stock' }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="pt-6 border-t border-slate-200">
                    {{ $products->links('vendor.pagination.storefront') }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white/95 backdrop-blur-sm rounded-3xl border border-slate-200/80 p-12 sm:p-16 text-center max-w-lg mx-auto space-y-4 shadow-xs">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center text-3xl shadow-inner">
                    <i class="bi bi-heart"></i>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">Your Wishlist is Currently Empty</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                    Save graphics cards, processors, motherboards, and PC components to your wishlist while shopping to easily track them and buy later.
                </p>
                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/25 transition">
                        <i class="bi bi-cpu"></i>
                        <span>Start Shopping Hardware</span>
                    </a>
                    <a href="{{ route('recently-viewed.index') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        <i class="bi bi-clock-history"></i>
                        <span>View Browsing History</span>
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    async function clearAllFavorites() {
        let confirmed = false;
        if (typeof window.confirmModal === 'function') {
            confirmed = await window.confirmModal({
                title: 'Clear All Favorites?',
                message: 'Are you sure you want to remove all saved components from your favorites wishlist?',
                confirmText: 'Yes, Clear All',
                cancelText: 'Keep Items',
                type: 'danger'
            });
        } else {
            confirmed = window.confirm('Are you sure you want to clear all your favorites?');
        }

        if (confirmed) {
            document.getElementById('clear-favorites-form').submit();
        }
    }

    function removeFavoriteCard(productId, btn) {
        if (typeof window.toggleFavorite === 'function') {
            window.toggleFavorite(productId, btn, function(data) {
                if (!data.is_favorited) {
                    const card = document.getElementById('favorite-card-' + productId);
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            card.remove();
                            const grid = document.getElementById('favorites-grid');
                            if (grid && grid.children.length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                }
            });
        }
    }
</script>
@endsection

