<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Manufacturers</p>
                    <h1 class="h3 mb-1">Edit Brand: {{ $brand->name }}</h1>
                    <p class="text-muted mb-0">Update brand details and online profile.</p>
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
            <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Brand Name *</label>
                        <input type="text" name="name" value="{{ old('name', $brand->name) }}" required class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Official Website</label>
                        <input type="url" name="website" value="{{ old('website', $brand->website) }}" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $brand->slug) }}" class="form-control font-monospace">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $brand->description) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <x-logo-uploader 
                            prefix="brand" 
                            :current-logo="$brand->logo_url" 
                            :entity-name="$brand->name" 
                        />
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="brandActive" {{ old('is_active', $brand->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="brandActive">Brand is Active</label>
                        </div>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Brand
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

