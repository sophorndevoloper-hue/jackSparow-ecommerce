<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-key-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Access Control</p>
                    <h1 class="h3 mb-1">System Permissions</h1>
                    <p class="text-muted mb-0">Overview of all system permissions, assigned roles, and direct user assignments dynamically mapped from Menu Setup.</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('manage permissions')
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Permission
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

        <!-- Guard Filter Bar -->
        <div class="panel p-2 mt-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted fw-bold me-2"><i class="bi bi-shield me-1"></i> Filter by Guard:</span>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm {{ !request('guard') ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All Guards
                </a>
                <a href="{{ route('admin.permissions.index', ['guard' => 'backend']) }}" class="btn btn-sm {{ request('guard') === 'backend' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi bi-cpu me-1"></i> backend
                </a>
                <a href="{{ route('admin.permissions.index', ['guard' => 'frontend']) }}" class="btn btn-sm {{ request('guard') === 'frontend' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi bi-shop me-1"></i> frontend
                </a>
                <a href="{{ route('admin.permissions.index', ['guard' => 'web']) }}" class="btn btn-sm {{ request('guard') === 'web' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="bi bi-globe me-1"></i> web
                </a>
            </div>
            <div class="small text-muted">
                Showing <strong>{{ $permissions->count() }}</strong> system permissions across <strong>{{ count($groupedPermissions) }}</strong> modules
            </div>
        </div>

        <div class="row g-3 mt-2">
            @foreach($groupedPermissions as $groupName => $perms)
                @if($perms->isNotEmpty())
                    <div class="col-12 col-lg-6">
                        <div class="panel p-0 overflow-hidden h-100 shadow-sm border">
                            <div class="panel-header p-3 border-bottom d-flex justify-content-between align-items-center bg-light-subtle">
                                <h5 class="mb-0 fs-6 fw-bold text-dark"><i class="bi bi-shield-lock me-1 text-primary"></i> {{ $groupName }}</h5>
                                <span class="badge bg-secondary font-monospace">{{ $perms->count() }} {{ str('permission')->plural($perms->count()) }}</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Permission Name</th>
                                            <th>Action Route</th>
                                            <th>Guard</th>
                                            <th>Roles</th>
                                            <th class="text-end">Direct Users</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($perms as $perm)
                                            <tr>
                                                <td><span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $perm->name }}</span></td>
                                                <td>
                                                    @if(!empty($perm->action_route) || !empty($actionRoutes[$perm->name]))
                                                        <span class="badge bg-body-secondary text-body-secondary border-0 font-monospace" style="font-size: 10px;">
                                                            <i class="bi bi-arrow-right-short text-primary"></i>{{ $perm->action_route ?: $actionRoutes[$perm->name] }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted small font-monospace">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge {{ $perm->guard_name === 'backend' ? 'bg-primary-subtle text-primary' : ($perm->guard_name === 'frontend' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary') }} border-0 font-monospace px-2 py-1" style="font-size: 10px;">
                                                        {{ $perm->guard_name }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @forelse($perm->roles as $r)
                                                        <span class="badge {{ $r->name === 'admin' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-body-secondary' }} border-0 me-1 px-2 py-1" style="font-size: 10px;">
                                                            {{ ucfirst($r->name) }}
                                                        </span>
                                                    @empty
                                                        <span class="text-muted small">None</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-end">
                                                    @if($perm->users->count() > 0)
                                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                                            {{ $perm->users->count() }} users
                                                        </span>
                                                    @else
                                                        <span class="text-muted small">0</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
