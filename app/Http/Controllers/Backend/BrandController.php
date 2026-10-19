<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BrandDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreBrandRequest;
use App\Http\Requests\Backend\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    /**
     * Display a listing of hardware manufacturers/brands.
     */
    public function index(Request $request, BrandDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $brands = Brand::query()
            ->withCount('products')
            ->search($request->input('search'), ['name', 'slug', 'website', 'description'])
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('backend.brands.index', compact('brands', 'dataTable'));
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create(): View
    {
        return view('backend.brands.create');
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->mediaService->upload($request->file('logo'), 'brands');
        }

        Brand::create($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): View
    {
        return view('backend.brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_logo')) {
            $this->mediaService->delete($brand->logo);
            $validated['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            $validated['logo'] = $this->mediaService->replace($brand->logo, $request->file('logo'), 'brands');
        }

        $brand->update($validated);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->count() > 0) {
            return back()->with('error', 'Cannot delete brand with associated products.');
        }

        $this->mediaService->delete($brand->logo);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
