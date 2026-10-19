<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductModelDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreProductModelRequest;
use App\Http\Requests\Backend\UpdateProductModelRequest;
use App\Models\Brand;
use App\Models\ProductModel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductModelController extends Controller
{
    /**
     * Display a listing of hardware models.
     */
    public function index(Request $request, ProductModelDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = ProductModel::query()
            ->with('brand')
            ->withCount('products');

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('slug', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%")
                    ->orWhereHas('brand', function ($bq) use ($search) {
                        $bq->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        $models = $query->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        $brands = Brand::orderBy('name')->get();

        return view('backend.models.index', compact('models', 'brands', 'dataTable'));
    }

    /**
     * Show the form for creating a new hardware model.
     */
    public function create(): View
    {
        $brands = Brand::orderBy('name')->get();

        return view('backend.models.create', compact('brands'));
    }

    /**
     * Store a newly created model in storage.
     */
    public function store(StoreProductModelRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (empty($validated['slug'])) {
            $validated['slug'] = str()->slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('models', 'public');
        }

        ProductModel::create($validated);

        return redirect()->route('admin.models.index')->with('success', 'Model created successfully.');
    }

    /**
     * Show the form for editing the specified model.
     */
    public function edit(ProductModel $model): View
    {
        $brands = Brand::orderBy('name')->get();

        return view('backend.models.edit', compact('model', 'brands'));
    }

    /**
     * Update the specified model in storage.
     */
    public function update(UpdateProductModelRequest $request, ProductModel $model): RedirectResponse
    {
        $validated = $request->validated();
        if (empty($validated['slug'])) {
            $validated['slug'] = str()->slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_image')) {
            if ($model->image && Storage::disk('public')->exists($model->image)) {
                Storage::disk('public')->delete($model->image);
            }
            $validated['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($model->image && Storage::disk('public')->exists($model->image)) {
                Storage::disk('public')->delete($model->image);
            }
            $validated['image'] = $request->file('image')->store('models', 'public');
        }

        $model->update($validated);

        return redirect()->route('admin.models.index')->with('success', 'Model updated successfully.');
    }

    /**
     * Remove the specified model from storage.
     */
    public function destroy(ProductModel $model): RedirectResponse
    {
        if ($model->products()->count() > 0) {
            return back()->with('error', 'Cannot delete model with associated products.');
        }

        if ($model->image && Storage::disk('public')->exists($model->image)) {
            Storage::disk('public')->delete($model->image);
        }

        $model->delete();

        return redirect()->route('admin.models.index')->with('success', 'Model deleted successfully.');
    }

    /**
     * Get active models filtered by brand for dynamic AJAX selects.
     */
    public function byBrand(?int $brandId = null): JsonResponse
    {
        $query = ProductModel::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $models = $query->get(['id', 'brand_id', 'name', 'slug']);

        return response()->json($models);
    }
}
