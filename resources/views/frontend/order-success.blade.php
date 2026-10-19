@extends('layouts.storefront')

@section('title', 'Order Receipt & Warranty Certificate - #' . $order->order_number . ' - JackSparow TECH')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 print:p-0 print:max-w-none">

    <!-- Top Action Bar (Hidden in Print) -->
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 print:hidden">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-1">
                <i class="bi bi-patch-check-fill text-emerald-500 text-sm"></i>
                <span>Order Placed & Warranty Registered</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Receipt & Warranty Documentation</h1>
            <p class="text-xs text-gray-500 mt-1">Keep this document as your official purchase proof and hardware warranty certificate.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm hover:shadow-md cursor-pointer">
                <i class="bi bi-printer text-sm"></i>
                <span>Print Certificate & Receipt</span>
            </button>
            <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm">
                <i class="bi bi-arrow-left text-sm"></i>
                <span>Continue Shopping</span>
            </a>
            @auth('frontend')
                <a href="{{ route('my-orders') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                    <i class="bi bi-box-seam text-sm"></i>
                    <span>My Orders</span>
                </a>
            @endauth
        </div>
    </div>

    <!-- Printable Invoice & Warranty Document Wrapper -->
    <div class="space-y-10">

        <!-- ========================================== -->
        <!-- DOCUMENT 1: OFFICIAL PURCHASE RECEIPT      -->
        <!-- ========================================== -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden print:border-none print:shadow-none print:p-0">
            <!-- Receipt Header -->
            <div class="p-6 sm:p-8 border-b border-gray-200 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex flex-wrap items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center font-black text-sm text-white">
                            JA
                        </div>
                        <span class="text-lg font-black tracking-tight text-white">JackSparow TECH</span>
                    </div>
                    <p class="text-xs text-slate-300">High-Performance Computing Hardware & Components</p>
                    <p class="text-[11px] text-slate-400 mt-1">100 Technology Plaza, Silicon Way | support@jacksparow-tech.test</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="inline-block px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-400/30 rounded-lg text-xs font-bold uppercase tracking-wider mb-2">
                        Official Receipt
                    </span>
                    <div class="font-mono text-xl sm:text-2xl font-black text-white">#{{ $order->order_number }}</div>
                    <div class="text-xs text-slate-300 mt-0.5">Date: {{ $order->created_at->format('M d, Y h:i A') }}</div>
                </div>
            </div>

            <!-- Customer & Order Meta Information -->
            <div class="p-6 sm:p-8 bg-gray-50/50 border-b border-gray-100 grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs text-gray-600">
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Billed To</span>
                    <strong class="text-sm font-bold text-gray-900 block">{{ $order->customer_name }}</strong>
                    <div class="text-gray-600 mt-0.5">{{ $order->customer_email }}</div>
                    <div class="text-gray-600">{{ $order->customer_phone }}</div>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Shipping Destination</span>
                    <strong class="text-sm font-bold text-gray-900 block">{{ $order->customer_name }}</strong>
                    <div class="text-gray-600 mt-0.5">{{ $order->shipping_address['street'] ?? 'Street address on file' }}</div>
                    <div class="text-gray-600">
                        {{ $order->shipping_address['city'] ?? '' }}{{ !empty($order->shipping_address['state']) ? ', ' . $order->shipping_address['state'] : '' }} {{ $order->shipping_address['postal_code'] ?? '' }}
                    </div>
                    <div class="text-gray-600">{{ $order->shipping_address['country'] ?? 'United States' }}</div>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-1.5">Payment & Fulfillment</span>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800">Method:</span>
                        <span class="uppercase font-mono font-bold text-gray-900">{{ str_replace('_', ' ', $order->payment_method) }}</span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="font-semibold text-gray-800">Payment Status:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $order->payment_status }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="font-semibold text-gray-800">Order Status:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-blue-100 text-blue-800">
                            {{ $order->status }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Receipt Items Table -->
            <div class="p-6 sm:p-8">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4">Itemized Hardware Purchases</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 uppercase tracking-wider text-[11px] font-bold">
                                <th class="py-3 px-2">Hardware Component</th>
                                <th class="py-3 px-2">SKU</th>
                                <th class="py-3 px-2 text-center">Qty</th>
                                <th class="py-3 px-2 text-right">Unit Price</th>
                                <th class="py-3 px-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($order->items as $item)
                                <tr>
                                    <td class="py-3.5 px-2">
                                        <div class="font-bold text-gray-900 text-sm">{{ $item->product_name }}</div>
                                        @if($item->product && $item->product->category)
                                            <span class="text-[11px] text-gray-500 font-medium">{{ $item->product->category->name }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-2 font-mono text-gray-500">{{ $item->product_sku ?: 'N/A' }}</td>
                                    <td class="py-3.5 px-2 text-center font-bold text-gray-900">{{ $item->quantity }}</td>
                                    <td class="py-3.5 px-2 text-right font-mono">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="py-3.5 px-2 text-right font-mono font-bold text-gray-900">${{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-400">No line items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Receipt Financial Summary -->
                <div class="mt-6 pt-6 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-start gap-4">
                    <div class="text-xs text-gray-500 max-w-sm">
                        <p class="font-semibold text-gray-700">Thank you for choosing JackSparow TECH!</p>
                        <p class="mt-1">All computer parts are rigorously tested prior to shipment and protected by our standard warranty policy below.</p>
                    </div>
                    <div class="w-full sm:w-72 space-y-2 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span class="font-mono font-semibold text-gray-900">${{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Estimated Tax (8%):</span>
                            <span class="font-mono font-semibold text-gray-900">${{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping & Handling:</span>
                            <span class="font-mono font-semibold text-gray-900">${{ number_format($order->shipping_fee, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600">
                                <span>Discounts:</span>
                                <span class="font-mono font-semibold">-${{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-black text-gray-900 pt-3 border-t border-gray-200">
                            <span>Grand Total:</span>
                            <span class="font-mono text-blue-600">${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- DOCUMENT 2: OFFICIAL WARRANTY CERTIFICATE -->
        <!-- ========================================== -->
        <div class="bg-white rounded-2xl border-2 border-indigo-200 shadow-md overflow-hidden relative print:border-2 print:border-gray-800 print:shadow-none print:break-before-page">
            <!-- Decorative Security Background Grid -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none bg-[radial-gradient(#4f46e5_1px,transparent_1px)] [background-size:16px_16px]"></div>

            <!-- Certificate Header Banner -->
            <div class="relative p-6 sm:p-8 bg-gradient-to-r from-indigo-900 via-blue-900 to-slate-900 text-white">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <i class="bi bi-shield-check text-2xl text-blue-400"></i>
                            <span class="text-xs font-black tracking-widest uppercase text-blue-300">JackSparow TECH &bull; Hardware Protection Plan</span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Warranty Certificate</h2>
                        <p class="text-xs text-indigo-200 mt-1">Authentic Manufacturer & Authorized System Integrator Guarantee</p>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-4 py-3 rounded-xl border border-white/20 text-center sm:text-right">
                        <div class="text-[10px] uppercase font-bold text-indigo-200 tracking-wider">Certificate ID</div>
                        <div class="font-mono font-black text-base sm:text-lg text-white">WAR-{{ $order->order_number }}</div>
                        <div class="text-[11px] text-emerald-400 font-semibold mt-0.5 flex items-center justify-center sm:justify-end gap-1">
                            <i class="bi bi-check-circle-fill text-xs"></i>
                            <span>Active & Verified</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certificate Core Details -->
            <div class="relative p-6 sm:p-8 space-y-6">
                <!-- Registration Specs Row -->
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 text-xs">
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-indigo-400">Registered Owner</span>
                        <strong class="text-gray-900 font-bold mt-0.5 block">{{ $order->customer_name }}</strong>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-indigo-400">Coverage Duration</span>
                        <strong class="text-gray-900 font-bold mt-0.5 block">24 Months Full Coverage</strong>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-indigo-400">Activation Date</span>
                        <span class="text-gray-800 font-semibold mt-0.5 block">{{ $order->created_at->format('F d, Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-indigo-400">Valid Until</span>
                        <span class="text-indigo-900 font-bold mt-0.5 block">{{ $order->created_at->copy()->addMonths(24)->format('F d, Y') }}</span>
                    </div>
                </div>

                <!-- Covered Components Matrix -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                        <i class="bi bi-cpu text-blue-600"></i>
                        <span>Covered Hardware Components</span>
                    </h3>
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px] font-bold border-b border-gray-200">
                                <tr>
                                    <th class="py-2.5 px-3">Item / Component</th>
                                    <th class="py-2.5 px-3">SKU / Model</th>
                                    <th class="py-2.5 px-3">Coverage Type</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="py-3 px-3 font-semibold text-gray-900">
                                            {{ $item->product_name }}
                                            <div class="text-[11px] text-gray-400">Quantity: {{ $item->quantity }}</div>
                                        </td>
                                        <td class="py-3 px-3 font-mono text-gray-600">{{ $item->product_sku ?: 'OEM-VERIFIED' }}</td>
                                        <td class="py-3 px-3 text-gray-600">
                                            <span class="inline-flex items-center gap-1 font-medium">
                                                <i class="bi bi-shield-fill text-indigo-500 text-xs"></i>
                                                <span>OEM + JackSparow Express RMA</span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                                <i class="bi bi-shield-check"></i>
                                                <span>Registered</span>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Warranty Terms & Security Stamp -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-4 border-t border-gray-200 text-xs text-gray-600">
                    <div class="sm:col-span-2 space-y-2">
                        <h4 class="font-bold text-gray-800 uppercase tracking-wider text-[11px]">Warranty Terms & Coverage Summary</h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600 text-[11px] leading-relaxed">
                            <li><strong>Defect Coverage:</strong> Covers defects in materials, manufacturing faults, and unexpected electronic component degradation.</li>
                            <li><strong>Rapid RMA Replacement:</strong> Immediate turnaround or direct hardware replacement within 48 business hours of receipt at our tech hub.</li>
                            <li><strong>Exclusions:</strong> Does not cover physical impact damage, water exposure, or unauthorized circuit modification/delidding.</li>
                            <li><strong>How to Claim:</strong> Submit an RMA request online via your account or contact support@jacksparow-tech.test quoting your Certificate ID <strong>WAR-{{ $order->order_number }}</strong>.</li>
                        </ul>
                    </div>
                    <div class="flex flex-col items-center justify-center p-4 rounded-xl border border-dashed border-indigo-300 bg-indigo-50/30 text-center">
                        <div class="w-14 h-14 rounded-full border-2 border-indigo-600 flex items-center justify-center text-indigo-700 mb-2">
                            <i class="bi bi-award text-2xl"></i>
                        </div>
                        <span class="text-[11px] font-black uppercase tracking-wider text-indigo-950">Official Guarantee Seal</span>
                        <span class="text-[10px] text-gray-500 font-mono mt-0.5">AUTH-SEC-{{ substr(md5($order->order_number), 0, 8) }}</span>
                        <span class="text-[9px] text-gray-400 mt-1">JackSparow Quality Assurance Dept.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
