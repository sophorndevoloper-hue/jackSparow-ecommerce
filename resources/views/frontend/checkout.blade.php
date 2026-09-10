@extends('layouts.storefront')

@section('title', 'Secure Checkout - JackSparow TECH')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Checkout</h1>
        <p class="text-sm text-gray-500 mt-1">Provide your shipping details and select a payment method</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            <!-- Left Form: Customer & Shipping Details -->
            <div class="md:col-span-7 xl:col-span-8 space-y-8">
                <!-- Contact Information -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-xs space-y-4">
                    <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">1</span>
                        Contact Information
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Full Name *</label>
                            <input
                                type="text"
                                name="customer_name"
                                value="{{ old('customer_name', $user?->name) }}"
                                required
                                placeholder="e.g. Alex Mercer"
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
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
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('customer_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Phone Number *</label>
                            <input
                                type="tel"
                                name="customer_phone"
                                value="{{ old('customer_phone') }}"
                                required
                                placeholder="+1 (555) 019-2834"
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('customer_phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-xs space-y-4">
                    <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">2</span>
                        Shipping Address
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">Street Address *</label>
                            <input
                                type="text"
                                name="street"
                                value="{{ old('street') }}"
                                required
                                placeholder="123 Gaming Street, Apt 4B"
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('street') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">City *</label>
                            <input
                                type="text"
                                name="city"
                                value="{{ old('city') }}"
                                required
                                placeholder="Austin"
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('city') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">State / Province *</label>
                            <input
                                type="text"
                                name="state"
                                value="{{ old('state') }}"
                                required
                                placeholder="TX"
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('state') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Postal Code *</label>
                            <input
                                type="text"
                                name="postal_code"
                                value="{{ old('postal_code') }}"
                                required
                                placeholder="78701"
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('postal_code') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Country *</label>
                            <input
                                type="text"
                                name="country"
                                value="{{ old('country', 'United States') }}"
                                required
                                class="w-full text-sm px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            >
                            @error('country') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200 shadow-xs space-y-4">
                    <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">3</span>
                        Payment Method
                    </h2>

                    <div class="space-y-3 pt-2">
                        <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-200 hover:border-blue-500 cursor-pointer transition">
                            <span class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="credit_card" checked class="text-blue-600 focus:ring-blue-500">
                                <span>
                                    <strong class="block text-sm text-gray-900">Credit / Debit Card</strong>
                                    <span class="block text-xs text-gray-500">Instant checkout simulation (Visa, MasterCard, Amex)</span>
                                </span>
                            </span>
                            <span class="text-xs font-bold text-gray-400">Instant</span>
                        </label>

                        <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-200 hover:border-blue-500 cursor-pointer transition">
                            <span class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="cash_on_delivery" class="text-blue-600 focus:ring-blue-500">
                                <span>
                                    <strong class="block text-sm text-gray-900">Cash on Delivery (COD)</strong>
                                    <span class="block text-xs text-gray-500">Pay when your hardware components arrive at your doorstep</span>
                                </span>
                            </span>
                            <span class="text-xs font-bold text-gray-400">On Delivery</span>
                        </label>

                        <label class="flex items-center justify-between p-4 rounded-2xl border border-gray-200 hover:border-blue-500 cursor-pointer transition">
                            <span class="flex items-center gap-3">
                                <input type="radio" name="payment_method" value="bank_transfer" class="text-blue-600 focus:ring-blue-500">
                                <span>
                                    <strong class="block text-sm text-gray-900">Direct Bank Wire Transfer</strong>
                                    <span class="block text-xs text-gray-500">Bank details will be provided upon order confirmation</span>
                                </span>
                            </span>
                            <span class="text-xs font-bold text-gray-400">Manual Wire</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Order Notes (Optional)</label>
                        <textarea
                            name="customer_notes"
                            rows="2"
                            placeholder="Special shipping instructions, gate code, or delivery preference..."
                            class="w-full text-xs px-3.5 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                        >{{ old('customer_notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar: Order Summary Review -->
            <div class="md:col-span-5 xl:col-span-4 bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8 space-y-6 sticky top-28">
                <h2 class="font-extrabold text-gray-900 text-lg">Order Items ({{ count($cart) }})</h2>

                <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto pr-1">
                    @foreach($cart as $item)
                        <div class="py-3 flex items-center justify-between gap-3 text-xs">
                            <div>
                                <span class="font-bold text-gray-900">{{ $item['name'] }}</span>
                                <div class="text-gray-400">Qty: {{ $item['quantity'] }} × ${{ number_format($item['price'], 2) }}</div>
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
                            <span class="font-bold text-emerald-600 uppercase">Free</span>
                        @else
                            <span class="font-semibold text-gray-900">${{ number_format($shipping, 2) }}</span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4 flex justify-between items-baseline">
                    <span class="font-bold text-gray-900 text-base">Grand Total</span>
                    <span class="font-extrabold text-2xl text-blue-600">${{ number_format($total, 2) }}</span>
                </div>

                <button
                    type="submit"
                    class="w-full py-4 px-6 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-sm rounded-xl shadow-lg shadow-blue-600/30 transition flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Confirm & Place Order</span>
                </button>

                <div class="text-center text-xs text-gray-400">
                    By placing your order, you agree to the hardware warranty and return terms.
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

