<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-truck" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management &bull; Suppliers</p>
                    <h1 class="h3 mb-1">Add Hardware Supplier</h1>
                    <p class="text-muted mb-0">Register a new computer parts manufacturer, vendor, or wholesale distributor.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Suppliers
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mt-3">
            <div class="col-12 col-lg-8">
                <div class="panel p-4">
                    <form action="{{ route('admin.suppliers.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Company / Vendor Name *</label>
                                <input type="text" name="company_name" value="{{ old('company_name') }}" required class="form-control" placeholder="e.g. ASUS Wholesale Distribution">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Contact Representative</label>
                                <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="form-control" placeholder="e.g. Sarah Jenkins">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="e.g. sales@vendor.com">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="e.g. +1 (555) 019-2834">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Office / Warehouse Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="e.g. 100 Technology Parkway, Suite 400, Austin, TX">
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold small">Supplied Hardware Categories</label>
                                <input type="text" name="supply_categories" value="{{ old('supply_categories') }}" class="form-control" placeholder="e.g. GPUs, CPUs, Motherboards, RAM">
                                <small class="text-muted">Separate multiple categories with commas.</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Partnership Status *</label>
                                <select name="status" required class="form-select">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active Partner</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Procurement Notes</label>
                                <textarea name="notes" rows="3" class="form-control" placeholder="Payment terms (Net-30), minimum order quantities (MOQ), RMA contact...">{{ old('notes') }}</textarea>
                            </div>

                            <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-check-circle me-1"></i> Save Supplier
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="panel p-4 bg-light-subtle">
                    <h5 class="mb-2"><i class="bi bi-info-circle text-primary me-1"></i> Supplier Information</h5>
                    <p class="text-muted small">
                        Suppliers represent your wholesale distributors and manufacturers who provide computer hardware components (CPUs, GPUs, Motherboards, Power Supplies).
                    </p>
                    <hr>
                    <ul class="list-unstyled small text-muted space-y-2 mb-0">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-1"></i> Keep vendor contact reps and direct emails on file.</li>
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-1"></i> Tag specific component categories for easy procurement.</li>
                        <li><i class="bi bi-check2-circle text-success me-1"></i> Inactivate suppliers who are currently out of distribution contract.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

