<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-person-plus-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management &bull; Customers</p>
                    <h1 class="h3 mb-1">Register Storefront Customer</h1>
                    <p class="text-muted mb-0">Create a new customer account, assign wholesale tier, and configure contact details.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Customers
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please correct the following errors:</div>
                <ul class="mb-0 small ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mt-3">
            <div class="col-12 col-lg-8">
                <div class="panel p-4">
                    <form action="{{ route('admin.customers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Johnathan Doe">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="e.g. jdoe@example.com">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Password *</label>
                                <input type="password" name="password" required class="form-control" placeholder="Minimum 8 characters">
                                <small class="text-muted">Must be at least 8 characters long.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="e.g. +1 (555) 234-5678">
                            </div>


                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Customer Group (B2B Wholesale)</label>
                                <select name="customer_group_id" class="form-select">
                                    <option value="">None (Standard Retail)</option>
                                    @foreach($customerGroups as $group)
                                        <option value="{{ $group->id }}" {{ old('customer_group_id') == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% off)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Company Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" class="form-control" placeholder="Optional business or organization name">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Tax / VAT Number</label>
                                <input type="text" name="tax_vat_number" value="{{ old('tax_vat_number') }}" class="form-control" placeholder="Optional Tax ID or VAT code">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Street Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="e.g. 742 Evergreen Terrace">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">City</label>
                                <input type="text" name="city" value="{{ old('city') }}" class="form-control" placeholder="e.g. Springfield">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">State / Province</label>
                                <input type="text" name="state" value="{{ old('state') }}" class="form-control" placeholder="e.g. Oregon">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Postal / Zip Code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code') }}" class="form-control" placeholder="e.g. 97477">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Country</label>
                                <input type="text" name="country" value="{{ old('country', 'United States') }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Credit Limit ($)</label>
                                <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', '0.00') }}" class="form-control">
                            </div>

                            <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-check-circle me-1"></i> Register Customer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

