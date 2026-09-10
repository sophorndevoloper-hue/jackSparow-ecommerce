<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-patch-plus" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manufacturers</p>
                    <h1 class="h3 mb-1">Add Hardware Brand</h1>
                    <p class="text-muted mb-0">Add a manufacturer or component brand to the catalog.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Brands
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel p-4 mt-3">
            <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Brand Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. NVIDIA">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Official Website</label>
                        <input type="url" name="website" value="{{ old('website') }}" class="form-control" placeholder="https://www.nvidia.com">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Slug (Optional)</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" class="form-control font-monospace" placeholder="e.g. nvidia (auto-generated if empty)">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Leader in visual computing and GPU architecture...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <x-logo-uploader prefix="brand" />
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="brandActive" checked>
                            <label class="form-check-label fw-bold small" for="brandActive">Brand is Active</label>
                        </div>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Brand
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

