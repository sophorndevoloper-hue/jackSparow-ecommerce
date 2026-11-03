@extends('layouts.storefront')

@section('title', 'Secure Checkout - JackSparow TECH')

@section('content')
<!-- Page Header Banner -->
<div class="relative overflow-hidden bg-slate-950 border-b border-slate-800/80 py-8 text-white">
    <!-- Ambient decorative glow -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2.5" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-blue-400 transition flex items-center gap-1">
                        <i class="bi bi-house"></i> Home
                    </a>
                    <span class="text-slate-600">/</span>
                    <a href="{{ route('cart.index') }}" class="hover:text-blue-400 transition flex items-center gap-1">
                        <i class="bi bi-cart3"></i> Cart ({{ count($cart) }})
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-blue-400 font-bold">Checkout</span>
                </nav>

                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex flex-wrap items-center gap-3">
                    <span>Secure Checkout</span>
                    <span class="text-xs px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-shield-lock-fill"></i> 256-Bit SSL Encrypted
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1.5">
                    Review your shipping destination and select a secure retail payment method
                </p>
            </div>

            <!-- Step Navigation Indicator -->
            <div class="flex items-center gap-2 sm:gap-3 bg-slate-900/90 border border-slate-800 px-4 py-2.5 rounded-2xl shadow-inner backdrop-blur-sm self-start md:self-auto">
                <a href="{{ route('cart.index') }}" class="flex items-center gap-2 text-xs text-slate-400 hover:text-white transition">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                    <span class="font-medium">Cart</span>
                </a>
                <div class="w-6 h-px bg-slate-700"></div>
                <div class="flex items-center gap-2 text-xs font-bold text-white">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold shadow-sm shadow-blue-500/50">2</span>
                    <span class="text-blue-400">Checkout</span>
                </div>
                <div class="w-6 h-px bg-slate-700"></div>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="w-5 h-5 rounded-full bg-slate-800 text-slate-500 flex items-center justify-center text-[10px] font-bold">3</span>
                    <span>Complete</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-slate-50/70 min-h-[calc(100vh-280px)] py-10 relative overflow-hidden"
    style="background: radial-gradient(ellipse 80% 60% at 50% 0%, #f8fafc 0%, #f1f5f9 100%);">
    <!-- Ambient subtle background lighting -->
    <div class="absolute top-0 right-1/4 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>
    <div class="absolute bottom-1/3 left-10 w-96 h-96 bg-indigo-100/30 rounded-full blur-3xl pointer-events-none -z-0"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10"
         x-data="{ 
             isSubmitting: false,
             paymentMethod: '{{ old('payment_method', $paymentMethods->first()?->code ?? 'cash_on_delivery') }}'
         }">

        <!-- Top Error / Notification Alerts -->
        @if(session('error') || $errors->any())
        <div class="mb-8 p-4 sm:p-5 rounded-2xl bg-rose-50/90 border border-rose-200 shadow-sm text-rose-900 flex items-start gap-3.5 animate-fadeIn">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <i class="bi bi-exclamation-octagon-fill text-lg"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h4 class="font-bold text-sm text-rose-900">Please review the following to complete your order:</h4>
                @if(session('error'))
                    <p class="text-xs text-rose-700 mt-1 font-medium">{{ session('error') }}</p>
                @endif
                @if($errors->any())
                    <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST"
            @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Left Form: Customer, Shipping & Payment Selection -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-6 sm:space-y-8">
                    
                    <!-- Step 1: Customer Contact Information -->
                    <div class="bg-white/95 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <h2 class="text-base sm:text-lg font-black text-gray-900 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white text-xs flex items-center justify-center font-black shadow-sm shadow-blue-500/25">
                                    1
                                </span>
                                Contact Information
                            </h2>
                            @if($user)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="truncate max-w-[200px]">Signed in as <strong>{{ $user->name }}</strong></span>
                            </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Full Name <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input type="text" name="customer_name" value="{{ old('customer_name', $user?->name) }}"
                                        required placeholder="e.g. Alex Mercer"
                                        class="w-full text-sm pl-10 pr-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('customer_name') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                </div>
                                @error('customer_name') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Email Address <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" name="customer_email"
                                        value="{{ old('customer_email', $user?->email) }}" required
                                        placeholder="alex@example.com"
                                        class="w-full text-sm pl-10 pr-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('customer_email') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                </div>
                                @error('customer_email') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Phone Number <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-telephone"></i>
                                    </span>
                                    <input type="tel" name="customer_phone"
                                        value="{{ old('customer_phone', $user?->phone) }}" required
                                        placeholder="+1 (555) 019-2834"
                                        class="w-full text-sm pl-10 pr-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('customer_phone') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                </div>
                                @error('customer_phone') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Shipping & Delivery Destination -->
                    <div class="bg-white/95 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <h2 class="text-base sm:text-lg font-black text-gray-900 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white text-xs flex items-center justify-center font-black shadow-sm shadow-blue-500/25">
                                    2
                                </span>
                                Shipping Destination
                            </h2>
                            <span class="text-xs font-medium text-slate-500 flex items-center gap-1.5">
                                <i class="bi bi-truck text-blue-600"></i> Express Insured Dispatch
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Street Address <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-geo-alt"></i>
                                    </span>
                                    <input type="text" name="street" value="{{ old('street', $user?->address) }}" required
                                        placeholder="123 Silicon Boulevard, Suite 400"
                                        class="w-full text-sm pl-10 pr-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('street') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                </div>
                                @error('street') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    City <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="city" value="{{ old('city', $user?->city) }}" required
                                    placeholder="e.g. Austin or San Jose"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('city') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                @error('city') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    State / Province / Region <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="state" value="{{ old('state', $user?->state) }}" required
                                    placeholder="e.g. California (CA)"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('state') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                @error('state') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Postal / ZIP Code <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="postal_code"
                                    value="{{ old('postal_code', $user?->postal_code) }}" required placeholder="94016"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('postal_code') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                @error('postal_code') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">
                                    Country <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="country"
                                    value="{{ old('country', $user?->country ?: 'United States') }}" required
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border @error('country') border-rose-300 focus:border-rose-500 focus:ring-rose-500/20 @else border-slate-200 focus:border-blue-600 focus:ring-blue-600/20 @enderror rounded-xl focus:ring-2 transition-all">
                                @error('country') <p class="text-xs text-rose-600 mt-1 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Logistics & Packing Guarantee Badge -->
                        <div class="mt-4 p-3.5 rounded-2xl bg-blue-50/60 border border-blue-100 flex items-center justify-between text-xs text-blue-900">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                                    <i class="bi bi-box-seam"></i>
                                </span>
                                <div>
                                    <strong class="block font-bold">Standard Hardware Express Shipping</strong>
                                    <span class="text-[11px] text-blue-700/80">Packed in ESD anti-static bags and reinforced shipping foam.</span>
                                </div>
                            </div>
                            <span class="font-extrabold text-xs uppercase px-2.5 py-1 rounded-md bg-white border border-blue-200 text-blue-700 shrink-0">
                                @if($shipping == 0) Free @else ${{ number_format($shipping, 2) }} @endif
                            </span>
                        </div>
                    </div>

                    <!-- Step 3: Payment Method Selection -->
                    <div class="bg-white/95 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                            <h2 class="text-base sm:text-lg font-black text-gray-900 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white text-xs flex items-center justify-center font-black shadow-sm shadow-blue-500/25">
                                    3
                                </span>
                                Payment Method
                            </h2>
                            <span class="text-xs font-medium text-slate-500 flex items-center gap-1.5">
                                <i class="bi bi-shield-check text-emerald-600"></i> Encrypted Transaction
                            </span>
                        </div>

                        <!-- Payment Method Radio List -->
                        <div class="space-y-3 pt-1">
                            @forelse($paymentMethods as $index => $method)
                            <label
                                class="flex items-center justify-between p-4 rounded-2xl border cursor-pointer transition-all shadow-2xs group"
                                :class="paymentMethod === '{{ $method->code }}' 
                                    ? 'border-blue-600 bg-gradient-to-r from-blue-50/70 to-indigo-50/40 ring-1 ring-blue-600' 
                                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/60'">
                                <span class="flex items-center gap-3.5 min-w-0">
                                    <input type="radio" name="payment_method" value="{{ $method->code }}"
                                        x-model="paymentMethod"
                                        {{ old('payment_method', $loop->first ? $method->code : null) === $method->code ? 'checked' : '' }}
                                        class="text-blue-600 focus:ring-blue-500 w-4 h-4">
                                    <span
                                        class="w-11 h-11 rounded-xl flex items-center justify-center text-lg shrink-0 transition-colors"
                                        :class="paymentMethod === '{{ $method->code }}' ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30' : 'bg-slate-100/90 text-slate-700 border border-slate-200/60'">
                                        <i class="bi {{ $method->icon() }}"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <strong class="block text-sm text-gray-900 font-bold group-hover:text-blue-600 transition-colors">
                                            {{ $method->name }}
                                        </strong>
                                        @if($method->description)
                                        <span class="block text-xs text-gray-500 mt-0.5 truncate max-w-sm sm:max-w-md">
                                            {{ $method->description }}
                                        </span>
                                        @endif
                                    </span>
                                </span>
                                
                                <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md shrink-0 ml-3 transition-colors"
                                    :class="paymentMethod === '{{ $method->code }}' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 border border-slate-200/60'">
                                    @if($method->code === 'credit_card')
                                    Instant Charge
                                    @elseif($method->code === 'cash_on_delivery')
                                    Pay on Delivery
                                    @elseif($method->code === 'aba_pay')
                                    KHQR Scan
                                    @elseif($method->code === 'bank_transfer')
                                    Wire Transfer
                                    @else
                                    Active
                                    @endif
                                </span>
                            </label>
                            @empty
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill text-amber-600"></i>
                                <span>No payment methods are currently active. Please contact support.</span>
                            </div>
                            @endforelse
                        </div>

                        @error('payment_method')
                        <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Order Notes Section -->
                        <div class="pt-4 border-t border-slate-100">
                            <label class="block text-xs font-bold text-gray-700 mb-1.5 flex items-center justify-between">
                                <span>Delivery Notes &amp; Special Instructions</span>
                                <span class="text-slate-400 font-normal text-[11px]">Optional</span>
                            </label>
                            <textarea name="customer_notes" rows="2"
                                placeholder="E.g. Apartment gate buzzer #42, please call upon arrival, leave with reception..."
                                class="w-full text-xs sm:text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all">{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Order Summary Review -->
                <div class="lg:col-span-5 xl:col-span-4 bg-white/95 backdrop-blur-sm rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-7 space-y-6 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                        <h2 class="font-extrabold text-gray-900 text-base sm:text-lg flex items-center gap-2">
                            <span>Order Summary</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold">
                                {{ count($cart) }} {{ str('item')->plural(count($cart)) }}
                            </span>
                        </h2>
                        <a href="{{ route('cart.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-bold transition flex items-center gap-1">
                            <i class="bi bi-pencil-square"></i> Edit Cart
                        </a>
                    </div>

                    <!-- Free Shipping Progress Tracker -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            @if($shipping == 0)
                            <span class="font-bold text-emerald-700 flex items-center gap-1.5">
                                <i class="bi bi-patch-check-fill text-emerald-600"></i> Free Express Shipping Unlocked!
                            </span>
                            <span class="font-extrabold text-emerald-700">100%</span>
                            @else
                            <span class="text-slate-600 font-medium">
                                Add <strong class="text-blue-600 font-bold">${{ number_format($amountToFreeShipping, 2) }}</strong> for FREE Shipping
                            </span>
                            <span class="text-slate-400 font-bold">{{ $shippingProgress }}%</span>
                            @endif
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $shipping == 0 ? 'bg-emerald-500' : 'bg-gradient-to-r from-blue-500 to-indigo-600' }}"
                                style="width: {{ $shippingProgress }}%;"></div>
                        </div>
                    </div>

                    <!-- Order Items Scrollable Container -->
                    <div class="divide-y divide-slate-100 max-h-72 overflow-y-auto pr-1 space-y-1">
                        @foreach($cart as $item)
                        <div class="py-3 first:pt-1 last:pb-1 flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center shrink-0 overflow-hidden p-1">
                                    @if(!empty($item['image_url']))
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                        class="w-full h-full object-contain"
                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';">
                                    @elseif(!empty($item['image']))
                                    <img src="{{ asset('storage/' . ltrim($item['image'], '/')) }}"
                                        alt="{{ $item['name'] }}"
                                        class="w-full h-full object-contain"
                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/placeholder-product.svg') }}';">
                                    @else
                                    <i class="bi bi-cpu text-blue-600 text-lg"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-gray-900 truncate block text-xs hover:text-blue-600 transition" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </span>
                                    <div class="text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>Qty: <strong class="text-slate-700">{{ $item['quantity'] }}</strong></span>
                                        <span>•</span>
                                        <span>${{ number_format($item['price'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="font-extrabold text-gray-900 shrink-0 text-sm">
                                ${{ number_format($item['price'] * $item['quantity'], 2) }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Financial Summary Breakdown -->
                    <div class="border-t border-slate-100 pt-4 space-y-2.5 text-xs text-slate-600">
                        <div class="flex justify-between items-center">
                            <span>Hardware Subtotal</span>
                            <span class="font-bold text-slate-900">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Estimated Sales Tax (8%)</span>
                            <span class="font-bold text-slate-900">${{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Standard Express Shipping</span>
                            @if($shipping == 0)
                            <span class="font-black text-emerald-600 uppercase text-[11px] tracking-wider bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                Free Delivery
                            </span>
                            @else
                            <span class="font-bold text-slate-900">${{ number_format($shipping, 2) }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Grand Total Card -->
                    <div class="border border-blue-100 bg-gradient-to-br from-blue-50/90 via-blue-50/40 to-indigo-50/60 rounded-2xl p-4 flex justify-between items-center shadow-2xs">
                        <div>
                            <span class="block text-[11px] font-black uppercase tracking-wider text-blue-900">Grand Total</span>
                            <span class="text-[11px] text-blue-700/80">All taxes &amp; shipping included</span>
                        </div>
                        <span class="font-black text-2xl sm:text-3xl text-blue-600 tracking-tight">
                            ${{ number_format($total, 2) }}
                        </span>
                    </div>

                    <!-- Order Action Submit Button -->
                    <button type="submit" :disabled="isSubmitting"
                        :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''"
                        class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 via-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-black text-sm rounded-2xl shadow-lg shadow-blue-600/30 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="isSubmitting" style="display: none;" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!isSubmitting">Confirm &amp; Place Order • ${{ number_format($total, 2) }}</span>
                        <span x-show="isSubmitting" style="display: none;">Securing Your Hardware Order...</span>
                    </button>

                    <!-- Trust & Guarantee Footer Badges -->
                    <div class="text-center text-xs text-slate-400 space-y-2 pt-2 border-t border-slate-100">
                        <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 font-medium">
                            <span class="flex items-center justify-center gap-1.5 p-1.5 bg-slate-50 rounded-lg border border-slate-100">
                                <i class="bi bi-shield-check text-emerald-500"></i> Official Warranty
                            </span>
                            <span class="flex items-center justify-center gap-1.5 p-1.5 bg-slate-50 rounded-lg border border-slate-100">
                                <i class="bi bi-arrow-counterclockwise text-blue-500"></i> 30-Day Returns
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400">
                            By placing this order, you agree to our hardware fulfillment &amp; return policy.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
