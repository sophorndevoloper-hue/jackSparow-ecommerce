<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockTransferDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockTransferController extends Controller
{
    /**
     * Display a listing of warehouse stock transfers.
     */
    public function index(Request $request, StockTransferDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = StockTransfer::query()->with(['fromWarehouse', 'toWarehouse', 'user', 'items.product']);

        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->input('from_warehouse_id'));
        }

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->input('to_warehouse_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $transfers = $query->latest('updated_at')->paginate(15)->withQueryString();

        $warehouses = Warehouse::orderBy('name')->get();

        return view('backend.stock.transfers.index', compact('transfers', 'warehouses', 'dataTable'));
    }

    /**
     * Show the form for creating a new stock transfer.
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
        $selectedFromId = $request->input('from_warehouse_id', $warehouses->first()?->id);
        $selectedToId = $request->input('to_warehouse_id', $warehouses->count() > 1 ? $warehouses->skip(1)->first()?->id : null);

        return view('backend.stock.transfers.create', compact('warehouses', 'products', 'productsData', 'selectedFromId', 'selectedToId'));
    }

    /**
     * Fetch available serial numbers in stock for a product in the source warehouse.
     */
    public function getProductSerials(Request $request): JsonResponse
    {
        $productId = $request->input('product_id');
        $warehouseId = $request->input('warehouse_id');

        if (! $productId || ! $warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID and Warehouse ID are required.',
                'serials' => [],
                'count' => 0,
            ], 422);
        }

        $product = Product::find($productId);
        $warehouse = Warehouse::find($warehouseId);

        if (! $product || ! $warehouse) {
            return response()->json([
                'success' => false,
                'message' => 'Product or Warehouse not found.',
                'serials' => [],
                'count' => 0,
            ], 404);
        }

        $serials = SerialNumber::query()
            ->where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->where('status', SerialNumber::STATUS_IN_STOCK)
            ->orderBy('serial_number')
            ->get(['id', 'serial_number', 'status', 'warehouse_id', 'inbound_date', 'cost_price']);

        $currentWarehouseStock = $warehouse->getProductStock($product);

        return response()->json([
            'success' => true,
            'count' => $serials->count(),
            'warehouse_stock' => $currentWarehouseStock,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'requires_serial_tracking' => (bool) $product->requires_serial_tracking,
            'warehouse_name' => $warehouse->name,
            'warehouse_code' => $warehouse->code,
            'serials' => $serials,
        ]);
    }

    /**
     * Store a newly created stock transfer and process stock movements.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'from_warehouse_id' => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id'],
            'status' => ['required', 'in:pending,in_transit,completed'],
            'transfer_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['nullable', 'integer'],
            'products.*.serials' => ['nullable', 'string'],
        ]);

        $fromWarehouse = Warehouse::findOrFail($request->input('from_warehouse_id'));
        $toWarehouse = Warehouse::findOrFail($request->input('to_warehouse_id'));
        $status = $request->input('status');
        $userId = auth('backend')->id() ?? auth()->id();

        try {
            DB::transaction(function () use ($request, $fromWarehouse, $toWarehouse, $status, $userId) {
                $transfer = StockTransfer::create([
                    'reference_number' => StockTransfer::generateReference(),
                    'from_warehouse_id' => $fromWarehouse->id,
                    'to_warehouse_id' => $toWarehouse->id,
                    'user_id' => $userId,
                    'status' => $status,
                    'transfer_date' => $request->input('transfer_date', now()),
                    'notes' => $request->input('notes'),
                ]);

                foreach ($request->input('products') as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $currentStock = $fromWarehouse->getProductStock($product);
                    $rawSerials = $item['serials'] ?? '';

                    // Clean and parse serial numbers if provided
                    $cleanedSerials = [];
                    if (! empty($rawSerials)) {
                        $rawList = preg_split('/[\r\n,]+/', (string) $rawSerials);
                        foreach ($rawList as $raw) {
                            $serial = strtoupper(trim((string) $raw));
                            if ($serial !== '') {
                                $cleanedSerials[] = $serial;
                            }
                        }
                        $cleanedSerials = array_values(array_unique($cleanedSerials));
                    }

                    // Determine transfer quantity
                    if (! empty($cleanedSerials)) {
                        $qty = count($cleanedSerials);
                    } else {
                        $qty = isset($item['quantity']) ? (int) $item['quantity'] : 0;
                    }

                    if ($qty <= 0) {
                        throw new InvalidArgumentException("Quantity must be at least 1 for product '{$product->name}'.");
                    }

                    if ($currentStock < $qty) {
                        throw new InvalidArgumentException("Insufficient stock in source warehouse '{$fromWarehouse->name}' for product '{$product->name}' (Available: {$currentStock}, Requested: {$qty}).");
                    }

                    // If serial numbers are provided, verify they are in stock in the source warehouse
                    if (! empty($cleanedSerials)) {
                        $inStockSerials = SerialNumber::where('product_id', $product->id)
                            ->where('warehouse_id', $fromWarehouse->id)
                            ->where('status', SerialNumber::STATUS_IN_STOCK)
                            ->whereIn('serial_number', $cleanedSerials)
                            ->pluck('serial_number')
                            ->toArray();

                        $missing = array_diff($cleanedSerials, $inStockSerials);
                        if (! empty($missing)) {
                            throw new InvalidArgumentException("The following serial number(s) were not found in stock in {$fromWarehouse->name} for '{$product->name}': ".implode(', ', $missing));
                        }
                    }

                    // Create StockTransferItem with serial_numbers recorded
                    $transfer->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'serial_numbers' => ! empty($cleanedSerials) ? $cleanedSerials : null,
                    ]);

                    // Process physical warehouse stock and serial movements based on status
                    if ($status === 'completed') {
                        // Deduct from source warehouse
                        $fromWarehouse->setProductStock($product, max(0, $currentStock - $qty));
                        // Add to destination warehouse
                        $toStock = $toWarehouse->getProductStock($product);
                        $toWarehouse->setProductStock($product, $toStock + $qty);

                        // Move serial numbers to destination warehouse
                        if (! empty($cleanedSerials)) {
                            SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $fromWarehouse->id)
                                ->whereIn('serial_number', $cleanedSerials)
                                ->update([
                                    'warehouse_id' => $toWarehouse->id,
                                    'status' => SerialNumber::STATUS_IN_STOCK,
                                    'notes' => "Transferred from {$fromWarehouse->name} to {$toWarehouse->name} via transfer {$transfer->reference_number}",
                                ]);
                        }
                    } elseif ($status === 'in_transit') {
                        // Deduct from source warehouse immediately
                        $fromWarehouse->setProductStock($product, max(0, $currentStock - $qty));

                        // Tag serial numbers with in-transit note
                        if (! empty($cleanedSerials)) {
                            SerialNumber::where('product_id', $product->id)
                                ->where('warehouse_id', $fromWarehouse->id)
                                ->whereIn('serial_number', $cleanedSerials)
                                ->update([
                                    'notes' => "In transit to {$toWarehouse->name} via transfer {$transfer->reference_number}",
                                ]);
                        }
                    }

                    $product->syncTotalStock();
                }
            });
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.stock.transfers.index')
            ->with('success', "Stock transfer '{$fromWarehouse->name}' &rarr; '{$toWarehouse->name}' processed successfully.");
    }

    /**
     * Display details of a specific stock transfer.
     */
    public function show(StockTransfer $transfer): View
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'user', 'items.product.category']);

        return view('backend.stock.transfers.show', compact('transfer'));
    }

    /**
     * Update the lifecycle status of an existing stock transfer.
     */
    public function updateStatus(Request $request, StockTransfer $transfer): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:in_transit,completed,cancelled'],
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $transfer->status;

        if ($oldStatus === $newStatus) {
            return back();
        }

        if ($oldStatus === 'completed' || $oldStatus === 'cancelled') {
            return back()->with('error', "Cannot change status of an already {$oldStatus} transfer.");
        }

        DB::transaction(function () use ($transfer, $oldStatus, $newStatus) {
            $fromWarehouse = $transfer->fromWarehouse;
            $toWarehouse = $transfer->toWarehouse;

            foreach ($transfer->items as $item) {
                $product = $item->product;
                $qty = $item->quantity;
                $serials = $item->serial_numbers ?? [];

                if ($oldStatus === 'pending' && $newStatus === 'in_transit') {
                    // Deduct from source
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, max(0, $fromStock - $qty));
                } elseif ($oldStatus === 'in_transit' && $newStatus === 'completed') {
                    // Add to destination
                    $toStock = $toWarehouse->getProductStock($product);
                    $toWarehouse->setProductStock($product, $toStock + $qty);

                    // Move serials to destination warehouse
                    if (! empty($serials)) {
                        SerialNumber::where('product_id', $product->id)
                            ->where('warehouse_id', $fromWarehouse->id)
                            ->whereIn('serial_number', $serials)
                            ->update([
                                'warehouse_id' => $toWarehouse->id,
                                'status' => SerialNumber::STATUS_IN_STOCK,
                                'notes' => "Arrived at {$toWarehouse->name} via transfer {$transfer->reference_number}",
                            ]);
                    }
                } elseif ($oldStatus === 'pending' && $newStatus === 'completed') {
                    // Deduct from source and add to destination
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $toStock = $toWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, max(0, $fromStock - $qty));
                    $toWarehouse->setProductStock($product, $toStock + $qty);

                    // Move serials to destination warehouse
                    if (! empty($serials)) {
                        SerialNumber::where('product_id', $product->id)
                            ->where('warehouse_id', $fromWarehouse->id)
                            ->whereIn('serial_number', $serials)
                            ->update([
                                'warehouse_id' => $toWarehouse->id,
                                'status' => SerialNumber::STATUS_IN_STOCK,
                                'notes' => "Transferred from {$fromWarehouse->name} to {$toWarehouse->name} via transfer {$transfer->reference_number}",
                            ]);
                    }
                } elseif ($oldStatus === 'in_transit' && $newStatus === 'cancelled') {
                    // Return stock to source
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, $fromStock + $qty);

                    // Revert notes on serials
                    if (! empty($serials)) {
                        SerialNumber::where('product_id', $product->id)
                            ->where('warehouse_id', $fromWarehouse->id)
                            ->whereIn('serial_number', $serials)
                            ->update([
                                'notes' => "Transfer {$transfer->reference_number} cancelled; retained at {$fromWarehouse->name}",
                            ]);
                    }
                }

                $product->syncTotalStock();
            }

            $transfer->update(['status' => $newStatus]);
        });

        return back()->with('success', "Stock transfer status updated to '{$newStatus}'.");
    }
}
