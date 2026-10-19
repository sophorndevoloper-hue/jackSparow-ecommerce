@extends('layouts.storefront')

@section('title', 'Secure Checkout - JackSparow TECH')

@section('content')
<div class="relative overflow-hidden bg-slate-950 border-b border-slate-800/80 py-8 text-white">
    <!-- Ambient decorative glow -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-2" aria-label="Breadcrumb">
                    <a href="{{ route('cart.index') }}" class="hover:text-blue-400 transition flex items-center gap-1.5">
                        <i class="bi bi-cart3"></i> Cart
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="text-blue-400">Checkout</span>
                    <span class="text-slate-600">/</span>
                    <span class="text-slate-500">Order Completed</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight flex items-center gap-3">
                    <span>Secure Checkout</span>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 font-semibold inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-shield-lock-fill"></i> 256-Bit SSL Encrypted
                    </span>
                </h1>
                <p class="text-xs text-slate-400 mt-1">Review your delivery address and choose an authorized payment method</p>
            </div>

            <!-- Step Navigation Indicator -->
            <div class="hidden sm:flex items-center gap-2.5 bg-slate-900/90 border border-slate-800 px-4 py-2.5 rounded-2xl shadow-inner backdrop-blur-sm">
                <a href="{{ route('cart.index') }}" class="flex items-center gap-2 text-xs text-slate-400 hover:text-white transition">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                    <span>Cart</span>
                </a>
                <div class="w-6 h-px bg-slate-700"></div>
                <div class="flex items-center gap-2 text-xs font-bold text-white">
                    <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                    <span class="text-blue-300">Checkout</span>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10" x-data="{ isSubmitting: false }">
        <form action="{{ route('checkout.process') }}" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                <!-- Left Form: Customer & Shipping Details -->
                <div class="md:col-span-7 xl:col-span-8 space-y-8">
                    <!-- Contact Information -->
                    <div class="bg-white/95 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white text-xs flex items-center justify-center font-black shadow-sm shadow-blue-500/25">1</span>
                                Contact Information
                            </h2>
                            @if($user)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Signed in as {{ $user->name }}
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Full Name *</label>
                                <input
                                    type="text"
                                    name="customer_name"
                                    value="{{ old('customer_name', $user?->name) }}"
                                    required
                                    placeholder="e.g. Alex Mercer"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('customer_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Email Address *</label>
                                <input
                                    type="email"
                                    name="customer_email"
                                    value="{{ old('customer_email', $user?->email) }}"
                                    required
                                    placeholder="alex@example.com"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('customer_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number *</label>
                                <input
                                    type="tel"
                                    name="customer_phone"
                                    value="{{ old('customer_phone', $user?->phone) }}"
                                    required
                                    placeholder="+1 (555) 019-2834"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('customer_phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="bg-white/95 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white text-xs flex items-center justify-center font-black shadow-sm shadow-blue-500/25">2</span>
                            Shipping Address
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Street Address *</label>
                                <input
                                    type="text"
                                    name="street"
                                    value="{{ old('street', $user?->address) }}"
                                    required
                                    placeholder="123 Gaming Street, Apt 4B"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('street') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">City *</label>
                                <input
                                    type="text"
                                    name="city"
                                    value="{{ old('city', $user?->city) }}"
                                    required
                                    placeholder="Austin"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('city') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">State / Province *</label>
                                <input
                                    type="text"
                                    name="state"
                                    value="{{ old('state', $user?->state) }}"
                                    required
                                    placeholder="TX"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('state') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Postal Code *</label>
                                <input
                                    type="text"
                                    name="postal_code"
                                    value="{{ old('postal_code', $user?->postal_code) }}"
                                    required
                                    placeholder="78701"
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('postal_code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Country *</label>
                                <input
                                    type="text"
                                    name="country"
                                    value="{{ old('country', $user?->country ?: 'United States') }}"
                                    required
                                    class="w-full text-sm px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                                >
                                @error('country') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="bg-white/95 backdrop-blur-sm p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                        <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white text-xs flex items-center justify-center font-black shadow-sm shadow-blue-500/25">3</span>
                            Payment Method
                        </h2>

                        <div class="space-y-3 pt-2">
                            @forelse($paymentMethods as $index => $method)
                                <label class="flex items-center justify-between p-4 rounded-2xl border border-slate-200 bg-white hover:border-blue-400 hover:bg-slate-50/60 cursor-pointer transition-all has-[:checked]:border-blue-600 has-[:checked]:bg-gradient-to-r has-[:checked]:from-blue-50/60 has-[:checked]:to-indigo-50/30 has-[:checked]:ring-1 has-[:checked]:ring-blue-600 shadow-2xs">
                                    <span class="flex items-center gap-3.5">
                                        <input type="radio"
                                               name="payment_method"
                                               value="{{ $method->code }}"
                                               {{ old('payment_method', $loop->first ? $method->code : null) === $method->code ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="w-10 h-10 rounded-xl bg-slate-100/80 border border-slate-200/60 flex items-center justify-center text-slate-700 text-lg shrink-0">
                                            <i class="bi {{ $method->icon() }}"></i>
                                        </span>
                                        <span>
                                            <strong class="block text-sm text-gray-900">{{ $method->name }}</strong>
                                            @if($method->description)
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ $method->description }}</span>
                                            @endif
                                        </span>
                                    </span>
                                    <span class="text-xs font-bold text-gray-400 capitalize shrink-0 ml-3">
                                        @if($method->code === 'credit_card')
                                            Instant
                                        @elseif($method->code === 'cash_on_delivery')
                                            On Delivery
                                        @elseif($method->code === 'aba_pay')
                                            KHQR Scan
                                        @elseif($method->code === 'bank_transfer')
                                            Manual Wire
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

                        <div class="pt-4 border-t border-gray-100">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Order Notes (Optional)</label>
                            <textarea
                                name="customer_notes"
                                rows="2"
                                placeholder="Special shipping instructions, gate code, or delivery preference..."
                                class="w-full text-xs px-4 py-2.5 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all"
                            >{{ old('customer_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Order Summary Review -->
                <div class="md:col-span-5 xl:col-span-4 bg-white/95 backdrop-blur-sm rounded-3xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6 sticky top-28">
                    <h2 class="font-extrabold text-gray-900 text-lg">Order Items ({{ count($cart) }})</h2>

                    <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto pr-1">
                        @foreach($cart as $item)
                            <div class="py-3 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center shrink-0 overflow-hidden p-0.5">
                                        @if(!empty($item['image_url']))
                                            <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain">
                                        @elseif(!empty($item['image']))
                                            <img src="{{ asset('storage/' . ltrim($item['image'], '/')) }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain">
                                        @else
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="font-bold text-gray-900 truncate block">{{ $item['name'] }}</span>
                                        <div class="text-gray-400">Qty: {{ $item['quantity'] }} × ${{ number_format($item['price'], 2) }}</div>
                                    </div>
                                </div>
                                <span class="font-bold text-gray-900 shrink-0">
                                    ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-2 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Parts Subtotal</span>
                            <span class="font-semibold text-gray-900">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Estimated Tax (8%)</span>
                            <span class="font-semibold text-gray-900">${{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Shipping Fee</span>
                            @if($shipping == 0)
                                <span class="font-bold text-emerald-600 uppercase text-xs tracking-wider">Free (Over $150)</span>
                            @else
                                <span class="font-semibold text-gray-900">${{ number_format($shipping, 2) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="border border-blue-100 bg-gradient-to-br from-blue-50/80 via-blue-50/40 to-indigo-50/50 rounded-2xl p-4 flex justify-between items-center">
                        <div>
                            <span class="block text-[11px] font-black uppercase tracking-wider text-blue-900">Grand Total</span>
                            <span class="text-xs text-blue-700/70">Taxes & shipping included</span>
                        </div>
                        <span class="font-black text-2xl sm:text-3xl text-blue-600 tracking-tight">${{ number_format($total, 2) }}</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''"
                        class="w-full py-4 px-6 bg-gradient-to-r from-blue-600 via-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/30 active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="isSubmitting" style="display: none;" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-show="!isSubmitting">Confirm & Place Order</span>
                        <span x-show="isSubmitting" style="display: none;">Processing Order...</span>
                    </button>

                    <div class="text-center text-xs text-gray-400 space-y-1">
                        <p class="flex items-center justify-center gap-1.5 text-slate-500">
                            <i class="bi bi-shield-check text-emerald-500"></i> Official Manufacturer Warranty Included
                        </p>
                        <p class="text-[11px]">By placing your order, you agree to our hardware return terms.</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

