<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Access Control</p>
                    <h1 class="h3 mb-1">System Roles</h1>
                    <p class="text-muted mb-0">Define user roles, inspect assigned members, and configure permission bundles.</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('create roles')
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add New Role
                    </a>
                @endcan
                @can('view users')
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people me-1"></i> View All Users
                    </a>
                @endcan
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Search Bar -->
        <div class="panel p-3 mt-3 mb-3">
            <form action="{{ route('admin.roles.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 520px;">
                    <div class="input-group input-group-sm w-100">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search role by name or capability...">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>

                <div class="small text-muted">
                    Showing <strong>{{ $roles->count() }}</strong> system roles
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role Name</th>
                            <th>Assigned Users</th>
                            <th>Permissions Granted</th>
                            <th>Role Type</th>
                            <th class="text-end no-sort">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            <i class="bi bi-shield-check fs-5"></i>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#roleModal_{{ $role->id }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                                {{ ucfirst($role->name) }}
                                            </a>
                                            <div><span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace" style="font-size: 11px;">{{ $role->name }}</span></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#roleModal_{{ $role->id }}">
                                        <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                                            <i class="bi bi-people me-1"></i>{{ $role->users_count }} users
                                        </span>
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#roleModal_{{ $role->id }}">
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-key me-1"></i>{{ $role->permissions_count }} permissions
                                        </span>
                                    </button>
                                </td>
                                <td>
                                    @if($role->name === 'superadmin')
                                        <span class="badge bg-danger-subtle text-danger border-0 px-2 py-1">Protected</span>
                                    @elseif($role->name === 'admin')
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">System Protected</span>
                                    @else
                                        <span class="badge bg-info-subtle text-info-emphasis border-0 px-2 py-1">Custom Role</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <button type="button" class="btn-ghost" data-bs-toggle="modal" data-bs-target="#roleModal_{{ $role->id }}" title="View Role Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @can('edit roles')
                                            @if($role->name !== 'superadmin')
                                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn-ghost text-primary" title="Edit Role & Permissions">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif
                                        @endcan
                                        @can('delete roles')
                                            @if(!in_array($role->name, ['superadmin', 'admin']))
                                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-ghost text-danger" title="Delete Role">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Role Detail Modals (Placed Outside of Table to prevent DOM break) -->
        @foreach($roles as $role)
            <div class="modal fade" id="roleModal_{{ $role->id }}" tabindex="-1" aria-labelledby="roleModalLabel_{{ $role->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shield-lock fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title mb-0 text-dark" id="roleModalLabel_{{ $role->id }}">Role Details: {{ ucfirst($role->name) }}</h5>
                                    <small class="text-muted font-monospace">{{ $role->guard_name }} guard</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body space-y-4">
                            <!-- Section 1: Assigned Users -->
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <i class="bi bi-people-fill text-primary me-1"></i>
                                        Assigned Members ({{ $role->users->count() }})
                                    </h6>
                                    <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;">
                                        Filter in Users Table &rarr;
                                    </a>
                                </div>

                                @if($role->users->isNotEmpty())
                                    <div class="table-responsive border rounded">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light small">
                                                <tr>
                                                    <th>User</th>
                                                    <th>Email</th>
                                                    <th>Registered</th>
                                                    <th class="text-end">Manage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($role->users as $u)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                @if($u->hasCustomAvatar())
                                                                    <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" class="rounded-circle object-fit-cover border shadow-xs" style="width: 28px; height: 28px; min-width: 28px;">
                                                                @else
                                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small" style="width: 28px; height: 28px; min-width: 28px; font-size: 11px;">
                                                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                                                    </div>
                                                                @endif
                                                                <strong class="small">{{ $u->name }}</strong>
                                                            </div>
                                                        </td>
                                                        <td><span class="text-muted small">{{ $u->email }}</span></td>
                                                        <td><span class="text-muted small">{{ $u->created_at->format('M d, Y') }}</span></td>
                                                        <td class="text-end">
                                                            <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-link text-primary p-0" style="font-size: 11px;">
                                                                Apply Permissions &rarr;
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-3 bg-light rounded text-center text-muted small">
                                        No users are currently assigned to this role.
                                    </div>
                                @endif
                            </div>

                            <hr class="my-3">

                            <!-- Section 2: Permissions Granted by this Role -->
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">
                                    <i class="bi bi-key-fill text-success me-1"></i>
                                    Permissions Granted ({{ $role->permissions->count() }})
                                </h6>

                                @if($role->permissions->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($role->permissions as $p)
                                            <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size: 11px;">
                                                <i class="bi bi-check-circle-fill text-success me-1"></i> {{ $p->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-3 bg-light rounded text-center text-muted small">
                                        This role has no permissions attached.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            @can('manage roles')
                                <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil me-1"></i> Edit Role & Permissions
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
