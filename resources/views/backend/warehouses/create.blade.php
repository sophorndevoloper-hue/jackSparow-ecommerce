<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-plus-circle" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Logistics &amp; Fulfillment</p>
                    <h1 class="h3 mb-1">Add Storage Warehouse</h1>
                    <p class="text-muted mb-0">Create a new warehouse location for storing and distributing hardware stock.</p>
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

        <form action="{{ route('admin.warehouses.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 text-dark fw-bold">Facility Information</h5>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">Warehouse Name *</label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. West Coast Distribution Hub">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Facility Code *</label>
                                <input type="text" name="code" value="{{ old('code') }}" required class="form-control font-monospace" placeholder="e.g. WH-WEST">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Contact Phone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="+1 (555) 000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Contact Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="warehouse@example.com">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">Street Address</label>
                                <input type="text" name="address" value="{{ old('address') }}" class="form-control" placeholder="123 Industrial Way">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">City / State</label>
                                <input type="text" name="city" value="{{ old('city') }}" class="form-control" placeholder="Seattle, WA">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3 text-dark fw-bold">Configuration</h5>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                            <label class="form-check-label fw-bold small" for="isActive">Operational &amp; Active</label>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault">
                            <label class="form-check-label fw-bold small" for="isDefault">Set as Primary Default Depot</label>
                            <small class="text-muted d-block mt-1">Default warehouse will receive all unassigned products automatically.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Warehouse
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>

