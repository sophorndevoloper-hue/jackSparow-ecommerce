<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\ProductDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\StoreProductRequest;
use App\Http\Requests\Backend\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Make;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\Warehouse;
use App\Services\SerialTrackingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(
        protected SerialTrackingService $serialTrackingService
    ) {}

    /**
     * Display a listing of computer hardware products with filtering.
     */
    public function index(Request $request, ProductDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = Product::query()->with(['category', 'brand', 'make', 'images', 'primaryImage', 'priceTiers', 'warehouses']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('make_id')) {
            $query->where('make_id', $request->input('make_id'));
        }

        if ($request->filled('socket')) {
            $query->where('socket', $request->input('socket'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%")
                    ->orWhere('mpn', 'ilike', "%{$search}%")
                    ->orWhere('upc_ean', 'ilike', "%{$search}%");
            });
        }

        if ($request->input('stock') === 'low') {
            $query->lowStock();
        } elseif ($request->input('stock') === 'out') {
            $query->outOfStock();
        }

        $products = $query->latest('updated_at')->paginate(15)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $makes = Make::orderBy('name')->get();
        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        return view('backend.products.index', compact('products', 'categories', 'brands', 'makes', 'customerGroups', 'dataTable'));
    }

    /**
     * Show the form for creating a new hardware product.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $makes = Make::where('is_active', true)->orderBy('name')->get();
        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('backend.products.create', compact('categories', 'brands', 'makes', 'customerGroups', 'warehouses'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = str()->slug($data['name']).'-'.rand(100, 999);
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured', false);
        $data['requires_serial_tracking'] = $request->boolean('requires_serial_tracking', false);
        $data['is_hazmat'] = $request->boolean('is_hazmat', false);

        // Reconstruct specifications key-value map
        $specifications = [];
        if (! empty($data['spec_keys']) && ! empty($data['spec_values'])) {
            foreach ($data['spec_keys'] as $index => $key) {
                if (! empty($key) && isset($data['spec_values'][$index])) {
                    $specifications[trim($key)] = trim($data['spec_values'][$index]);
                }
            }
        }
        $data['specifications'] = $specifications;
        $data['specs'] = $specifications;

        return DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);

            // Save B2B pricing tiers if provided
            $this->syncPriceTiers($product, $request);

            // Handle uploaded images if any provided
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $index === 0,
                        'sort_order' => $index,
                    ]);
                }
            } elseif ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }

            if ($request->input('action') === 'save') {
                return redirect()->route('admin.products.index')->with('success', "Hardware product '{$product->name}' created successfully.");
            }

            return redirect()->route('admin.products.images.index', ['product_id' => $product->id])
                ->with('success', "Hardware product '{$product->name}' created! You can now upload and crop photos.");
        });
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('product.show', $product->slug);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['images', 'priceTiers.customerGroup', 'warehouses', 'serialNumbers' => function ($q) {
            $q->latest()->limit(50);
        }, 'compatibilities.compatibleProduct']);

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $makes = Make::orderBy('name')->get();
        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $allProducts = Product::where('id', '!=', $product->id)->orderBy('name')->get(['id', 'name', 'sku', 'socket']);

        return view('backend.products.edit', compact('product', 'categories', 'brands', 'makes', 'customerGroups', 'warehouses', 'allProducts'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = str()->slug($data['name']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['requires_serial_tracking'] = $request->boolean('requires_serial_tracking');
        $data['is_hazmat'] = $request->boolean('is_hazmat');

        // Reconstruct specifications key-value map
        $specifications = [];
        if (! empty($data['spec_keys']) && ! empty($data['spec_values'])) {
            foreach ($data['spec_keys'] as $index => $key) {
                if (! empty($key) && isset($data['spec_values'][$index])) {
                    $specifications[trim($key)] = trim($data['spec_values'][$index]);
                }
            }
        }
        $data['specifications'] = $specifications;
        $data['specs'] = $specifications;

        $serialCount = $product->serialNumbers()->count();
        $isSerialTracked = $product->requires_serial_tracking
            || $request->boolean('requires_serial_tracking')
            || $serialCount > 0;

        if ($isSerialTracked && $serialCount > 0 && ! $request->boolean('allow_stock_mismatch')) {
            $inputStock = (int) ($data['stock_quantity'] ?? 0);
            if ($inputStock !== $serialCount) {
                $comparison = $inputStock > $serialCount ? 'more than' : 'less than';
                $errorMessage = "Total stock quantity ({$inputStock}) is {$comparison} tracked serial numbers ({$serialCount}). Please adjust total stock quantity to {$serialCount} or register matching serial numbers.";

                return redirect()
                    ->to(route('admin.products.edit', $product->id).'#inventory')
                    ->withInput()
                    ->with('error', $errorMessage);
            }
        }

        return DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);

            // Sync B2B Price Tiers
            $this->syncPriceTiers($product, $request);

            // Handle deleted images
            if ($request->filled('delete_image_ids')) {
                $deleteIds = (array) $request->input('delete_image_ids');
                $imagesToDelete = $product->images()->whereIn('id', $deleteIds)->get();
                foreach ($imagesToDelete as $delImg) {
                    if ($delImg->image_path && Storage::disk('public')->exists($delImg->image_path)) {
                        Storage::disk('public')->delete($delImg->image_path);
                    }
                    $delImg->delete();
                }
            }

            // Handle uploaded images
            $hasExistingPrimary = $product->images()->where('is_primary', true)->exists();
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => ! $hasExistingPrimary && $index === 0,
                        'sort_order' => $product->images()->count(),
                    ]);
                }
            } elseif ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $product->images()->create([
                    'image_path' => $path,
                    'is_primary' => ! $hasExistingPrimary,
                    'sort_order' => $product->images()->count(),
                ]);
            }

            // Ensure at least one image is primary if images exist
            if ($product->images()->exists() && ! $product->images()->where('is_primary', true)->exists()) {
                $product->images()->first()?->update(['is_primary' => true]);
            }

            return redirect()->route('admin.products.index')->with('success', 'Hardware product updated successfully.');
        });
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product removed successfully.');
    }

    /**
     * Ingest batch serial numbers directly from product edit page.
     */
    public function storeSerials(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'serials_text' => ['required', 'string'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $cost = ! empty($validated['cost_price']) ? (float) $validated['cost_price'] : (float) $product->cost_price;

        try {
            $created = $this->serialTrackingService->ingestSerials(
                $product,
                $warehouse,
                $validated['serials_text'],
                $cost
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully registered {$created->count()} serial numbers into {$warehouse->name}.",
                    'count' => $created->count(),
                    'total_count' => $product->fresh()->serialNumbers()->count(),
                    'serials' => $created->map(fn ($sn) => [
                        'id' => $sn->id,
                        'serial_number' => $sn->serial_number,
                        'warehouse_name' => $sn->warehouse?->name ?? 'Default',
                        'status' => $sn->status,
                        'warranty_end_date' => $sn->warranty_end_date ? $sn->warranty_end_date->format('M d, Y') : '-',
                    ]),
                ]);
            }

            return back()->with('success', "Successfully registered {$created->count()} serial numbers into {$warehouse->name}.");
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'serials_text' => [$e->getMessage()],
                    ],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['serials_text' => $e->getMessage()]);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An error occurred while ingesting serial numbers: '.$e->getMessage(),
                    'errors' => [
                        'serials_text' => ['An error occurred while ingesting serial numbers: '.$e->getMessage()],
                    ],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['serials_text' => 'An error occurred while ingesting serial numbers: '.$e->getMessage()]);
        }
    }

    /**
     * Sync B2B Pricing Tiers from form arrays.
     */
    protected function syncPriceTiers(Product $product, Request $request): void
    {
        $minQtys = $request->input('tier_min_qty', []);
        $maxQtys = $request->input('tier_max_qty', []);
        $prices = $request->input('tier_price', []);
        $groupIds = $request->input('tier_customer_group_id', []);

        if (! is_array($prices) || empty($prices)) {
            return;
        }

        $product->priceTiers()->delete();

        foreach ($prices as $i => $price) {
            if ($price !== null && $price !== '') {
                $minQty = isset($minQtys[$i]) && $minQtys[$i] > 0 ? (int) $minQtys[$i] : 1;
                $maxQty = isset($maxQtys[$i]) && $maxQtys[$i] > 0 ? (int) $maxQtys[$i] : null;
                $groupId = ! empty($groupIds[$i]) ? (int) $groupIds[$i] : null;

                ProductPriceTier::create([
                    'product_id' => $product->id,
                    'customer_group_id' => $groupId,
                    'min_quantity' => $minQty,
                    'max_quantity' => $maxQty,
                    'unit_price' => (float) $price,
                    'currency' => 'USD',
                ]);
            }
        }
    }
}
