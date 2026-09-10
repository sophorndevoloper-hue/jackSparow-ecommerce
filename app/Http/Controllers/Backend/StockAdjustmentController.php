<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockAdjustmentDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of stock adjustment logs.
     */
    public function index(Request $request, StockAdjustmentDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = StockAdjustment::query()->with(['warehouse', 'user', 'items.product']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->input('reason'));
        }

        $adjustments = $query->latest('updated_at')->paginate(15)->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return view('backend.stock.adjustments.index', compact('adjustments', 'warehouses', 'dataTable'));
    }

    /**
     * Show the form for creating a new stock adjustment.
     */
    public function create(Request $request): View
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $products = Product::with('category')->orderBy('name')->get();
        $productsData = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'category' => $p->category?->name ?? 'Hardware',
            'serial_tracking' => (bool) $p->requires_serial_tracking,
        ]);
        $selectedWarehouseId = $request->input('warehouse_id', $warehouses->first()?->id);

        return view('backend.stock.adjustments.create', compact('warehouses', 'products', 'productsData', 'selectedWarehouseId'));
    }

    /**
     * Store a newly created stock adjustment and synchronize warehouse stock.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', 'in:addition,subtraction,correction'],
            'reason' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:0'],
            'products.*.serials' => ['nullable', 'string'],
        ]);

        $warehouse = Warehouse::findOrFail($request->input('warehouse_id'));
        $type = $request->input('type');
        $userId = auth('backend')->id() ?? auth()->id();

        try {
            DB::transaction(function () use ($request, $warehouse, $type, $userId) {
                $adjustment = StockAdjustment::create([
                    'reference_number' => StockAdjustment::generateReference(),
                    'warehouse_id' => $warehouse->id,
                    'user_id' => $userId,
                    'type' => $type,
                    'reason' => $request->input('reason'),
                    'notes' => $request->input('notes'),
                ]);

                foreach ($request->input('products') as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);
                    $oldQuantity = $warehouse->getProductStock($product);
                    $inputQty = (int) $itemData['quantity'];

                    // Parse and clean serial numbers if provided
                    $cleanedSerials = [];
                    if (! empty($itemData['serials'])) {
                        $rawList = preg_split('/[\r\n,]+/', (string) $itemData['serials']);
                        foreach ($rawList as $raw) {
                            $serial = strtoupper(trim((string) $raw));
                            if ($serial !== '') {
                                $cleanedSerials[] = $serial;
                            }
                        }
                    }

                    // If serial numbers are provided, auto-sync quantity if quantity was default or zero
                    if (! empty($cleanedSerials)) {
                        $serialCount = count($cleanedSerials);
                        if ($inputQty === 0 || $inputQty === 1) {
                            $inputQty = $serialCount;
                        }

                        // Validate internal batch duplicates
                        $counts = array_count_values($cleanedSerials);
                        $duplicates = array_keys(array_filter($counts, fn ($c) => $c > 1));
                        if (! empty($duplicates)) {
                            throw new InvalidArgumentException("Duplicate serial number(s) entered for '{$product->name}': ".implode(', ', $duplicates));
                        }

                        if ($type === 'addition' || $type === 'correction') {
                            // Check existing serials in inventory
                            $existing = SerialNumber::whereIn('serial_number', $cleanedSerials)->pluck('serial_number')->toArray();
                            if (! empty($existing)) {
                                throw new InvalidArgumentException("Serial number(s) already registered in inventory for '{$product->name}': ".implode(', ', $existing));
                            }

                            // Create serial numbers in warehouse
                            foreach ($cleanedSerials as $serial) {
                                SerialNumber::create([
                                    'product_id' => $product->id,
                                    'warehouse_id' => $warehouse->id,
                                    'serial_number' => $serial,
                                    'status' => SerialNumber::STATUS_IN_STOCK,
                                    'cost_price' => $product->cost_price,
                                    'inbound_date' => now()->toDateString(),
                                ]);
                            }

                            if (! $product->requires_serial_tracking) {
                                $product->update(['requires_serial_tracking' => true]);
                            }
                        } elseif ($type === 'subtraction') {
                            // Update matching serial numbers to defective/scrap
                            SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->whereIn('serial_number', $cleanedSerials)
                                ->update(['status' => SerialNumber::STATUS_DEFECTIVE_SCRAP]);
                        }
                    }

                    $newQuantity = match ($type) {
                        'addition' => $oldQuantity + $inputQty,
                        'subtraction' => max(0, $oldQuantity - $inputQty),
                        'correction' => max(0, $inputQty),
                    };

                    $adjustedQuantity = match ($type) {
                        'addition' => +$inputQty,
                        'subtraction' => -$inputQty,
                        'correction' => $newQuantity - $oldQuantity,
                    };

                    // Create adjustment line item
                    $adjustment->items()->create([
                        'product_id' => $product->id,
                        'old_quantity' => $oldQuantity,
                        'adjusted_quantity' => $adjustedQuantity,
                        'new_quantity' => $newQuantity,
                    ]);

                    // Update warehouse stock
                    $warehouse->setProductStock($product, $newQuantity);

                    // Recalculate global product stock
                    $product->syncTotalStock();
                }
            });
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.stock.adjustments.index')
            ->with('success', 'Stock adjustment recorded and inventory successfully synchronized.');
    }

    /**
     * Display details of a specific stock adjustment.
     */
    public function show(StockAdjustment $adjustment): View
    {
        $adjustment->load(['warehouse', 'user', 'items.product.category']);

        return view('backend.stock.adjustments.show', compact('adjustment'));
    }
}
