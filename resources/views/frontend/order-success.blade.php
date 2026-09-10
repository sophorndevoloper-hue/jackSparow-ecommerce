@extends('layouts.storefront')

@section('title', 'Order Confirmed - JackSparow TECH')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6 sm:p-12 text-center">
        <!-- Success Check Icon -->
        <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 shadow-xs">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-2">
            Order Successfully Placed
        </span>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Thank You For Your Order!</h1>
        <p class="text-sm text-gray-500 max-w-md mx-auto mt-2">
            Your hardware parts order has been logged in our fulfillment warehouse. A confirmation notice will be processed.
        </p>

        <!-- Order Metadata Strip -->
        <div class="my-8 p-5 bg-gray-50 rounded-2xl border border-gray-100 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left">
            <div>
                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">Order Number</span>
                <strong class="font-mono text-sm text-gray-900 font-extrabold">{{ $order->order_number }}</strong>
            </div>
            <div>
                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">Order Date</span>
                <span class="text-xs font-semibold text-gray-800">{{ $order->created_at->format('M d, Y H:i') }}</span>
            </div>
            <div>
                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">Order Status</span>
                <span class="inline-block mt-0.5 text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 uppercase">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div>
                <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Amount</span>
                <strong class="text-base text-blue-600 font-extrabold">${{ number_format($order->total_amount, 2) }}</strong>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <div class="text-left border border-gray-200 rounded-2xl overflow-hidden mb-8">
            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200 font-bold text-xs uppercase tracking-wider text-gray-600">
                Components Ordered
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                    <div class="p-4 flex items-center justify-between text-xs sm:text-sm">
                        <div>
                            <span class="font-bold text-gray-900">{{ $item->product_name }}</span>
                            <div class="text-xs text-gray-400 font-mono">SKU: {{ $item->product_sku }} | Qty: {{ $item->quantity }}</div>
                        </div>
                        <span class="font-bold text-gray-900">${{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="bg-gray-50 p-4 border-t border-gray-200 space-y-1.5 text-xs text-gray-600">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-gray-900">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Tax:</span>
                    <span class="font-semibold text-gray-900">${{ number_format($order->tax_amount, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Shipping:</span>
                    <span class="font-semibold text-gray-900">${{ number_format($order->shipping_fee, 2) }}</span>
                </div>
                <div class="flex justify-between font-extrabold text-sm text-gray-900 pt-2 border-t border-gray-200">
                    <span>Total Paid / Due:</span>
                    <span class="text-blue-600">${{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Delivery & Payment Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-left mb-8">
            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Shipping Details</span>
                <p class="text-xs text-gray-800 font-semibold">{{ $order->customer_name }}</p>
                <p class="text-xs text-gray-600">{{ $order->shipping_address['street'] ?? '' }}</p>
                <p class="text-xs text-gray-600">{{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}</p>
                <p class="text-xs text-gray-600">{{ $order->customer_phone }}</p>
            </div>

            <div class="p-4 rounded-xl border border-gray-200 bg-gray-50">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Payment Information</span>
                <p class="text-xs text-gray-800 font-semibold uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                <p class="text-xs text-gray-600 mt-1">Payment Status: <strong class="uppercase text-emerald-600">{{ $order->payment_status }}</strong></p>
                @if($order->customer_notes)
                    <p class="text-xs text-gray-500 italic mt-2">Notes: "{{ $order->customer_notes }}"</p>
                @endif
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('shop') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-xs transition">
                Continue Shopping Hardware
            </a>
            @auth
                <a href="{{ route('my-orders') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs rounded-xl transition">
                    View My Orders
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection

