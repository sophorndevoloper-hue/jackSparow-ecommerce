<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-person-plus-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Admin Dashboard Staff</p>
                    <h1 class="h3 mb-1">Create Admin User</h1>
                    <p class="text-muted mb-0">Create a new user account with <code>backend</code> guard access for managing the store dashboard.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Users
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

        <form action="{{ route('admin.users.store') }}" method="POST" class="mt-3">
            @csrf

            <div class="row g-3">
                <!-- Left: User Account Credentials & Backend Roles -->
                <div class="col-12 col-lg-4 space-y-3">
                    <div class="panel p-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Account Details</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">guard: backend</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. John Hardware">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Email Address *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="e.g. john@admin.test">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Password *</label>
                            <input type="password" name="password" required class="form-control" placeholder="Minimum 8 characters">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Confirm Password *</label>
                            <input type="password" name="password_confirmation" required class="form-control" placeholder="Re-enter password">
                        </div>
                    </div>

                    <!-- Backend Roles Panel -->
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-2"><i class="bi bi-shield-lock text-primary me-1"></i> Dashboard Roles</h5>
                        <p class="text-muted small mb-3">Select the backend roles to assign to this administrator.</p>

                        <div class="space-y-2">
                            @foreach($roles as $role)
                                <div class="form-check p-2 border rounded mb-2">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}" {{ (old('roles') && in_array($role->name, old('roles'))) || $role->name === 'admin' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark d-flex align-items-center justify-content-between" for="role_{{ $role->id }}">
                                        <span>{{ ucfirst($role->name) }}</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 10px;">
                                            backend
                                        </span>
                                    </label>
                                    <small class="d-block text-muted ps-4">
                                        Inherits {{ $role->permissions->count() }} dashboard permissions
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="panel p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-person-check me-1"></i> Create Admin User
                        </button>
                    </div>
                </div>

                <!-- Right: Granular Backend Permissions Matrix -->
                <div class="col-12 col-lg-8 space-y-3">
                    <div class="panel p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-3 gap-2">
                            <div>
                                <h5 class="mb-1"><i class="bi bi-key-fill text-primary me-1"></i> Apply Direct Backend Permissions</h5>
                                <p class="text-muted small mb-0">Apply specific system capabilities directly to this user.</p>
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

                                            @foreach($perms as $permission)
                                                <div class="form-check my-2">
                                                    <input class="form-check-input perm-checkbox" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}" 
                                                           id="perm_{{ $permission->id }}">
                                                    <label class="form-check-label small text-dark d-flex align-items-center justify-content-between" for="perm_{{ $permission->id }}">
                                                        <span>{{ $permission->name }}</span>
                                                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 9px;">backend</span>
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

