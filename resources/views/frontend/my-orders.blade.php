@extends('layouts.storefront')

@section('title', 'My Hardware Orders - JackSparow TECH')

@section('content')
<div class="bg-gray-100 border-b border-gray-200 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">My Orders</h1>
        <p class="text-sm text-gray-500 mt-1">Track and review past computer hardware purchases</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="space-y-6">
        @forelse($orders as $order)
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="p-5 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4 text-xs">
                    <div class="flex flex-wrap items-center gap-6">
                        <div>
                            <span class="block font-bold text-gray-400 uppercase tracking-wider text-[11px]">Order Number</span>
                            <span class="font-mono font-extrabold text-gray-900 text-sm">{{ $order->order_number }}</span>
                        </div>
                        <div>
                            <span class="block font-bold text-gray-400 uppercase tracking-wider text-[11px]">Date Placed</span>
                            <span class="font-medium text-gray-800">{{ $order->created_at->format('M d, Y') }}</span>
                        </div>
                        <div>
                            <span class="block font-bold text-gray-400 uppercase tracking-wider text-[11px]">Total Paid</span>
                            <span class="font-extrabold text-gray-900">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase
                            @if($order->status === 'delivered') bg-emerald-100 text-emerald-800
                            @elseif($order->status === 'shipped') bg-indigo-100 text-indigo-800
                            @elseif($order->status === 'processing') bg-blue-100 text-blue-800
                            @elseif($order->status === 'cancelled') bg-rose-100 text-rose-800
                            @else bg-amber-100 text-amber-800 @endif
                        ">
                            {{ $order->status }}
                        </span>

                        <a href="{{ route('order.success', $order->order_number) }}" class="px-3 py-1.5 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 transition">
                            View Receipt
                        </a>
                    </div>
                </div>

                <div class="p-6 divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between text-xs sm:text-sm">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $item->product_name }}</h4>
                                <span class="text-xs text-gray-400 font-mono">SKU: {{ $item->product_sku }} | Qty: {{ $item->quantity }}</span>
                            </div>
                            <span class="font-bold text-gray-900">${{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center max-w-md mx-auto space-y-3">
                <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 text-gray-400 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="font-bold text-gray-900 text-base">No Orders Yet</h3>
                <p class="text-xs text-gray-500">You haven't placed any computer parts orders yet.</p>
                <div class="pt-2">
                    <a href="{{ route('shop') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-lg transition">
                        Shop Computer Hardware
                    </a>
                </div>
            </div>
        @endforelse

        <div class="pt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

