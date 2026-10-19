<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-person-lines-fill" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">People &bull; Delivery Fleet</p>
                    <h1 class="h3 mb-1">Delivery Agents</h1>
                    <p class="text-muted mb-0">Manage delivery drivers, couriers, vehicles, operating zones, and dispatch assignments.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create delivery agents', 'edit delivery agents'])
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDeliveryAgentModal">
                        <i class="bi bi-plus-circle me-1"></i> Register Delivery Agent
                    </button>
                @endcanany
                @can('view deliveries')
                    <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-box-seam me-1"></i> Deliveries
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Summary KPI Cards -->
        <div class="row g-3 mt-1 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Agents</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ $totalAgents }}</h3>
                            <small class="text-muted">Registered delivery staff</small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Available / Ready</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ $availableCount }}</h3>
                            <small class="text-muted">Ready for dispatch</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">On Delivery</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ $onDeliveryCount }}</h3>
                            <small class="text-muted">Currently active routes</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-send fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="panel p-3 border-start border-4 border-secondary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Off-Duty / Inactive</small>
                            <h3 class="mb-0 mt-1 fw-bold text-secondary">{{ $inactiveCount }}</h3>
                            <small class="text-muted">Not taking dispatches</small>
                        </div>
                        <div class="rounded-circle bg-secondary-subtle text-secondary p-3">
                            <i class="bi bi-pause-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Panel -->
        <div class="panel p-3 mb-3">
            <form action="{{ route('admin.delivery-agents.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, agent code, phone, zone, license plate..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="vehicle_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Vehicles</option>
                        <option value="motorcycle" {{ request('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>Motorcycle / Scooter</option>
                        <option value="van" {{ request('vehicle_type') === 'van' ? 'selected' : '' }}>Delivery Van</option>
                        <option value="truck" {{ request('vehicle_type') === 'truck' ? 'selected' : '' }}>Freight Truck</option>
                        <option value="bicycle" {{ request('vehicle_type') === 'bicycle' ? 'selected' : '' }}>Bicycle Courier</option>
                        <option value="car" {{ request('vehicle_type') === 'car' ? 'selected' : '' }}>Sedan / Car</option>
                        <option value="other" {{ request('vehicle_type') === 'other' ? 'selected' : '' }}>Other Vehicle</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="on_delivery" {{ request('status') === 'on_delivery' ? 'selected' : '' }}>On Delivery</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm flex-fill">Filter</button>
                    @if(request()->hasAny(['search', 'vehicle_type', 'status']))
                        <a href="{{ route('admin.delivery-agents.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Delivery Agents Table -->
        <div class="panel p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 260px;">Agent Info</th>
                            <th>Contact</th>
                            <th>Vehicle &amp; Plate</th>
                            <th>Assigned Zone</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Deliveries</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-6" style="width: 40px; height: 40px; flex-shrink: 0;">
                                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $agent->name }}</div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge bg-light text-dark border font-monospace" style="font-size: 11px;">{{ $agent->code }}</span>
                                                @if(! $agent->is_active)
                                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">Deactivated</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div><i class="bi bi-telephone text-muted me-1"></i> <span class="text-dark">{{ $agent->phone }}</span></div>
                                    @if($agent->email)
                                        <div class="text-muted" style="font-size: 12px;"><i class="bi bi-envelope text-muted me-1"></i> {{ $agent->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 fw-semibold text-dark">
                                        <i class="bi {{ $agent->vehicleIcon() }} text-primary"></i>
                                        <span>{{ $agent->vehicleLabel() }}</span>
                                    </div>
                                    @if($agent->license_plate)
                                        <small class="text-muted font-monospace"><i class="bi bi-card-text me-1"></i>{{ $agent->license_plate }}</small>
                                    @else
                                        <small class="text-muted fst-italic">No plate specified</small>
                                    @endif
                                </td>
                                <td>
                                    @if($agent->zone)
                                        <span class="text-dark"><i class="bi bi-geo-alt text-danger me-1"></i>{{ $agent->zone }}</span>
                                    @else
                                        <span class="text-muted fst-italic">All Zones / Unassigned</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge {{ $agent->statusBadgeClass() }} px-2 py-1">
                                            {{ $agent->statusLabel() }}
                                        </span>
                                        @can('edit delivery agents')
                                            <form action="{{ route('admin.delivery-agents.toggle-active', $agent) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 11px;" title="Toggle active status">
                                                    {{ $agent->is_active ? 'Turn Off' : 'Turn On' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-dark border px-2 py-1">
                                        {{ $agent->deliveries_count }} shipments
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        @can('edit delivery agents')
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDeliveryAgentModal-{{ $agent->id }}" title="Edit Agent">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan
                                        @can('delete delivery agents')
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDeliveryAgentModal-{{ $agent->id }}" title="Delete Agent">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal for Agent -->
                            @can('edit delivery agents')
                                <div class="modal fade" id="editDeliveryAgentModal-{{ $agent->id }}" tabindex="-1" aria-labelledby="editDeliveryAgentLabel-{{ $agent->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.delivery-agents.update', $agent) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold" id="editDeliveryAgentLabel-{{ $agent->id }}">
                                                        <i class="bi bi-pencil-square text-primary me-2"></i>Edit Delivery Agent: {{ $agent->name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-7">
                                                            <label class="form-label fw-semibold">Agent Full Name <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control" value="{{ old('name', $agent->name) }}" required>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <label class="form-label fw-semibold">Agent Code <span class="text-danger">*</span></label>
                                                            <input type="text" name="code" class="form-control font-monospace" value="{{ old('code', $agent->code) }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $agent->phone) }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control" value="{{ old('email', $agent->email) }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Vehicle Type <span class="text-danger">*</span></label>
                                                            <select name="vehicle_type" class="form-select" required>
                                                                <option value="motorcycle" {{ old('vehicle_type', $agent->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>Motorcycle / Scooter</option>
                                                                <option value="van" {{ old('vehicle_type', $agent->vehicle_type) === 'van' ? 'selected' : '' }}>Delivery Van</option>
                                                                <option value="truck" {{ old('vehicle_type', $agent->vehicle_type) === 'truck' ? 'selected' : '' }}>Heavy Truck</option>
                                                                <option value="bicycle" {{ old('vehicle_type', $agent->vehicle_type) === 'bicycle' ? 'selected' : '' }}>Bicycle Courier</option>
                                                                <option value="car" {{ old('vehicle_type', $agent->vehicle_type) === 'car' ? 'selected' : '' }}>Sedan / Car</option>
                                                                <option value="other" {{ old('vehicle_type', $agent->vehicle_type) === 'other' ? 'selected' : '' }}>Other Vehicle</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">License Plate / Vehicle Reg</label>
                                                            <input type="text" name="license_plate" class="form-control font-monospace" value="{{ old('license_plate', $agent->license_plate) }}" placeholder="e.g. 1AB-1234 PP">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Assigned Operating Zone</label>
                                                            <input type="text" name="zone" class="form-control" value="{{ old('zone', $agent->zone) }}" placeholder="e.g. Phnom Penh Central, Toul Kork">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-semibold">Duty Status <span class="text-danger">*</span></label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="available" {{ old('status', $agent->status) === 'available' ? 'selected' : '' }}>Available</option>
                                                                <option value="on_delivery" {{ old('status', $agent->status) === 'on_delivery' ? 'selected' : '' }}>On Delivery</option>
                                                                <option value="inactive" {{ old('status', $agent->status) === 'inactive' ? 'selected' : '' }}>Inactive / Off-Duty</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-check form-switch mt-1">
                                                                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="edit_is_active_{{ $agent->id }}" {{ old('is_active', $agent->is_active) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-semibold" for="edit_is_active_{{ $agent->id }}">Account Active (available for assignment)</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label fw-semibold">Internal Notes</label>
                                                            <textarea name="notes" class="form-control" rows="2" placeholder="Driver certifications, special instructions...">{{ old('notes', $agent->notes) }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                            <!-- Delete Modal for Agent -->
                            @can('delete delivery agents')
                                <div class="modal fade" id="deleteDeliveryAgentModal-{{ $agent->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.delivery-agents.destroy', $agent) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold text-danger">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Delete Delivery Agent
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-1">Are you sure you want to delete delivery agent <strong>{{ $agent->name }}</strong> (<code>{{ $agent->code }}</code>)?</p>
                                                    @if($agent->deliveries_count > 0)
                                                        <div class="alert alert-warning py-2 px-3 mt-2 mb-0" style="font-size: 12px;">
                                                            <i class="bi bi-info-circle me-1"></i> This agent has {{ $agent->deliveries_count }} recorded deliveries. They can only be deleted if they have no active ongoing deliveries.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x display-4 d-block mb-2 text-secondary"></i>
                                    <h5>No delivery agents found</h5>
                                    <p class="mb-3">No couriers match your search or filter criteria.</p>
                                    @canany(['create delivery agents', 'edit delivery agents'])
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createDeliveryAgentModal">
                                            <i class="bi bi-plus-circle me-1"></i> Register First Delivery Agent
                                        </button>
                                    @endcanany
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($agents->hasPages())
                <div class="p-3 border-top">
                    {{ $agents->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Delivery Agent Modal -->
    @canany(['create delivery agents', 'edit delivery agents'])
        <div class="modal fade" id="createDeliveryAgentModal" tabindex="-1" aria-labelledby="createDeliveryAgentLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('admin.delivery-agents.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="createDeliveryAgentLabel">
                                <i class="bi bi-person-plus-fill text-primary me-2"></i>Register New Delivery Agent
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-semibold">Agent Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Sokha Vong" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Agent Code <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="code" id="new_agent_code" class="form-control font-monospace" value="{{ old('code', 'AGT-'.strtoupper(substr(uniqid(), -4))) }}" required>
                                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('new_agent_code').value = 'AGT-' + Math.floor(1000 + Math.random() * 9000)">
                                            <i class="bi bi-shuffle"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="e.g. +855 12 345 678" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="e.g. agent@courier.local">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vehicle Type <span class="text-danger">*</span></label>
                                    <select name="vehicle_type" class="form-select" required>
                                        <option value="motorcycle" {{ old('vehicle_type', 'motorcycle') === 'motorcycle' ? 'selected' : '' }}>Motorcycle / Scooter</option>
                                        <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Delivery Van</option>
                                        <option value="truck" {{ old('vehicle_type') === 'truck' ? 'selected' : '' }}>Heavy Truck</option>
                                        <option value="bicycle" {{ old('vehicle_type') === 'bicycle' ? 'selected' : '' }}>Bicycle Courier</option>
                                        <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Sedan / Car</option>
                                        <option value="other" {{ old('vehicle_type') === 'other' ? 'selected' : '' }}>Other Vehicle</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">License Plate / Vehicle Reg</label>
                                    <input type="text" name="license_plate" class="form-control font-monospace" value="{{ old('license_plate') }}" placeholder="e.g. 1AB-1234 PP">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Assigned Operating Zone</label>
                                    <input type="text" name="zone" class="form-control" value="{{ old('zone') }}" placeholder="e.g. Phnom Penh Central, Toul Kork">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Initial Duty Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="on_delivery" {{ old('status') === 'on_delivery' ? 'selected' : '' }}>On Delivery</option>
                                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive / Off-Duty</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="new_is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="new_is_active">Account Active (available for assignment)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Internal Notes</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Driver certifications, special instructions, contact notes...">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Register Agent</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcanany
</x-app-layout>

