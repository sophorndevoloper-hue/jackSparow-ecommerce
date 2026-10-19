@extends('layouts.storefront')

@section('title', 'About Us - ' . $siteName)

@section('content')
<div class="py-12 bg-slate-950 text-white border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs font-semibold uppercase tracking-wider mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Empowering PC Enthusiasts
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight">About {{ $siteName }}</h1>
        <p class="mt-3 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
            {{ $siteTagline }} &bull; Next-generation computer hardware, gaming rigs, and high-performance workstation solutions.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
    <!-- Story & Mission -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Our Story &amp; Dedication</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-2 mb-4">Built by Gamers, Tailored for Extreme Performance</h2>
            <p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-4">
                {{ $footerAbout }}
            </p>
            <p class="text-sm text-gray-600 leading-relaxed mb-6">
                From competitive esports athletes demanding 360Hz frame rates to machine learning engineers building deep learning models, we supply authentic factory-sealed components tested for peak efficiency and thermals.
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('shop') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm rounded-xl shadow-md shadow-blue-500/20 transition">
                    Browse All Hardware &rarr;
                </a>
                <a href="{{ route('contact') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-xs sm:text-sm rounded-xl transition">
                    Get In Touch
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                    ⚡
                </div>
                <h4 class="font-bold text-gray-900 text-base">Factory Direct</h4>
                <p class="text-xs text-gray-500">100% genuine retail packaging straight from authorized distributors.</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                    🛡️
                </div>
                <h4 class="font-bold text-gray-900 text-base">Warranty Shield</h4>
                <p class="text-xs text-gray-500">Full manufacturer warranty with serial number tracking on every order.</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg">
                    🚀
                </div>
                <h4 class="font-bold text-gray-900 text-base">Express Dispatch</h4>
                <p class="text-xs text-gray-500">Shockproof courier packaging and expedited freight handling.</p>
            </div>
            <div class="p-6 bg-white rounded-2xl border border-gray-200 shadow-sm space-y-2">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">
                    🔧
                </div>
                <h4 class="font-bold text-gray-900 text-base">Expert Support</h4>
                <p class="text-xs text-gray-500">Hardware compatibility checks and custom quote guidance.</p>
            </div>
        </div>
    </div>

    <!-- Official Warranty Section -->
    <div id="warranty" class="p-8 sm:p-12 rounded-3xl bg-slate-900 text-white border border-slate-800">
        <div class="max-w-3xl">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Guaranteed Authenticity</span>
            <h3 class="text-2xl sm:text-3xl font-extrabold mt-2 mb-4">Official Brand Warranty &amp; Serial Verification</h3>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6">
                Every CPU, GPU, motherboard, and power supply sold through {{ $siteName }} is assigned a verifiable serial number in our central warehouse registry. We honor manufacturer warranty claims and provide immediate technical guidance for returns or replacements.
            </p>
            <div class="flex flex-wrap items-center gap-6 text-xs sm:text-sm text-slate-300">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Instant Serial Lookup</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Hassle-free RMA Support</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Up to 10-Year Part Coverage</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

