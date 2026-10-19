<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cpu" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Hardware Catalog</p>
                    <h1 class="h3 mb-1">Hardware Models & Series</h1>
                    <p class="text-muted mb-0">Manage hardware models and series organized by brand (e.g. ROG Strix, RTX 4080 Super, TUF Gaming).</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('create models')
                    <a href="{{ route('admin.models.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Model
                    </a>
                @else
                    <a href="{{ route('admin.models.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Model
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

        <!-- Filter Bar -->
        <div class="panel p-3 mt-3 mb-3">
            <form action="{{ route('admin.models.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" style="max-width: 700px;">
                    <div class="input-group input-group-sm flex-grow-1" style="min-width: 240px;">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search model name, slug, or brand...">
                    </div>

                    <select name="brand_id" class="form-select form-select-sm" style="width: auto; min-width: 180px;" onchange="this.form.submit()">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>

                    @if(request('search') || request('brand_id'))
                        <a href="{{ route('admin.models.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>

                <div class="small text-muted">
                    Showing <strong>{{ $models->total() }}</strong> models
                </div>
            </form>
        </div>

        <div class="panel p-3">
            <x-datatable id="modelsTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="modelsTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($models as $modelItem)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($modelItem->image_url)
                                            <img src="{{ $modelItem->image_url }}" alt="{{ $modelItem->name }}" class="rounded-3 bg-body-secondary border p-1 object-fit-contain" style="width: 40px; height: 40px; min-width: 40px;">
                                        @else
                                            <div class="rounded-3 bg-body-secondary border text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; min-width: 40px;">
                                                <i class="bi bi-cpu fs-5"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $modelItem->name }}</div>
                                            <div class="text-muted small font-monospace" style="font-size: 11px;">{{ $modelItem->slug }}</div>
                                            @if($modelItem->description)
                                                <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;">{{ $modelItem->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($modelItem->brand)
                                        <span class="badge bg-secondary-subtle text-body-secondary border px-2 py-1">
                                            <i class="bi bi-tag-fill me-1 text-primary"></i>{{ $modelItem->brand->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                                        {{ $modelItem->products_count }} components
                                    </span>
                                </td>
                                <td>
                                    @if($modelItem->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $modelItem->updated_at?->format('Y-m-d H:i') ?? '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('admin.models.edit', $modelItem->id) }}" class="btn-ghost text-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.models.destroy', $modelItem->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this model?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-ghost text-danger" title="Delete" {{ $modelItem->products_count > 0 ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-cpu display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    @if(request('search') || request('brand_id'))
                                        No models found matching your criteria.
                                        <div class="mt-2">
                                            <a href="{{ route('admin.models.index') }}" class="btn btn-sm btn-outline-primary">Clear Filters</a>
                                        </div>
                                    @else
                                        No models registered yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $models->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
