<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Catalog Organization</p>
                    <h1 class="h3 mb-1">Hardware Categories</h1>
                    <p class="text-muted mb-0">Organize computer parts (CPUs, GPUs, RAM, Motherboards, PSUs, Storage).</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('create categories')
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Category
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

        <!-- Search & Filter Bar -->
        <div class="panel p-3 mt-3 mb-3">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 520px;">
                    <div class="input-group input-group-sm w-100">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search category by name, slug, or description...">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>

                <div class="small text-muted">
                    Showing <strong>{{ $categories->total() }}</strong> categories
                </div>
            </form>
        </div>

        <div class="panel p-3">
            <x-datatable id="categoriesTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="categoriesTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-body-secondary border text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                            <i class="bi bi-folder2-open fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $category->name }}</div>
                                            @if($category->description)
                                                <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;">{{ $category->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $category->slug }}</span></td>
                                <td>
                                    @if($category->parent)
                                        <span class="badge bg-body-secondary text-body-secondary border-0 px-2 py-1">{{ $category->parent->name }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                                        {{ $category->products_count }} components
                                    </span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('shop', ['category' => $category->slug]) }}" target="_blank" class="btn-ghost" title="View in Shop">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        @can('edit categories')
                                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn-ghost text-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete categories')
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete" {{ $category->products_count > 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-grid-3x3-gap display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    @if(request('search'))
                                        No categories found matching "<strong>{{ request('search') }}</strong>".
                                        <div class="mt-2">
                                            <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-primary">Clear Search</a>
                                        </div>
                                    @else
                                        No categories found.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

