<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-cpu" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Hardware Catalog</p>
                    <h1 class="h3 mb-1">Add Hardware Model</h1>
                    <p class="text-muted mb-0">Create a hardware model or product series associated with a brand manufacturer.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.models.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Models
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
            <form action="{{ route('admin.models.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Brand / Manufacturer *</label>
                        <select name="brand_id" required class="form-select">
                            <option value="">-- Select Brand --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', request('brand_id')) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">Select the brand this hardware model belongs to.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Model Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="form-control" placeholder="e.g. ROG Strix GeForce RTX 4090">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Slug (Optional)</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" class="form-control font-monospace" placeholder="e.g. rog-strix-rtx-4090 (auto-generated if empty)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="form-control" placeholder="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Description / Series Details</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Flagship gaming graphics card series with axial-tech fans and vapor chamber...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <x-logo-uploader 
                            prefix="model" 
                            input-name="image" 
                            remove-input-name="remove_image" 
                            label="Model Image / Badge" 
                        />
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="modelActive" checked>
                            <label class="form-check-label fw-bold small" for="modelActive">Model is Active</label>
                        </div>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Model
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
