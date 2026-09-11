<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreProductCollectionRequest;
use App\Http\Requests\Backend\UpdateProductCollectionRequest;
use App\Models\Product;
use App\Models\ProductCollection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductCollectionController extends Controller
{
    /**
     * Display a listing of product collections.
     */
    public function index(Request $request): View
    {
        $query = ProductCollection::query()
            ->withCount('products');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $collections = $query->orderBy('sort_order')
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('backend.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new product collection.
     */
    public function create(): View
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'price']);

        return view('backend.collections.create', compact('products'));
    }

    /**
     * Store a newly created product collection in storage.
     */
    public function store(StoreProductCollectionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('collections', 'public');
        }

        $collection = ProductCollection::create($data);

        $productIds = $request->input('product_ids', []);
        if (! empty($productIds)) {
            $collection->products()->sync(is_array($productIds) ? $productIds : []);
        }

        return redirect()->route('admin.collections.index')
            ->with('success', "Product collection '{$collection->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified product collection.
     */
    public function edit(ProductCollection $collection): View
    {
        $collection->load('products:id,name,sku,price');
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'price']);

        return view('backend.collections.edit', compact('collection', 'products'));
    }

    /**
     * Update the specified product collection in storage.
     */
    public function update(UpdateProductCollectionRequest $request, ProductCollection $collection): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['is_active'] = $request->boolean('is_active', false);
        $data['is_featured'] = $request->boolean('is_featured', false);

        if ($request->boolean('remove_image')) {
            if ($collection->image && ! str_starts_with($collection->image, 'http')) {
                Storage::disk('public')->delete($collection->image);
            }
            $data['image'] = null;
        }

        if ($request->hasFile('image_file')) {
            if ($collection->image && ! str_starts_with($collection->image, 'http')) {
                Storage::disk('public')->delete($collection->image);
            }
            $data['image'] = $request->file('image_file')->store('collections', 'public');
        } elseif (! $request->boolean('remove_image') && ! $request->filled('image')) {
            $data['image'] = $collection->image;
        }

        $collection->update($data);

        $productIds = $request->input('product_ids', []);
        $collection->products()->sync(is_array($productIds) ? $productIds : []);

        return redirect()->route('admin.collections.index')
            ->with('success', "Product collection '{$collection->name}' updated successfully.");
    }

    /**
     * Remove the specified product collection from storage.
     */
    public function destroy(ProductCollection $collection): RedirectResponse
    {
        $name = $collection->name;
        if ($collection->image && ! str_starts_with($collection->image, 'http')) {
            Storage::disk('public')->delete($collection->image);
        }
        $collection->products()->detach();
        $collection->delete();

        return redirect()->route('admin.collections.index')
            ->with('success', "Product collection '{$name}' deleted successfully.");
    }
}
