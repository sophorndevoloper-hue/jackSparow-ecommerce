<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manufacturers</p>
                    <h1 class="h3 mb-1">Hardware Brands</h1>
                    <p class="text-muted mb-0">Manage hardware manufacturers (AMD, Intel, NVIDIA, ASUS, MSI, Corsair, Samsung).</p>
                </div>
            </div>
            <div class="heading-actions">
                @can('create brands')
                    <a href="{{ route('admin.brands.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Brand
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
            <form action="{{ route('admin.brands.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 520px;">
                    <div class="input-group input-group-sm w-100">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search brand by name, slug, or website...">
                    </div>
                    @if(request('search'))
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </div>

                <div class="small text-muted">
                    Showing <strong>{{ $brands->total() }}</strong> brands
                </div>
            </form>
        </div>

        <div class="panel p-3">
            <x-datatable id="brandsTable" :dataTable="$dataTable">
                <table class="table table-hover align-middle mb-0" id="brandsTable">
                    <x-datatable.thead :dataTable="$dataTable" />
                    <tbody>
                        @forelse($brands as $brand)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($brand->logo_url)
                                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="rounded-3 bg-body-secondary border p-1 object-fit-contain" style="width: 40px; height: 40px; min-width: 40px;">
                                        @else
                                            <div class="rounded-3 bg-body-secondary border text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; min-width: 40px;">
                                                {{ substr($brand->name, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold text-body-emphasis">{{ $brand->name }}</div>
                                            @if($brand->description)
                                                <div class="text-muted small text-truncate" style="max-width: 250px; font-size: 11px;">{{ $brand->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-body-secondary border-0 font-monospace">{{ $brand->slug }}</span></td>
                                <td>
                                    @if($brand->website)
                                        <a href="{{ $brand->website }}" target="_blank" class="small text-decoration-none">
                                            {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }} <i class="bi bi-box-arrow-up-right small"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                                        {{ $brand->products_count }} components
                                    </span>
                                </td>
                                <td>
                                    @if($brand->is_active)
                                        <span class="badge bg-success-subtle text-success border-0 px-2 py-1">
                                            <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('shop', ['brand' => $brand->slug]) }}" target="_blank" class="btn-ghost" title="View Brand in Store">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                        @can('edit brands')
                                            <a href="{{ route('admin.brands.edit', $brand->id) }}" class="btn-ghost text-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endcan
                                        @can('delete brands')
                                            <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this brand?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-ghost text-danger" title="Delete" {{ $brand->products_count > 0 ? 'disabled' : '' }}>
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
                                    <i class="bi bi-patch-check display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    @if(request('search'))
                                        No brands found matching "<strong>{{ request('search') }}</strong>".
                                        <div class="mt-2">
                                            <a href="{{ route('admin.brands.index') }}" class="btn btn-sm btn-outline-primary">Clear Search</a>
                                        </div>
                                    @else
                                        No brands found.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </x-datatable>

            <div class="p-3 border-top">
                {{ $brands->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

