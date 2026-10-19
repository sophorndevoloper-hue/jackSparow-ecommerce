@extends('layouts.storefront')

@section('title', 'My Hardware Orders - JackSparow TECH')

@section('content')
<!-- Dark Modern Tech Header Banner -->
<div class="relative overflow-hidden bg-slate-950 border-b border-slate-800/80 py-8 text-white">
    <!-- Ambient decorative glow -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-blue-400 transition flex items-center gap-1.5">
                        <i class="bi bi-house-door"></i> Home
                    </a>
                    <span class="text-slate-600">/</span>
                    <a href="{{ route('customer.profile') }}" class="hover:text-blue-400 transition">My Account</a>
                    <span class="text-slate-600">/</span>
                    <span class="text-blue-400 font-bold">Order History</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                    <span>Hardware Order History</span>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-blue-500/15 text-blue-400 border border-blue-500/30 font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-shield-check text-emerald-400"></i> Warranty Protected
                    </span>
                </h1>
                <p class="text-xs text-slate-400 mt-1">Track component shipments, review hardware warranties, and access invoice receipts</p>
            </div>

            <!-- Quick Action Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md shadow-blue-600/25 transition">
                    <i class="bi bi-cpu"></i>
                    <span>Browse Hardware Catalog</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Main Canvas with Ambient Radial Background -->
