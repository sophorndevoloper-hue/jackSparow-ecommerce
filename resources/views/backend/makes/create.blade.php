<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-tools" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manufacturers & OEM</p>
                    <h1 class="h3 mb-1">Add Hardware Make</h1>
                    <p class="text-muted mb-0">Add a hardware make or OEM manufacturer to the catalog.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.makes.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Makes
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
            <form action="{{ route('admin.makes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Make Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. Dell, HP, Lenovo">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Official Website</label>
                        <input type="url" name="website" value="{{ old('website') }}" class="form-control" placeholder="https://www.dell.com">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Slug (Optional)</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" class="form-control font-monospace" placeholder="e.g. dell (auto-generated if empty)">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="OEM manufacturer and technology provider...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <x-logo-uploader prefix="make" />
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="makeActive" checked>
                            <label class="form-check-label fw-bold small" for="makeActive">Make is Active</label>
                        </div>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Make
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

