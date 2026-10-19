<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management &bull; Customers</p>
                    <h1 class="h3 mb-1">Edit Customer: {{ $customer->name }}</h1>
                    <p class="text-muted mb-0">Update customer account profile, assigned wholesale tier, and credentials.</p>
                </div>
            </div>
            <div class="heading-actions d-flex gap-2">
                <form action="{{ route('admin.customers.toggle-block', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $customer->is_blocked ? 'Unblock customer account ' . addslashes($customer->name) . '?' : 'Block customer account ' . addslashes($customer->name) . '? They will not be able to log in or make purchases.' }}');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-sm {{ $customer->is_blocked ? 'btn-outline-success' : 'btn-outline-warning' }}">
                        <i class="bi {{ $customer->is_blocked ? 'bi-unlock' : 'bi-slash-circle' }} me-1"></i>
                        {{ $customer->is_blocked ? 'Unblock Account' : 'Block Account' }}
                    </button>
                </form>
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
                    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Full Name *</label>
                                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Email Address *</label>
                                <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep unchanged">
                                <small class="text-muted">Only fill if resetting the customer's password.</small>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control">
                            </div>


                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Customer Group (B2B Wholesale)</label>
                                <select name="customer_group_id" class="form-select">
                                    <option value="">None (Standard Retail)</option>
                                    @foreach($customerGroups as $group)
                                        <option value="{{ $group->id }}" {{ old('customer_group_id', $customer->customer_group_id) == $group->id ? 'selected' : '' }}>
                                            {{ $group->name }} ({{ number_format($group->default_discount_percentage, 1) }}% off)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Company Name</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Tax / VAT Number</label>
                                <input type="text" name="tax_vat_number" value="{{ old('tax_vat_number', $customer->tax_vat_number) }}" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Street Address</label>
                                <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">City</label>
                                <input type="text" name="city" value="{{ old('city', $customer->city) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">State / Province</label>
                                <input type="text" name="state" value="{{ old('state', $customer->state) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Postal / Zip Code</label>
                                <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Country</label>
                                <input type="text" name="country" value="{{ old('country', $customer->country ?? 'United States') }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Credit Limit ($)</label>
                                <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="form-control">
                            </div>

                            <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm">Cancel</a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-save me-1"></i> Update Customer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

