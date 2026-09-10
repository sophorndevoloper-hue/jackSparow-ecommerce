<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Logistics &amp; Fulfillment</p>
                    <h1 class="h3 mb-1">Edit Warehouse: {{ $warehouse->name }}</h1>
                    <p class="text-muted mb-0">Update contact details, address, and facility status.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Warehouses
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

        <form action="{{ route('admin.warehouses.update', $warehouse->id) }}" method="POST" class="mt-3">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 text-dark fw-bold">Facility Information</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">Warehouse Name *</label>
                                <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Facility Code *</label>
                                <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" required class="form-control font-monospace">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Contact Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $warehouse->phone) }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Contact Email</label>
                                <input type="email" name="email" value="{{ old('email', $warehouse->email) }}" class="form-control">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">Street Address</label>
                                <input type="text" name="address" value="{{ old('address', $warehouse->address) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">City / State</label>
                                <input type="text" name="city" value="{{ old('city', $warehouse->city) }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 text-dark fw-bold">Configuration</h5>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="isActive">Operational &amp; Active</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault" {{ old('is_default', $warehouse->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="isDefault">Set as Primary Default Depot</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Warehouse
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

