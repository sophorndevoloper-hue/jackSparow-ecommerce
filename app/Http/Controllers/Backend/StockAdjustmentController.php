<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockAdjustmentDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
            'products.*.quantity' => ['nullable', 'integer', 'min:0'],
            'products.*.serials' => ['nullable', 'string'],
            'products.*.remove_serials' => ['nullable', 'string'],
            'products.*.new_serials' => ['nullable', 'string'],
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
                    $inputQty = isset($itemData['quantity']) ? (int) $itemData['quantity'] : 0;

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
                        $cleanedSerials = array_values(array_unique($cleanedSerials));
                    }

                    $removeSerials = [];
                    if (! empty($itemData['remove_serials'])) {
                        $rawList = preg_split('/[\r\n,]+/', (string) $itemData['remove_serials']);
                        foreach ($rawList as $raw) {
                            $serial = strtoupper(trim((string) $raw));
                            if ($serial !== '') {
                                $removeSerials[] = $serial;
                            }
                        }
                        $removeSerials = array_values(array_unique($removeSerials));
                    }

                    $newSerials = [];
                    if (! empty($itemData['new_serials'])) {
                        $rawList = preg_split('/[\r\n,]+/', (string) $itemData['new_serials']);
                        foreach ($rawList as $raw) {
                            $serial = strtoupper(trim((string) $raw));
                            if ($serial !== '') {
                                $newSerials[] = $serial;
                            }
                        }
                        $newSerials = array_values(array_unique($newSerials));
                    }

                    // Check duplicate serials in batch
                    $allSupplied = array_merge($cleanedSerials, $newSerials);
                    $counts = array_count_values($allSupplied);
                    $duplicates = array_keys(array_filter($counts, fn ($c) => $c > 1));
                    if (! empty($duplicates)) {
                        throw new InvalidArgumentException("Duplicate serial number(s) entered for '{$product->name}': ".implode(', ', $duplicates));
                    }

                    $deductStatus = match ($request->input('reason')) {
                        'damaged' => SerialNumber::STATUS_DEFECTIVE_SCRAP,
                        'loss' => SerialNumber::STATUS_OTHER,
                        default => SerialNumber::STATUS_OTHER,
                    };

                    if ($type === 'addition') {
                        if (! empty($cleanedSerials)) {
                            $inputQty = count($cleanedSerials);

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
                        }

                        if ($inputQty <= 0) {
                            throw new InvalidArgumentException("Please enter or select at least 1 serial number for '{$product->name}'.");
                        }

                        $newQuantity = $oldQuantity + $inputQty;
                        $adjustedQuantity = +$inputQty;

                    } elseif ($type === 'subtraction') {
                        if (! empty($cleanedSerials)) {
                            $inputQty = count($cleanedSerials);

                            // Check that matching serials exist and are in stock in this warehouse
                            $existing = SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->where('status', SerialNumber::STATUS_IN_STOCK)
                                ->whereIn('serial_number', $cleanedSerials)
                                ->pluck('serial_number')
                                ->toArray();

                            $missing = array_diff($cleanedSerials, $existing);
                            if (! empty($missing)) {
                                throw new InvalidArgumentException("The following serial number(s) were not found in stock in {$warehouse->name} for '{$product->name}': ".implode(', ', $missing));
                            }

                            // Update matching serial numbers to deducted status
                            SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->whereIn('serial_number', $cleanedSerials)
                                ->update([
                                    'status' => $deductStatus,
                                    'outbound_date' => now()->toDateString(),
                                    'notes' => 'Deducted via stock adjustment ('.$request->input('reason').')',
                                ]);
                        }

                        if ($inputQty <= 0) {
                            throw new InvalidArgumentException("Please enter or select at least 1 serial number for '{$product->name}'.");
                        }

                        $newQuantity = max(0, $oldQuantity - $inputQty);
                        $adjustedQuantity = -$inputQty;

                    } elseif ($type === 'correction') {
                        // 1. If explicit remove_serials provided, update them to deducted status
                        if (! empty($removeSerials)) {
                            $existingToRemove = SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->where('status', SerialNumber::STATUS_IN_STOCK)
                                ->whereIn('serial_number', $removeSerials)
                                ->pluck('serial_number')
                                ->toArray();

                            $missingToRemove = array_diff($removeSerials, $existingToRemove);
                            if (! empty($missingToRemove)) {
                                throw new InvalidArgumentException("The following serial number(s) to remove were not found in stock in {$warehouse->name} for '{$product->name}': ".implode(', ', $missingToRemove));
                            }

                            SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->whereIn('serial_number', $removeSerials)
                                ->update([
                                    'status' => $deductStatus,
                                    'outbound_date' => now()->toDateString(),
                                    'notes' => 'Removed via count correction audit ('.$request->input('reason').')',
                                ]);
                        }

                        // 2. Process newly added serial numbers
                        $serialsToCreate = $newSerials;
                        if (! empty($cleanedSerials)) {
                            $alreadyInDb = SerialNumber::whereIn('serial_number', $cleanedSerials)->pluck('serial_number')->toArray();
                            $brandNew = array_diff($cleanedSerials, $alreadyInDb);
                            $serialsToCreate = array_values(array_unique(array_merge($serialsToCreate, $brandNew)));
                        }

                        if (! empty($serialsToCreate)) {
                            $conflicting = SerialNumber::whereIn('serial_number', $serialsToCreate)->pluck('serial_number')->toArray();
                            if (! empty($conflicting)) {
                                throw new InvalidArgumentException("Serial number(s) already registered in inventory for '{$product->name}': ".implode(', ', $conflicting));
                            }

                            foreach ($serialsToCreate as $serial) {
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
                        }

                        // 3. If remove_serials was not explicitly provided, but cleanedSerials is passed as the confirmed in-stock list:
                        // Any existing in-stock serial in this warehouse NOT in cleanedSerials should be removed!
                        if (empty($removeSerials) && ! empty($cleanedSerials)) {
                            $existingInWarehouse = SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->where('status', SerialNumber::STATUS_IN_STOCK)
                                ->pluck('serial_number')
                                ->toArray();

                            $unconfirmed = array_diff($existingInWarehouse, $cleanedSerials);
                            if (! empty($unconfirmed)) {
                                SerialNumber::where('product_id', $product->id)
                                    ->where('warehouse_id', $warehouse->id)
                                    ->whereIn('serial_number', $unconfirmed)
                                    ->update([
                                        'status' => $deductStatus,
                                        'outbound_date' => now()->toDateString(),
                                        'notes' => 'Removed via count correction audit (unconfirmed unit)',
                                    ]);
                            }
                        }

                        // 4. Calculate exact new quantity:
                        $hasSerialsInWh = SerialNumber::where('product_id', $product->id)
                            ->where('warehouse_id', $warehouse->id)
                            ->exists();

                        if ($product->requires_serial_tracking || $hasSerialsInWh) {
                            $newQuantity = SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $warehouse->id)
                                ->where('status', SerialNumber::STATUS_IN_STOCK)
                                ->count();
                        } else {
                            $newQuantity = max(0, $inputQty);
                        }

                        $adjustedQuantity = $newQuantity - $oldQuantity;
                    }

                    // Create adjustment line item
                    $adjustment->items()->create([
                        'product_id' => $product->id,
                        'old_quantity' => $oldQuantity,
                        'adjusted_quantity' => $adjustedQuantity,
                        'new_quantity' => $newQuantity,
                        'serial_numbers' => ! empty($cleanedSerials) ? $cleanedSerials : null,
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
        $adjustment->load(['warehouse', 'user', 'items.product.category', 'items.product.brand']);

        return view('backend.stock.adjustments.show', compact('adjustment'));
    }

    /**
     * Get available in-stock serial numbers for a product in a warehouse (or all warehouses).
     */
    public function getProductSerials(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['nullable'],
        ]);

        $product = Product::findOrFail($request->input('product_id'));
        $warehouseId = $request->input('warehouse_id');

        // Get active warehouses and count of in-stock serials for this product per warehouse
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $countsByWarehouse = SerialNumber::query()
            ->where('product_id', $product->id)
            ->where('status', SerialNumber::STATUS_IN_STOCK)
            ->groupBy('warehouse_id')
            ->selectRaw('warehouse_id, count(*) as total')
            ->pluck('total', 'warehouse_id')
            ->toArray();

        $warehousesSummary = $warehouses->map(function ($wh) use ($countsByWarehouse) {
            return [
                'id' => $wh->id,
                'name' => $wh->name,
                'code' => $wh->code,
                'count' => (int) ($countsByWarehouse[$wh->id] ?? 0),
            ];
        })->values()->toArray();

        $totalInStockAll = array_sum($countsByWarehouse);

        $serialsQuery = SerialNumber::query()
            ->with(['warehouse:id,name,code'])
            ->where('product_id', $product->id)
            ->where('status', SerialNumber::STATUS_IN_STOCK)
            ->orderBy('serial_number');

        $selectedWarehouse = null;
        if ($warehouseId && $warehouseId !== 'all') {
            $selectedWarehouse = Warehouse::find($warehouseId);
            if ($selectedWarehouse) {
                $serialsQuery->where('warehouse_id', $selectedWarehouse->id);
            }
        }

        $serials = $serialsQuery->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'serial_number' => $s->serial_number,
                'status' => $s->status,
                'warehouse_id' => $s->warehouse_id,
                'warehouse_name' => $s->warehouse?->name ?? 'Unknown',
                'warehouse_code' => $s->warehouse?->code ?? 'WH',
                'inbound_date' => $s->inbound_date,
                'cost_price' => $s->cost_price,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $serials->count(),
            'total_in_stock_all' => $totalInStockAll,
            'selected_warehouse_id' => $selectedWarehouse?->id ?? 'all',
            'selected_warehouse_name' => $selectedWarehouse?->name ?? 'All Warehouses',
            'warehouses_summary' => $warehousesSummary,
            'serials' => $serials,
            'product_name' => $product->name,
            'warehouse_name' => $selectedWarehouse?->name ?? 'All Warehouses',
            'requires_serial_tracking' => (bool) $product->requires_serial_tracking,
        ]);
    }
}
