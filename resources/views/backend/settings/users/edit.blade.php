<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-person-gear" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Access Control</p>
                    <h1 class="h3 mb-1">Manage Permissions: {{ $user->name }}</h1>
                    <p class="text-muted mb-0">Assign roles and apply specific permissions directly to this user.</p>
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

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="mt-3">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <!-- Left: User Profile & Roles -->
                <div class="col-12 col-lg-4 space-y-3">
                    <!-- User Card -->
                    <div class="panel p-4 mb-3">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if($user->hasCustomAvatar())
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover border shadow-xs" style="width: 50px; height: 50px; min-width: 50px;">
                            @else
                                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center fs-4 shadow-xs" style="width: 50px; height: 50px; min-width: 50px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-0">{{ $user->name }}</h5>
                                <span class="text-muted small">{{ $user->email }}</span>
                            </div>
                        </div>

                        <hr>

                        <div class="small space-y-2">
                            <div><span class="text-muted">Account ID:</span> <strong>#{{ $user->id }}</strong></div>
                            <div><span class="text-muted">Registered:</span> <strong>{{ $user->created_at->format('M d, Y') }}</strong></div>
                            <div><span class="text-muted">Total Orders Placed:</span> <strong>{{ $user->orders()->count() }}</strong></div>
                        </div>
                    </div>

                    <!-- Role Assignment Panel -->
                    <div class="panel p-4 mb-3">
                        <h5 class="mb-2"><i class="bi bi-shield-lock me-1"></i> User Roles</h5>
                        <p class="text-muted small mb-3">Select the roles that belong to this account.</p>

                        <div class="space-y-2">
                            @foreach($roles as $role)
                                @if($role->name === 'superadmin')
                                    <div class="form-check p-2 border border-danger-subtle bg-danger-subtle rounded mb-2">
                                        <input type="hidden" name="roles[]" value="superadmin">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" checked disabled id="role_{{ $role->id }}">
                                        <label class="form-check-label fw-bold text-danger d-flex align-items-center justify-content-between" for="role_{{ $role->id }}">
                                            <span><i class="bi bi-shield-fill-check me-1"></i> Superadmin</span>
                                            <span class="badge bg-danger text-white font-monospace" style="font-size: 10px;">Primary Owner</span>
                                        </label>
                                        <small class="d-block text-danger-emphasis ps-4">
                                            Unique system owner role. Cannot be transferred or assigned to other accounts.
                                        </small>
                                    </div>
                                @else
                                    <div class="form-check p-2 border rounded mb-2 {{ $user->hasRole($role->name, $role->guard_name) ? 'bg-light border-primary' : '' }}">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}" {{ $user->hasRole($role->name, $role->guard_name) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark d-flex align-items-center justify-content-between" for="role_{{ $role->id }}">
                                            <span>{{ ucfirst($role->name) }}</span>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace" style="font-size: 10px;">
                                                {{ $role->guard_name }}
                                            </span>
                                        </label>
                                        <small class="d-block text-muted ps-4">
                                            Inherits {{ $role->permissions->count() }} permissions
                                        </small>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Save Action -->
                    <div class="panel p-3">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Save User Permissions & Roles
                        </button>
                    </div>
                </div>

                <!-- Right: Granular Direct Permissions -->
                <div class="col-12 col-lg-8 space-y-3">
                    <div class="panel p-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom mb-3 gap-2">
                            <div>
                                <h5 class="mb-1"><i class="bi bi-key-fill text-primary me-1"></i> Direct Permissions</h5>
                                <p class="text-muted small mb-0">Apply specific system capabilities directly to this user.</p>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleAllPermissions(true)">Select All</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleAllPermissions(false)">Deselect All</button>
                            </div>
                        </div>

                        @if($user->hasRole('admin'))
                            <div class="alert alert-info py-2 small d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div>
                                    <strong>Admin Role Active:</strong> This user has the <code>admin</code> role and automatically possesses all permissions across the platform. You can still apply specific direct permissions below.
                                </div>
                            </div>
                        @endif

                        <div class="row g-3">
                            @foreach($groupedPermissions as $groupName => $perms)
                                @if($perms->isNotEmpty())
                                    <div class="col-12 col-md-6">
                                        <div class="border rounded p-3 h-100 bg-light-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                <strong class="text-dark small text-uppercase">{{ $groupName }}</strong>
                                                <span class="badge bg-secondary" style="font-size: 10px;">{{ $perms->count() }} permissions</span>
                                            </div>

                                            @foreach($perms as $permission)
                                                @php
                                                    $isDirect = in_array($permission->name, $userDirectPermissions);
                                                    $isViaRole = in_array($permission->name, $userRolePermissions);
                                                @endphp
                                                <div class="form-check my-2">
                                                    <input class="form-check-input perm-checkbox" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="{{ $permission->name }}" 
                                                           id="perm_{{ $permission->id }}"
                                                           {{ $isDirect ? 'checked' : '' }}>
                                                    <label class="form-check-label small d-flex align-items-center justify-content-between" for="perm_{{ $permission->id }}">
                                                        <span class="{{ $isDirect ? 'fw-bold text-primary' : 'text-dark' }}">{{ $permission->name }}</span>
                                                        @if($isViaRole)
                                                            <span class="badge bg-light text-success border ms-1" style="font-size: 9px;" title="Granted via assigned role">
                                                                <i class="bi bi-check2"></i> Via Role
                                                            </span>
                                                        @endif
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

