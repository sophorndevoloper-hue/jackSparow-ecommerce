<x-app-layout>
    <style>
        .collections-thead th {
            background-color: var(--admin-surface-soft, #f8fafc);
            color: var(--admin-muted, #6b7280);
            border-bottom: 1px solid var(--admin-border, #dbe4ef);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .collection-thumb-box {
            background-color: var(--admin-surface-soft, #f8fafc);
            border: 1px solid var(--admin-border, #dbe4ef);
            color: var(--admin-primary, #2563eb);
        }

        /* Dark Mode Theme Support */
        html[data-theme="dark"] .collections-thead th,
        html[data-bs-theme="dark"] .collections-thead th {
            background-color: #111827 !important;
            color: #94a3b8 !important;
            border-bottom-color: #2f3b52 !important;
        }
        html[data-theme="dark"] .collection-thumb-box,
        html[data-bs-theme="dark"] .collection-thumb-box {
            background-color: #111827 !important;
            border-color: #2f3b52 !important;
            color: #60a5fa !important;
        }
        html[data-theme="dark"] .table,
        html[data-bs-theme="dark"] .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e5edf7;
            --bs-table-border-color: #2f3b52;
        }
        html[data-theme="dark"] .table tbody tr:hover,
        html[data-bs-theme="dark"] .table tbody tr:hover {
            background-color: #141c2b !important;
        }
    </style>

    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-collection" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Catalog Organization</p>
                    <h1 class="h3 mb-1">Product Collections</h1>
                    <p class="text-muted mb-0">Curate and feature hardware component groupings (e.g. Gaming Bundles, Workstation Builds, Flash Deals).</p>
                </div>
            </div>
            <div class="heading-actions">
                @canany(['create collections', 'create products'])
                    <a href="{{ route('admin.collections.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Collection
                    </a>
                @endcanany
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

        <!-- Search & Filter Bar -->
        <div class="panel p-3 mt-3 mb-3">
            <form action="{{ route('admin.collections.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 520px;">
                    <div class="input-group input-group-sm w-100">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search collection by name, slug, or description...">
                    </div>
                    @if(request('search') || request('status'))
                        <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <select name="status" class="form-select form-select-sm" style="width: 140px;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <span class="small text-muted">
                        Total: <strong>{{ $collections->total() }}</strong>
                    </span>
                </div>
            </form>
        </div>

        <!-- Collections Table -->
        <div class="panel p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="collections-thead">
                        <tr>
                            <th>Collection</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $collection)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 collection-thumb-box d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                            @if($collection->image)
                                                <img src="{{ $collection->image_url }}" alt="{{ $collection->name }}" class="w-100 h-100 object-fit-cover rounded-3">
                                            @else
                                                <i class="bi bi-collection fs-5"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $collection->name }}</div>
                                            @if($collection->description)
                                                <div class="text-muted small text-truncate" style="max-width: 280px; font-size: 11px;">
                                                    {{ Str::limit($collection->description, 60) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-body-secondary font-monospace">{{ $collection->slug }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                                        <i class="bi bi-cpu me-1"></i>{{ $collection->products_count }} products
                                    </span>
                                </td>
                                <td>
                                    @if($collection->is_featured)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-star-fill me-1"></i>Featured
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($collection->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-monospace small text-muted">{{ $collection->sort_order }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @canany(['edit collections', 'edit products'])
                                            <a href="{{ route('admin.collections.edit', $collection->id) }}" class="btn btn-outline-secondary btn-sm py-1 px-2" title="Edit Collection">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endcanany

                                        @canany(['delete collections', 'delete products'])
                                            <form action="{{ route('admin.collections.destroy', $collection->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this collection? Products inside will not be deleted.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Collection">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-collection fs-1 d-block mb-2 opacity-50"></i>
                                    <p class="mb-1 fw-semibold">No product collections found.</p>
                                    <small>Start by clicking "Add Collection" above to curate your first product grouping.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($collections->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <span class="small text-muted">
                        Showing {{ $collections->firstItem() }} to {{ $collections->lastItem() }} of {{ $collections->total() }} collections
                    </span>
                    {{ $collections->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