<div class="bg-slate-50/70 min-h-[calc(100vh-280px)] py-10 relative overflow-hidden" style="background: radial-gradient(ellipse 80% 60% at 50% 0%, #f8fafc 0%, #f1f5f9 100%);">
    <!-- Ambient subtle background lighting -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute bottom-1/3 left-10 w-96 h-96 bg-indigo-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 space-y-8">

        <!-- Summary Stat Cards -->
        @if(($totalOrdersCount ?? $orders->total()) > 0)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Stat 1: Total Orders -->
                <div class="bg-white/95 backdrop-blur-sm p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Orders</span>
                        <span class="text-xl font-extrabold text-slate-900">{{ $totalOrdersCount ?? $orders->total() }}</span>
                    </div>
                </div>

                <!-- Stat 2: Active Shipments -->
                <div class="bg-white/95 backdrop-blur-sm p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">In Progress</span>
                        <span class="text-xl font-extrabold text-slate-900">{{ $activeOrdersCount ?? 0 }}</span>
                    </div>
                </div>

                <!-- Stat 3: Delivered Orders -->
                <div class="bg-white/95 backdrop-blur-sm p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Completed</span>
                        <span class="text-xl font-extrabold text-slate-900">{{ $deliveredOrdersCount ?? 0 }}</span>
                    </div>
                </div>

                <!-- Stat 4: Total Investment -->
                <div class="bg-white/95 backdrop-blur-sm p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center text-lg shrink-0">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Spent</span>
                        <span class="text-xl font-extrabold text-slate-900">${{ number_format($totalSpent ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Order List Section -->
        <div class="space-y-6">
            @forelse($orders as $order)
                <div class="bg-white/95 backdrop-blur-sm rounded-3xl border border-slate-200/80 shadow-xs hover:border-slate-300 transition-all overflow-hidden">
                    <!-- Order Top Header Strip -->
                    <div class="p-5 sm:p-6 bg-slate-50/70 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs">
                        <div class="flex flex-wrap items-center gap-6 sm:gap-8">
                            <!-- Order ID -->
                            <div>
                                <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Order Number</span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono font-black text-slate-900 text-sm tracking-tight">#{{ $order->order_number }}</span>
                                </div>
                            </div>

                            <!-- Date Placed -->
                            <div>
                                <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Date Placed</span>
                                <span class="font-semibold text-slate-800 text-xs mt-0.5 block">{{ $order->created_at->format('M d, Y') }}</span>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Payment Method</span>
                                <span class="inline-flex items-center gap-1.5 font-semibold text-slate-700 text-xs mt-0.5">
                                    @if($order->payment_method === 'credit_card')
                                        <i class="bi bi-credit-card-2-front text-blue-600"></i> Credit Card
                                    @elseif($order->payment_method === 'aba_pay')
                                        <i class="bi bi-qr-code text-blue-600"></i> ABA KHQR
                                    @elseif($order->payment_method === 'cash_on_delivery')
                                        <i class="bi bi-cash-stack text-emerald-600"></i> Cash on Delivery
                                    @elseif($order->payment_method === 'bank_transfer')
                                        <i class="bi bi-bank text-indigo-600"></i> Wire Transfer
                                    @else
                                        <i class="bi bi-wallet2 text-slate-500"></i> {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Standard')) }}
                                    @endif
                                </span>
                            </div>

                            <!-- Total Paid -->
                            <div>
                                <span class="block font-bold text-slate-400 uppercase tracking-wider text-[10px]">Total Amount</span>
                                <span class="font-black text-blue-600 text-sm sm:text-base mt-0.5 block">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Status & Action Buttons -->
                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Order Status Badge -->
                            <span class="px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs
                                @if($order->status === 'delivered') bg-emerald-50 text-emerald-700 border border-emerald-200
                                @elseif($order->status === 'shipped') bg-indigo-50 text-indigo-700 border border-indigo-200
                                @elseif($order->status === 'processing') bg-blue-50 text-blue-700 border border-blue-200
                                @elseif($order->status === 'cancelled') bg-rose-50 text-rose-700 border border-rose-200
                                @else bg-amber-50 text-amber-700 border border-amber-200 @endif
                            ">
                                @if($order->status === 'delivered')
                                    <i class="bi bi-check-circle-fill text-emerald-500"></i>
                                    <span>Delivered</span>
                                @elseif($order->status === 'shipped')
                                    <i class="bi bi-truck text-indigo-500"></i>
                                    <span>In Transit</span>
                                @elseif($order->status === 'processing')
                                    <i class="bi bi-gear-fill text-blue-500 animate-spin"></i>
                                    <span>Preparing Rig</span>
                                @elseif($order->status === 'cancelled')
                                    <i class="bi bi-x-circle-fill text-rose-500"></i>
                                    <span>Cancelled</span>
                                @else
                                    <i class="bi bi-clock-fill text-amber-500"></i>
                                    <span>Order Placed</span>
                                @endif
                            </span>

                            <!-- View Receipt Button -->
                            <a href="{{ route('order.success', $order->order_number) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 transition shadow-2xs">
                                <i class="bi bi-receipt text-blue-600"></i>
                                <span>View Receipt & Warranty</span>
                            </a>
                        </div>
                    </div>

                    <!-- Progress Stepper (Only for active / completed orders) -->
                    @if($order->status !== 'cancelled')
                        @php
                            $stepIndex = match($order->status) {
                                'delivered' => 4,
                                'shipped' => 3,
                                'processing' => 2,
                                default => 1,
                            };
                        @endphp
                        <div class="px-6 py-4 bg-white border-b border-slate-100 hidden sm:block">
                            <div class="max-w-2xl mx-auto flex items-center justify-between relative">
                                <!-- Background Line -->
                                <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-1 bg-slate-100 -z-0"></div>
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 h-1 bg-blue-600 -z-0 transition-all duration-500"
                                     style="width: {{ $stepIndex === 1 ? '10%' : ($stepIndex === 2 ? '40%' : ($stepIndex === 3 ? '75%' : '96%')) }};"></div>

                                <!-- Step 1: Placed -->
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $stepIndex >= 1 ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-200 text-slate-500' }}">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 mt-1">Placed</span>
                                </div>

                                <!-- Step 2: Processing -->
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $stepIndex >= 2 ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-200 text-slate-500' }}">
                                        @if($stepIndex > 2) <i class="bi bi-check-lg"></i> @else 2 @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 mt-1">Processing</span>
                                </div>

                                <!-- Step 3: Shipped -->
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $stepIndex >= 3 ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-200 text-slate-500' }}">
                                        @if($stepIndex > 3) <i class="bi bi-check-lg"></i> @else 3 @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 mt-1">Shipped</span>
                                </div>

                                <!-- Step 4: Delivered -->
                                <div class="flex flex-col items-center relative z-10">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $stepIndex >= 4 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-200 text-slate-500' }}">
                                        @if($stepIndex >= 4) <i class="bi bi-check-lg"></i> @else 4 @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 mt-1">Delivered</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items List -->
                    <div class="p-6 divide-y divide-slate-100">
                        @foreach($order->items as $item)
                            <div class="py-3.5 first:pt-0 last:pb-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs sm:text-sm">
                                <!-- Product details & thumbnail -->
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center shrink-0 overflow-hidden p-1">
                                        @if(!empty($item->product?->image_url))
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-contain">
                                        @elseif(!empty($item->product?->image))
                                            <img src="{{ asset('storage/' . ltrim($item->product->image, '/')) }}" alt="{{ $item->product_name }}" class="w-full h-full object-contain">
                                        @else
                                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-900 text-sm truncate">
                                            @if($item->product && $item->product->slug)
                                                <a href="{{ route('product.show', $item->product->slug) }}" class="hover:text-blue-600 transition">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                {{ $item->product_name }}
                                            @endif
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 mt-0.5">
                                            <span class="font-mono text-slate-400">SKU: {{ $item->product_sku }}</span>
                                            <span>•</span>
                                            <span>Qty: <strong class="text-slate-800">{{ $item->quantity }}</strong> × ${{ number_format($item->unit_price, 2) }}</span>
                                            <span>•</span>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                                <i class="bi bi-shield-check"></i> Official Warranty
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item Line Price -->
                                <div class="text-left sm:text-right shrink-0">
                                    <span class="font-extrabold text-slate-900 text-sm sm:text-base">${{ number_format($item->total_price, 2) }}</span>
                                    @if($item->product && $item->product->slug)
                                        <div class="mt-1">
                                            <a href="{{ route('product.show', $item->product->slug) }}" class="text-[11px] font-semibold text-blue-600 hover:underline">
                                                Buy Again &rarr;
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Card Footer Summary Strip -->
                    <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 text-slate-500 text-xs">
                            <i class="bi bi-geo-alt text-blue-600"></i>
                            <span>
                                Destination:
                                <strong class="text-slate-800">
                                    {{ is_array($order->shipping_address) ? ($order->shipping_address['street'] ?? 'Saved Address') . ', ' . ($order->shipping_address['city'] ?? '') : ($order->shipping_address ?? 'Saved Address') }}
                                </strong>
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('order.success', $order->order_number) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1">
                                <span>Download PDF / Print Receipt</span>
                                <i class="bi bi-arrow-right text-[11px]"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Modern Empty State -->
                <div class="bg-white/95 backdrop-blur-sm rounded-3xl border border-slate-200/80 p-12 text-center max-w-lg mx-auto space-y-4 shadow-xs">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center text-2xl shadow-inner">
                        <i class="bi bi-motherboard"></i>
                    </div>
                    <h3 class="text-xl font-extrabold text-slate-900">No Hardware Orders Yet</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                        You haven't placed any computer hardware or component orders yet. Explore our selection of CPUs, GPUs, motherboards, and memory to build your custom rig.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-md shadow-blue-600/25 transition">
                            <i class="bi bi-cpu"></i>
                            <span>Explore Computer Parts Catalog</span>
                        </a>
                    </div>
                </div>
            @endforelse

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="pt-6 border-t border-slate-200">
                    {{ $orders->links('vendor.pagination.storefront') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
