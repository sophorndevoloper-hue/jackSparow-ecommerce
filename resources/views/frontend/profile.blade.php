@extends('layouts.storefront')

@section('title', 'My Customer Profile - JackSparow TECH')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <!-- Page Header (Matching Cart & Checkout Standards) -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">My Customer Profile</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage your account information, contact details, and default delivery address</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('my-orders') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 shadow-xs transition">
                <i class="bi bi-box-seam text-blue-600"></i>
                <span>My Orders</span>
            </a>
            <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                <i class="bi bi-cart3"></i>
                <span>View Cart</span>
            </a>
        </div>
    </div>

    <!-- Profile Main 2-Column Responsive Layout -->
    <div class="profile-layout-container">
        <!-- ================= LEFT COLUMN: Customer Summary Card ================= -->
        <aside class="profile-sidebar space-y-6">
            <!-- Customer Avatar & Account Tier Card -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-6 text-center">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-blue-500/25 mb-4" style="background: linear-gradient(135deg, #2563eb, #4f46e5); color: #ffffff;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <h2 class="text-base font-extrabold text-gray-900">{{ $user->name }}</h2>
                <p class="text-xs text-gray-500 mt-0.5 break-all">{{ $user->email }}</p>

                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-center">
                    @if($user->isSpecial())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-full text-[11px] font-extrabold uppercase tracking-wider">
                            <i class="bi bi-star-fill text-amber-500 text-[10px]"></i> VIP Special Tier
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-800 rounded-full text-[11px] font-extrabold uppercase tracking-wider">
                            <i class="bi bi-person-check-fill text-blue-600 text-[10px]"></i> Standard Customer
                        </span>
                    @endif
                </div>

                <!-- Account Metrics (Orders Count & Total Spent) -->
                <div class="profile-metrics-grid mt-6 pt-6 border-t border-gray-100 text-left">
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Orders</span>
                        <span class="text-base font-black text-gray-900">{{ $user->orders_count ?? 0 }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Spent</span>
                        <span class="text-base font-black text-blue-600">${{ number_format($user->total_spent ?? 0, 2) }}</span>
                    </div>
                </div>

                @if(! $user->isSpecial() && $user->ordersUntilSpecial() > 0)
                    <div class="mt-4 p-3 bg-amber-50/70 border border-amber-100 rounded-2xl text-[11px] text-amber-800 text-left">
                        <span class="font-bold">VIP Upgrade:</span> Place {{ $user->ordersUntilSpecial() }} more {{ Str::plural('order', $user->ordersUntilSpecial()) }} to automatically qualify for VIP Wholesale pricing!
                    </div>
                @endif
            </div>

            <!-- Quick Navigation Menu -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-3 space-y-1">
                <a href="{{ route('customer.profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-bold bg-blue-50 text-blue-700">
                    <i class="bi bi-person-lines-fill text-blue-600 text-sm"></i>
                    <span>Profile & Address Settings</span>
                </a>
                <a href="{{ route('my-orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-semibold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                    <i class="bi bi-box-seam text-gray-400 text-sm"></i>
                    <span>My Hardware Orders</span>
                </a>
                <a href="{{ route('shop') }}" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-xs font-semibold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                    <i class="bi bi-grid text-gray-400 text-sm"></i>
                    <span>Browse Hardware Catalog</span>
                </a>
            </div>
        </aside>

        <!-- ================= RIGHT COLUMN: Form Panels ================= -->
        <main class="profile-content space-y-8">
            <!-- 1. Personal & Delivery Details Form -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0">
                        <i class="bi bi-geo-alt-fill text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Personal and Delivery Details</h2>
                        <p class="text-xs text-gray-500">Edit your contact details and default shipping address for faster checkout</p>
                    </div>
                </div>

                @if($errors->any() && ! $errors->passwordUpdate->any())
                    <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <!-- Full Name & Email Row -->
                    <div class="form-row">
                        <div class="form-group-half">
                            <label for="name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <input id="name" 
                                   type="text" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
                                   required
                                   class="form-control-custom">
                        </div>

                        <div class="form-group-half">
                            <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Email Address <span class="text-rose-500">*</span>
                            </label>
                            <input id="email" 
                                   type="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required
                                   class="form-control-custom">
                        </div>
                    </div>

                    <!-- Phone Number & Company Row -->
                    <div class="form-row">
                        <div class="form-group-half">
                            <label for="phone" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Phone Number
                            </label>
                            <input id="phone" 
                                   type="text" 
                                   name="phone" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="+1 (555) 000-0000"
                                   class="form-control-custom">
                        </div>

                        <div class="form-group-half">
                            <label for="company_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Company / Business (Optional)
                            </label>
                            <input id="company_name" 
                                   type="text" 
                                   name="company_name" 
                                   value="{{ old('company_name', $user->company_name) }}" 
                                   placeholder="e.g. Acme Gaming Studio"
                                   class="form-control-custom">
                        </div>
                    </div>

                    <!-- Default Shipping Address -->
                    <div>
                        <label for="address" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Default Shipping Address
                        </label>
                        <input id="address" 
                               type="text" 
                               name="address" 
                               value="{{ old('address', $user->address) }}" 
                               placeholder="Street address, suite, unit, building..."
                               class="form-control-custom">
                    </div>

                    <!-- City, State, Postal Code Row -->
                    <div class="form-row">
                        <div class="form-group-third">
                            <label for="city" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                City
                            </label>
                            <input id="city" 
                                   type="text" 
                                   name="city" 
                                   value="{{ old('city', $user->city) }}" 
                                   placeholder="e.g. Seattle"
                                   class="form-control-custom">
                        </div>

                        <div class="form-group-third">
                            <label for="state" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                State / Province
                            </label>
                            <input id="state" 
                                   type="text" 
                                   name="state" 
                                   value="{{ old('state', $user->state) }}" 
                                   placeholder="e.g. Washington"
                                   class="form-control-custom">
                        </div>

                        <div class="form-group-third">
                            <label for="postal_code" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Postal / ZIP Code
                            </label>
                            <input id="postal_code" 
                                   type="text" 
                                   name="postal_code" 
                                   value="{{ old('postal_code', $user->postal_code) }}" 
                                   placeholder="e.g. 98101"
                                   class="form-control-custom">
                        </div>
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Country
                        </label>
                        <input id="country" 
                               type="text" 
                               name="country" 
                               value="{{ old('country', $user->country ?? 'United States') }}" 
                               placeholder="e.g. United States"
                               class="form-control-custom">
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <button type="submit" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md shadow-blue-600/20 transition inline-flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-check2"></i>
                            <span>Save Profile Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. Security & Change Password Form -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xs p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold shrink-0">
                        <i class="bi bi-shield-lock-fill text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900">Security and Password</h2>
                        <p class="text-xs text-gray-500">Ensure your customer account is using a strong, unique password</p>
                    </div>
                </div>

                @if($errors->passwordUpdate->any())
                    <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->passwordUpdate->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="max-w-md">
                        <label for="current_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <input id="current_password" 
                               type="password" 
                               name="current_password" 
                               required
                               autocomplete="current-password"
                               placeholder="Enter your current password"
                               class="form-control-custom">
                    </div>

                    <div class="form-row max-w-2xl">
                        <div class="form-group-half">
                            <label for="new_password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                New Password <span class="text-rose-500">*</span>
                            </label>
                            <input id="new_password" 
                                   type="password" 
                                   name="password" 
                                   required
                                   autocomplete="new-password"
                                   placeholder="Min. 8 characters"
                                   class="form-control-custom">
                        </div>

                        <div class="form-group-half">
                            <label for="new_password_confirmation" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Confirm New Password <span class="text-rose-500">*</span>
                            </label>
                            <input id="new_password_confirmation" 
                                   type="password" 
                                   name="password_confirmation" 
                                   required
                                   autocomplete="new-password"
                                   placeholder="Repeat new password"
                                   class="form-control-custom">
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="px-6 py-3.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl shadow-xs transition inline-flex items-center gap-2 cursor-pointer">
                            <i class="bi bi-key-fill"></i>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<style>
/* Robust Layout Styles ensuring the profile page NEVER squishes */
.profile-layout-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    width: 100%;
    align-items: flex-start;
}

@media (min-width: 1024px) {
    .profile-layout-container {
        flex-direction: row;
    }
}

.profile-sidebar {
    width: 100%;
    flex-shrink: 0;
}

@media (min-width: 1024px) {
    .profile-sidebar {
        width: 320px;
    }
}

.profile-content {
    flex: 1 1 0%;
    width: 100%;
    min-width: 0;
}

.profile-metrics-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.form-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

@media (min-width: 640px) {
    .form-row {
        flex-direction: row;
    }
}

.form-group-half {
    flex: 1 1 0%;
    min-width: 0;
    width: 100%;
}

.form-group-third {
    flex: 1 1 0%;
    min-width: 0;
    width: 100%;
}

.form-control-custom {
    width: 100% !important;
    padding: 0.625rem 1rem !important;
    border-radius: 0.75rem !important;
    border: 1px solid #e5e7eb !important;
    background-color: #ffffff !important;
    font-size: 0.875rem !important;
    color: #111827 !important;
    box-sizing: border-box !important;
    transition: all 0.15s ease-in-out !important;
    outline: none !important;
}

.form-control-custom:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12) !important;
}
</style>
@endsection
