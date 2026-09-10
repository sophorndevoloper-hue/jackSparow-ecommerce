<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People Management &bull; Suppliers</p>
                    <h1 class="h3 mb-1">Edit Supplier: {{ $supplier->company_name }}</h1>
                    <p class="text-muted mb-0">Update wholesale contact information, supply categories, and partnership status.</p>
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
                    <form action="{{ route('admin.suppliers.update', $supplier->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Company / Vendor Name *</label>
                                <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name) }}" required class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Contact Representative</label>
                                <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Phone Number</label>
                                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Office / Warehouse Address</label>
                                <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="form-control">
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold small">Supplied Hardware Categories</label>
                                <input type="text" name="supply_categories" value="{{ old('supply_categories', $supplier->supply_categories) }}" class="form-control">
                                <small class="text-muted">Separate multiple categories with commas.</small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Partnership Status *</label>
                                <select name="status" required class="form-select">
                                    <option value="active" {{ old('status', $supplier->status) === 'active' ? 'selected' : '' }}>Active Partner</option>
                                    <option value="inactive" {{ old('status', $supplier->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Procurement Notes</label>
                                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $supplier->notes) }}</textarea>
                            </div>

                            <div class="col-12 pt-3 border-top d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('Are you sure you want to delete this supplier?')) document.getElementById('delete-supplier-form').submit();">
                                    <i class="bi bi-trash me-1"></i> Delete Supplier
                                </button>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        <i class="bi bi-check-circle me-1"></i> Update Supplier
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <form id="delete-supplier-form" action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="panel p-4 bg-light-subtle">
                    <h5 class="mb-2"><i class="bi bi-clock-history text-primary me-1"></i> Supplier Summary</h5>
                    <div class="small text-muted space-y-2">
                        <p class="mb-1"><strong>Created:</strong> {{ $supplier->created_at->format('M d, Y h:i A') }}</p>
                        <p class="mb-0"><strong>Last Updated:</strong> {{ $supplier->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

