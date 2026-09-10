<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-tools" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manufacturers & OEM</p>
                    <h1 class="h3 mb-1">Hardware Makes</h1>
                    <p class="text-muted mb-0">Manage hardware makes and OEM manufacturers for components and devices.</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('create makes')
                    <a href="{{ route('admin.makes.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Make
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
            <form action="{{ route('admin.makes.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 520px;">
                    <div class="input-group input-group-sm w-100">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search make by name, slug, or website...">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.makes.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>

                <div class="small text-muted">
                    Showing <strong>{{ $makes->total() }}</strong> makes
                </div>
            </form>
        </div>

        <div class="panel p-3">
            <x-datatable id="makesTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="makesTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($makes as $make)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($make->logo_url)
                                            <img src="{{ $make->logo_url }}" alt="{{ $make->name }}" class="rounded-3 bg-body-secondary border p-1 object-fit-contain" style="width: 40px; height: 40px; min-width: 40px;">
                                        @else
                                            <div class="rounded-3 bg-body-secondary border text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; min-width: 40px;">
                                                {{ substr($make->name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $make->name }}</div>
                                            @if($make->description)
                                                <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;">{{ $make->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $make->slug }}</span></td>
                                <td>
                                    @if($make->website)
                                        <a href="{{ $make->website }}" target="_blank" class="small text-decoration-none">
                                            {{ parse_url($make->website, PHP_URL_HOST) ?? $make->website }} <i class="bi bi-box-arrow-up-right small"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                                        {{ $make->products_count }} components
                                    </span>
                                </td>
                                <td>
                                    @if($make->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        @can('edit makes')
                                            <a href="{{ route('admin.makes.edit', $make->id) }}" class="btn-ghost text-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete makes')
                                            <form action="{{ route('admin.makes.destroy', $make->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this make?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete" {{ $make->products_count > 0 ? 'disabled' : '' }}>
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
                                    <i class="bi bi-tools display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    @if(request('search'))
                                        No makes found matching "<strong>{{ request('search') }}</strong>".
                                        <div class="mt-2">
                                            <a href="{{ route('admin.makes.index') }}" class="btn btn-sm btn-outline-primary">Clear Search</a>
                                        </div>
                                    @else
                                        No makes found.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $makes->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

