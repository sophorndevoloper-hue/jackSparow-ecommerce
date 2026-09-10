<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-key" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Access Control</p>
                    <h1 class="h3 mb-1">Add System Permission</h1>
                    <p class="text-muted mb-0">Create a new permission capability for the e-commerce store.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Permissions
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

        <div class="panel p-4 mt-3" style="max-width: 600px;">
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold small">Permission Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. export inventory reports">
                    <small class="text-muted">Will be stored in lowercase for role & user capability checks.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Action Route Target</label>
                    <input type="text" name="action_route" value="{{ old('action_route') }}" class="form-control font-monospace" placeholder="e.g. admin.products.create">
                    <small class="text-muted">Target Laravel route associated with this permission.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Authentication Guard *</label>
                    <select name="guard_name" class="form-select" required>
                        <option value="backend" {{ old('guard_name') === 'backend' ? 'selected' : '' }}>backend (Admin Portal & Staff)</option>
                        <option value="frontend" {{ old('guard_name') === 'frontend' ? 'selected' : '' }}>frontend (Storefront & Customers)</option>
                        <option value="web" {{ old('guard_name') === 'web' ? 'selected' : '' }}>web (Universal Web Guard)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                    <i class="bi bi-save me-1"></i> Save Permission
                </button>
            </form>
        </div>
    </div>
</x-app-layout>

