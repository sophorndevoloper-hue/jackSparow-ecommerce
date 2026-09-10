<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-shield-plus" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Access Control</p>
                    <h1 class="h3 mb-1">Create System Role</h1>
                    <p class="text-muted mb-0">Define a new user role (e.g. Inventory Manager, Order Dispatcher) and set its permissions.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Roles
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

        <form action="{{ route('admin.roles.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-3">Role Details</h5>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Role Name / Slug *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. inventory-manager">
                            <small class="text-muted">Use lowercase alphanumeric and dashes.</small>
                        </div>

                        <input type="hidden" name="guard_name" value="backend">

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Save New Role
                        </button>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="panel p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-key-fill text-primary me-1"></i> Assign Permissions to Role</h5>
                                <small class="text-muted">Select capabilities this role should grant automatically.</small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleAllPermissions(true)">Select All</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleAllPermissions(false)">Deselect All</button>
                            </div>
                        </div>

                        <div class="row g-3">
                            @foreach($groupedPermissions as $groupName => $perms)
                                @if($perms->isNotEmpty())
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded p-3 h-100 bg-light-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                <strong class="text-dark small text-uppercase">{{ $groupName }}</strong>
                                                <span class="badge bg-secondary" style="font-size: 10px;">{{ $perms->count() }}</span>
                                            </div>

                                            @foreach($perms as $perm)
                                                <div class="form-check my-2">
                                                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->id }}">
                                                    <label class="form-check-label small text-dark" for="perm_{{ $perm->id }}">
                                                        {{ $perm->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleAllPermissions(checked) {
            document.querySelectorAll('.perm-checkbox').forEach(cb => {
                cb.checked = checked;
            });
        }
    </script>
</x-app-layout>

