<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <!-- Page Header -->
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Sales &amp; Fulfillment</p>
                    <h1 class="h3 mb-1">Shipping Methods</h1>
                    <p class="text-muted mb-0">Configure carriers, base shipping rates, free delivery thresholds, and transit times.</p>
                </div>
            </div>
            <div class="heading-actions d-flex align-items-center gap-2">
                @canany(['create shipping methods', 'edit shipping methods'])
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createShippingMethodModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Shipping Method
                    </button>
                @endcanany
                @can('view deliveries')
                    <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-truck me-1"></i> Deliveries
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
            <div class="col-12 col-sm-4">
                <div class="panel p-3 border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Total Methods</small>
                            <h3 class="mb-0 mt-1 fw-bold text-dark">{{ $totalMethods }}</h3>
                            <small class="text-muted">Configured options</small>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-3">
                            <i class="bi bi-signpost-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-4">
                <div class="panel p-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Active Methods</small>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{{ $activeMethods }}</h3>
                            <small class="text-muted">Available at checkout</small>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-3">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-4">
                <div class="panel p-3 border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted text-uppercase fw-bold">Free Shipping Rules</small>
                            <h3 class="mb-0 mt-1 fw-bold text-info">{{ $freeShippingAvailable }}</h3>
                            <small class="text-muted">Threshold-based discounts</small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info p-3">
                            <i class="bi bi-gift fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Methods Table -->
        <div class="panel p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">Order</th>
                            <th>Shipping Option</th>
                            <th>Carrier</th>
                            <th>Base Cost</th>
                            <th>Free Shipping Over</th>
                            <th>Estimated Delivery</th>
                            <th>Deliveries Linked</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shippingMethods as $method)
                            <tr>
                                <td class="text-muted font-monospace small">#{{ $method->sort_order }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-3 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                                            <i class="bi {{ $method->base_cost == 0 ? 'bi-geo-alt' : ($method->base_cost > 30 ? 'bi-airplane' : 'bi-truck') }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $method->name }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">code: {{ $method->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body border">
                                        <i class="bi bi-building me-1"></i>{{ $method->carrier ?: 'Universal / Store' }}
                                    </span>
                                </td>
                                <td>
                                    @if($method->base_cost == 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">FREE ($0.00)</span>
                                    @else
                                        <span class="fw-bold font-monospace text-dark">${{ number_format($method->base_cost, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($method->min_order_amount)
                                        <span class="badge bg-info-subtle text-info border font-monospace">
                                            Orders &ge; ${{ number_format($method->min_order_amount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-body small">
                                        <i class="bi bi-clock me-1 text-muted"></i>{{ $method->estimated_days ?: 'Standard handling' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted border font-monospace">
                                        {{ $method->deliveries_count }} {{ str('shipment')->plural($method->deliveries_count) }}
                                    </span>
                                </td>
                                <td>
                                    @can('edit shipping methods')
                                        <form action="{{ route('admin.shipping-methods.toggle-active', $method->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm border-0 p-0" title="Click to toggle status">
                                                @if($method->is_active)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-muted border px-2 py-1">
                                                        <i class="bi bi-dash-circle me-1"></i>Inactive
                                                    </span>
                                                @endif
                                            </button>
                                        </form>
                                    @else
                                        @if($method->is_active)
                                            <span class="badge bg-success-subtle text-success border">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-muted border">Inactive</span>
                                        @endif
                                    @endcan
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @can('edit shipping methods')
                                            <button type="button" class="btn-ghost" data-bs-toggle="modal" data-bs-target="#editShippingMethodModal_{{ $method->id }}" title="Edit Shipping Method">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endcan

                                        @can('delete shipping methods')
                                            <form action="{{ route('admin.shipping-methods.destroy', $method->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove {{ $method->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete Shipping Method">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal for Method #{{ $method->id }} -->
                            <div class="modal fade" id="editShippingMethodModal_{{ $method->id }}" tabindex="-1" aria-labelledby="editModalLabel_{{ $method->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg border-0">
                                        <form action="{{ route('admin.shipping-methods.update', $method->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-primary-subtle text-primary border-bottom border-primary-subtle">
                                                <h5 class="modal-title fw-bold" id="editModalLabel_{{ $method->id }}">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit Shipping Method
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Method Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $method->name) }}" required>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Identifier Code <span class="text-danger">*</span></label>
                                                        <input type="text" name="code" class="form-control font-monospace" value="{{ old('code', $method->code) }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Carrier / Provider</label>
                                                        <input type="text" name="carrier" class="form-control" value="{{ old('carrier', $method->carrier) }}" placeholder="e.g. FedEx Ground">
                                                    </div>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Base Cost ($) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.01" min="0" name="base_cost" class="form-control font-monospace" value="{{ old('base_cost', $method->base_cost) }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Free Shipping Over ($)</label>
                                                        <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control font-monospace" value="{{ old('min_order_amount', $method->min_order_amount) }}" placeholder="Leave blank if none">
                                                    </div>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-8">
                                                        <label class="form-label fw-semibold">Estimated Days</label>
                                                        <input type="text" name="estimated_days" class="form-control" value="{{ old('estimated_days', $method->estimated_days) }}" placeholder="e.g. 2 - 3 business days">
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label fw-semibold">Sort Order</label>
                                                        <input type="number" name="sort_order" class="form-control font-monospace" value="{{ old('sort_order', $method->sort_order) }}">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Description</label>
                                                    <textarea name="description" class="form-control" rows="2" placeholder="Brief notes for customers during checkout...">{{ old('description', $method->description) }}</textarea>
                                                </div>

                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="is_active_{{ $method->id }}" name="is_active" value="1" {{ $method->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="is_active_{{ $method->id }}">Method Active &amp; Available</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light-subtle d-flex justify-content-between">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm px-3">Update Method</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-signpost-split fs-2 d-block mb-2"></i>
                                    No shipping methods configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Shipping Method Modal -->
    <div class="modal fade" id="createShippingMethodModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <form action="{{ route('admin.shipping-methods.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold" id="createModalLabel">
                            <i class="bi bi-plus-circle me-1"></i> Add Shipping Method
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Method Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Express Priority Air" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Identifier Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. express_air" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Carrier / Provider</label>
                                <input type="text" name="carrier" class="form-control" placeholder="e.g. DHL Express">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Base Cost ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="base_cost" class="form-control font-monospace" value="0.00" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Free Shipping Over ($)</label>
                                <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control font-monospace" placeholder="Optional threshold">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label fw-semibold">Estimated Delivery Time</label>
                                <input type="text" name="estimated_days" class="form-control" placeholder="e.g. 1 - 2 business days">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control font-monospace" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Customer details shown during checkout..."></textarea>
                        </div>

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="create_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label fw-semibold" for="create_is_active">Make Active Immediately</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light-subtle d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3">Save Shipping Method</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

