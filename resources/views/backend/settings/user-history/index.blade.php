<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">System Access &bull; Audit Trail</p>
                    <h1 class="h3 mb-1">User History &amp; Activity Logs</h1>
                    <p class="text-muted mb-0">Trace comprehensive user actions, logins, database mutations, staff shifts, and payroll events across the platform.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @can('delete activity logs')
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#pruneLogsModal">
                        <i class="bi bi-trash3 me-1"></i> Prune Logs
                    </button>
                @endcan
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-people me-1"></i> Users
                </a>
                <a href="{{ route('admin.staff.monthly-report') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-printer me-1"></i> Staff Monthly Report
                </a>
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

        <!-- KPI Metrics Summary Cards -->
        <div class="row g-3 mt-1 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Activities</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ number_format($totalActivities) }}</h3>
                            <small class="text-muted">Lifetime logged actions</small>
                        </div>
                        <div class="fs-1 text-primary opacity-25">
                            <i class="bi bi-database-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Today's Actions</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ number_format($todayActivities) }}</h3>
                            <small class="text-muted">Recorded today</small>
                        </div>
                        <div class="fs-1 text-info opacity-25">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Modifications</small>
                            <h3 class="mb-0 mt-1 fw-bold text-warning">{{ number_format($modificationsCount) }}</h3>
                            <small class="text-muted">Create / Update / Delete</small>
                        </div>
                        <div class="fs-1 text-warning opacity-25">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active Operators</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ number_format($activeUserCount) }}</h3>
                            <small class="text-muted">Users with recorded actions</small>
                        </div>
                        <div class="fs-1 text-success opacity-25">
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Panel -->
        <div class="panel p-3 mb-4">
            <form action="{{ route('admin.user-history.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small text-muted mb-1">Search Details</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Description, user, IP...">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Module</label>
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All Modules</option>
                        @foreach($distinctModules as $mod)
                            <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                                {{ $mod }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small text-muted mb-1">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All Actions</option>
                        @foreach($distinctActions as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                                {{ $act }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 d-flex gap-2">
                    <div class="flex-grow-1">
                        <label class="form-label small text-muted mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    </div>
                    <div class="flex-grow-1">
                        <label class="form-label small text-muted mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="col-12 text-end d-flex justify-content-end gap-2 mt-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel me-1"></i> Filter Logs
                    </button>
                    @if(request()->hasAny(['search', 'user_id', 'module', 'action', 'date_from', 'date_to']))
                        <a href="{{ route('admin.user-history.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Activity History Log Table -->
        <div class="panel p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-uppercase fw-semibold" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-3" style="width: 170px;">Date &amp; Time</th>
                            <th style="width: 180px;">User</th>
                            <th style="width: 140px;">Module</th>
                            <th style="width: 130px;">Action</th>
                            <th>Description</th>
                            <th style="width: 130px;">IP Address</th>
                            <th class="text-end pe-3" style="width: 80px;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td class="ps-3 text-nowrap">
                                    <div class="fw-semibold text-dark">{{ $activity->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted font-monospace">{{ $activity->created_at->format('H:i:s') }} ({{ $activity->created_at->diffForHumans() }})</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-light border text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; min-width: 32px; font-size: 0.8rem;">
                                            {{ strtoupper(substr($activity->user_name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $activity->user_name }}</div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 130px;">{{ $activity->user_email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="bi bi-box-seam me-1"></i>{{ $activity->module }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $activity->action_badge_class }}">
                                        {{ $activity->action }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $activity->description }}</span>
                                    @if($activity->entity_id)
                                        <span class="badge bg-light text-muted border font-monospace ms-1">#{{ $activity->entity_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        {{ $activity->ip_address ?? '127.0.0.1' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-2 py-1" onclick="showActivityDetail({{ $activity->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <div class="mb-2 fs-3 text-muted opacity-50"><i class="bi bi-clock-history"></i></div>
                                    <p class="mb-0">No user activity logs found matching the selected criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($activities->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} records
                    </small>
                    <div>
                        {{ $activities->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Detail Inspection Modal -->
    <div class="modal fade" id="activityDetailModal" tabindex="-1" aria-labelledby="activityDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="activityDetailModalLabel">
                        <i class="bi bi-file-earmark-code me-2 text-primary"></i>Activity Log Inspector
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold d-block">Operator</small>
                            <span id="modalUserName" class="fw-bold text-dark">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold d-block">Module</small>
                            <span id="modalModule" class="badge bg-secondary-subtle text-secondary border">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold d-block">Action</small>
                            <span id="modalAction" class="badge bg-primary text-white">-</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted text-uppercase fw-semibold d-block">Timestamp</small>
                            <span id="modalTimestamp" class="text-muted font-monospace small">-</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Description</small>
                        <div id="modalDescription" class="p-2 bg-light border rounded text-dark"></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">IP Address</small>
                            <span id="modalIp" class="badge bg-light text-dark border font-monospace">-</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted text-uppercase fw-semibold d-block mb-1">User Agent</small>
                            <small id="modalUserAgent" class="text-muted text-break font-monospace small">-</small>
                        </div>
                    </div>

                    <div>
                        <small class="text-muted text-uppercase fw-semibold d-block mb-1">Payload / State Changes (Properties)</small>
                        <pre id="modalProperties" class="bg-dark text-light p-3 rounded font-monospace small mb-0" style="max-height: 250px; overflow-y: auto;">{}</pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Prune Logs Modal -->
    <div class="modal fade" id="pruneLogsModal" tabindex="-1" aria-labelledby="pruneLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.user-history.clear') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-danger" id="pruneLogsModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Prune Activity Logs
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Pruning will permanently delete historical audit logs older than the selected retention window.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Retention Period</label>
                            <select name="days" class="form-select">
                                <option value="7">Delete logs older than 7 days</option>
                                <option value="30" selected>Delete logs older than 30 days (Recommended)</option>
                                <option value="90">Delete logs older than 90 days</option>
                                <option value="180">Delete logs older than 180 days</option>
                                <option value="0">Delete ALL logs (Clear Audit Table)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash3 me-1"></i> Confirm Prune
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showActivityDetail(activityId) {
            fetch(`/admin/user-history/${activityId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('modalUserName').innerText = data.user.name + ' (' + data.user.email + ')';
                document.getElementById('modalModule').innerText = data.module;
                document.getElementById('modalAction').innerText = data.action;
                document.getElementById('modalTimestamp').innerText = data.created_at + ' (' + data.time_ago + ')';
                document.getElementById('modalDescription').innerText = data.description;
                document.getElementById('modalIp').innerText = data.ip_address || 'N/A';
                document.getElementById('modalUserAgent').innerText = data.user_agent || 'N/A';

                const props = data.properties ? JSON.stringify(data.properties, null, 2) : 'No extra payload recorded.';
                document.getElementById('modalProperties').innerText = props;

                const modal = new bootstrap.Modal(document.getElementById('activityDetailModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error fetching activity details:', error);
                alert('Could not fetch activity details.');
            });
        }
    </script>
</x-app-layout>

