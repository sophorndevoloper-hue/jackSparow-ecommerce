<x-app-layout>
    <div class="container-fluid px-3 px-lg-4 py-4">
        <div class="page-heading">
            <div class="page-heading-copy">
                <span class="page-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span>
                <div>
                    <p class="eyebrow mb-1">Categories</p>
                    <h1 class="h3 mb-1">Edit Category: {{ $category->name }}</h1>
                    <p class="text-muted mb-0">Update category details and hierarchy.</p>
                </div>
            </div>
            <div class="heading-actions">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                    &larr; Back to Categories
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
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">Category Name *</label>
                        <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Parent Category</label>
                        <select name="parent_id" class="form-select">
                            <option value="">None (Top-Level Category)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control font-monospace">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="form-control">
                    </div>

                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="catActive" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small" for="catActive">Category is Active</label>
                        </div>
                    </div>

                    <div class="col-12 pt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-save me-1"></i> Update Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

