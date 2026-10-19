@extends('layouts.storefront')

@section('title', 'Contact Support & RFQ - ' . $siteName)

@section('content')
<div class="py-12 bg-slate-950 text-white border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-semibold uppercase tracking-wider mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Hardware Support &amp; Sales Desk
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">Contact {{ $siteName }}</h1>
        <p class="mt-3 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
            Need hardware recommendations, custom PC quote pricing, or warranty assistance? We are here to help.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Direct Contact Cards -->
        <div class="space-y-6">
            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl shrink-0">
                    📞
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 text-sm">Call Support Hotline</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Mon - Sat, 8:00 AM - 8:00 PM</p>
                    <a href="tel:{{ $contactPhone }}" class="text-blue-600 font-bold text-sm hover:underline mt-2 inline-block">
                        {{ $contactPhone }}
                    </a>
                </div>
            </div>

            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl shrink-0">
                    ✉️
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 text-sm">Direct Email</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Average response within 2 hours</p>
                    <a href="mailto:{{ $contactEmail }}" class="text-blue-600 font-bold text-sm hover:underline mt-2 inline-block">
                        {{ $contactEmail }}
                    </a>
                </div>
            </div>

            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl shrink-0">
                    📍
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 text-sm">Corporate Headquarters</h4>
                    <p class="text-xs text-gray-500 mt-0.5">Central Logistics &amp; Distribution</p>
                    <p class="text-gray-700 text-sm font-medium mt-1">
                        {{ $contactAddress }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Contact / Quote Inquiry Form -->
        <div class="lg:col-span-2 p-8 bg-white rounded-3xl border border-gray-200 shadow-sm">
            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-900 mb-2">Send Us an Inquiry or RFQ</h3>
            <p class="text-xs sm:text-sm text-gray-500 mb-6">Fill out the form below and our hardware engineering team will get back to you with specs and availability.</p>

            <form action="#" method="POST" onsubmit="event.preventDefault(); window.toast('Thank you! Your inquiry has been received. Our team will contact you shortly.', 'success', 'Inquiry Received');">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Your Full Name</label>
                        <input type="text" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-600 focus:bg-white" placeholder="John Gamer">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                        <input type="email" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-600 focus:bg-white" placeholder="john@example.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Phone Number (Optional)</label>
                        <input type="text" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-600 focus:bg-white" placeholder="+1 (555) 000-0000">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Inquiry Subject</label>
                        <select class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-600 focus:bg-white">
                            <option>Custom PC Build &amp; Part Compatibility</option>
                            <option>Wholesale B2B / Volume RFQ</option>
                            <option>Warranty &amp; Serial Verification</option>
                            <option>Order Shipping &amp; Logistics</option>
                            <option>Other Question</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Your Message or Parts List</label>
                    <textarea rows="4" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-600 focus:bg-white" placeholder="Specify required components, CPU/GPU models, quantities or questions..."></textarea>
                </div>

                <button type="submit" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/25 transition flex items-center gap-2">
                    <span>Submit Inquiry</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

