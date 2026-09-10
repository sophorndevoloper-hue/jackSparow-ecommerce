<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">System Access Control</p>
                    <h1 class="h3 mb-1">Users & Permission Management</h1>
                    <p class="text-muted mb-0">Inspect user profiles, assign roles, and configure individual permissions.</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('view roles')
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                        <i class="bi bi-shield-lock me-1"></i> System Roles
                    </a>
                @endcan
                @can('view settings')
                    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-sliders me-1"></i> Menu Setup
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

        <!-- Filter Bar -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 440px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search user by name or email address...">
                </div>

                <div style="min-width: 180px;">
                    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                                {{ ucfirst($r->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(request('search') || request('role'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="panel p-3">
            <x-datatable id="usersTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($user->hasCustomAvatar())
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-3 object-fit-cover border shadow-xs" style="width: 40px; height: 40px; min-width: 40px;">
                                        @else
                                            <div class="rounded-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 40px; height: 40px; min-width: 40px; font-size: 15px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="d-flex align-items-center gap-1">
                                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#userModal_{{ $user->id }}" class="fw-semibold text-body-emphasis text-decoration-none">
                                                    {{ $user->name }}
                                                </a>
                                                @if($user->id === auth()->id())
                                                    <span class="badge bg-info-subtle text-info-emphasis border-0 px-1 py-0" style="font-size: 10px;">You</span>
                                                @endif
                                            </div>
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $user->email }}" class="text-body-secondary text-decoration-none small">
                                        {{ $user->email }}
                                    </a>
                                </td>
                                <td>
                                    @if($user->is_approved)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Approved
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border-0 px-2 py-1">
                                            <i class="bi bi-hourglass-split me-1"></i>Pending Approval
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <a href="{{ route('admin.roles.index') }}" class="badge {{ $role->name === 'admin' || $role->name === 'superadmin' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-body-secondary' }} border-0 text-decoration-none px-2 py-1">
                                                <i class="bi bi-shield me-1"></i>{{ ucfirst($role->name) }}
                                            </a>
                                        @empty
                                            <span class="text-muted small">No role</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    @if($user->permissions->isNotEmpty())
                                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#userModal_{{ $user->id }}">
                                            <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                                <i class="bi bi-key me-1"></i>{{ $user->permissions->count() }} direct permissions
                                            </span>
                                        </button>
                                    @else
                                        <span class="text-muted small">Inherited via role</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary border-0 px-2 py-1">
                                        {{ $user->orders->count() }} orders
                                    </span>
                                </td>
                                <td class="text-end">
                                    @php
                                        $authUser = auth('backend')->user() ?? auth()->user();
                                        $isTargetProtected = $user->hasRole('admin', 'backend') || $user->hasRole('superadmin', 'backend');
                                        $isSuperAdmin = $authUser && $authUser->hasRole('superadmin', 'backend');
                                        $isAdmin = $authUser && ($isSuperAdmin || $authUser->hasRole('admin', 'backend'));

                                        // Who has rights to approve/revoke: admin, superadmin, or user with explicit 'approve users' permission
                                        $hasApproveRight = $isAdmin || ($authUser && $authUser->can('approve users'));

                                        // Can toggle approval: must have rights, cannot alter self, and only superadmin can alter admin/superadmin
                                        $canToggleApproval = $hasApproveRight && ($user->id !== $authUser?->id) && (! $isTargetProtected || $isSuperAdmin);

                                        // Only admin and superadmin are protected; regular users can have permissions edited
                                        $canEditPermissions = (! $isTargetProtected && ($isAdmin || ($authUser && $authUser->can('edit users')))) || $isSuperAdmin;

                                        // Admin can delete other users; cannot delete self or protected admin/superadmin (only superadmin can delete admin)
                                        $canDeleteUser = ($user->id !== $authUser?->id) && ((! $isTargetProtected && ($isAdmin || ($authUser && $authUser->can('delete users')))) || $isSuperAdmin);
                                    @endphp
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @if($canToggleApproval)
                                            @if(!$user->is_approved)
                                                <form action="{{ route('admin.users.toggle-approval', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-ghost text-success" title="Approve User Account">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.toggle-approval', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to revoke approval for {{ $user->name }}?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-ghost text-warning" title="Revoke Approval">
                                                        <i class="bi bi-shield-slash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if($canEditPermissions)
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-ghost text-primary" title="Apply Permissions & Roles">
                                                <i class="bi bi-shield-check"></i>
                                            </a>
                                        @elseif($isTargetProtected)
                                            <button type="button" class="btn-ghost text-muted opacity-50" disabled title="Protected Admin: Permissions can only be edited by superadmin">
                                                <i class="bi bi-shield-lock"></i>
                                            </button>
                                        @endif

                                        @if($canDeleteUser)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete user {{ $user->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    No user accounts found matching query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $users->links() }}
            </div>
        </div>

        <!-- User Detail Modals (Placed Outside of Table to prevent DOM breaking) -->
        @foreach($users as $user)
            <div class="modal fade" id="userModal_{{ $user->id }}" tabindex="-1" aria-labelledby="userModalLabel_{{ $user->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                @if($user->hasCustomAvatar())
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover border shadow-xs" style="width: 44px; height: 44px; min-width: 44px;">
                                @else
                                    <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 44px; height: 44px; min-width: 44px; font-size: 18px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h5 class="modal-title mb-0" id="userModalLabel_{{ $user->id }}">{{ $user->name }}</h5>
                                    <small class="text-muted">{{ $user->email }} • Registered {{ $user->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body space-y-4">
                            <!-- Section 1: Assigned Roles -->
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">
                                    <i class="bi bi-shield-lock-fill text-primary me-1"></i> Assigned Roles
                                </h6>
                                <div class="d-flex flex-wrap gap-2">
                                    @forelse($user->roles as $r)
                                        <span class="badge {{ $r->name === 'admin' ? 'bg-primary' : 'bg-secondary' }} p-2">
                                            <i class="bi bi-shield-check me-1"></i> {{ ucfirst($r->name) }} ({{ $r->permissions->count() }} permissions)
                                        </span>
                                    @empty
                                        <span class="text-muted small">No roles assigned to this account.</span>
                                    @endforelse
                                </div>
                            </div>

                            <hr class="my-3">

                            <!-- Section 2: Direct Applied Permissions -->
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">
                                    <i class="bi bi-key-fill text-success me-1"></i>
                                    Directly Applied Permissions ({{ $user->permissions->count() }})
                                </h6>
                                @if($user->permissions->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($user->permissions as $p)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1" style="font-size: 11px;">
                                                <i class="bi bi-check2"></i> {{ $p->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-2 bg-light rounded text-muted small">
                                        No direct permissions explicitly granted. Permissions are inherited via assigned role(s).
                                    </div>
                                @endif
                            </div>

                            <hr class="my-3">

                            <!-- Section 3: Hardware Orders Summary -->
                            <div>
                                <h6 class="fw-bold mb-2 text-dark">
                                    <i class="bi bi-cart-check text-info me-1"></i>
                                    Recent Hardware Orders ({{ $user->orders->count() }})
                                </h6>
                                @if($user->orders->isNotEmpty())
                                    <div class="table-responsive border rounded">
                                        <table class="table table-sm table-hover align-middle mb-0 small">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Date</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($user->orders->take(5) as $ord)
                                                    <tr>
                                                        <td><span class="fw-bold text-primary font-monospace">{{ $ord->order_number }}</span></td>
                                                        <td>{{ $ord->created_at->format('M d, Y') }}</td>
                                                        <td class="fw-bold">${{ number_format($ord->total_amount, 2) }}</td>
                                                        <td>
                                                            <span class="badge {{ $ord->status === 'delivered' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                {{ ucfirst($ord->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('admin.orders.show', $ord->id) }}" class="btn btn-sm btn-link p-0 text-primary">
                                                                View &rarr;
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-2 bg-light rounded text-muted small">
                                        This customer hasn't placed any hardware orders yet.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            @can('manage users')
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-shield-check me-1"></i> Edit Roles & Permissions
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
