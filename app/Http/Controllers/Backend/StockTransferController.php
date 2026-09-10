<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockTransferDataTable;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $products = Product::orderBy('name')->get();
        $selectedFromId = $request->input('from_warehouse_id', $warehouses->first()?->id);

        return view('backend.stock.transfers.create', compact('warehouses', 'products', 'selectedFromId'));
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
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $fromWarehouse = Warehouse::findOrFail($request->input('from_warehouse_id'));
        $toWarehouse = Warehouse::findOrFail($request->input('to_warehouse_id'));
        $status = $request->input('status');
        $userId = auth('backend')->id() ?? auth()->id();

        // Validate source warehouse has sufficient stock
        foreach ($request->input('products') as $item) {
            $product = Product::findOrFail($item['product_id']);
            $currentStock = $fromWarehouse->getProductStock($product);
            $qty = (int) $item['quantity'];

            if ($currentStock < $qty) {
                return back()->withInput()->with('error', "Insufficient stock in source warehouse '{$fromWarehouse->name}' for product '{$product->name}' (Available: {$currentStock}, Requested: {$qty}).");
            }
        }

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
                $qty = (int) $item['quantity'];

                $transfer->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                ]);

                // If completed or in_transit, deduct from source warehouse
                if ($status === 'completed' || $status === 'in_transit') {
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, max(0, $fromStock - $qty));
                }

                // If completed, add immediately to destination warehouse
                if ($status === 'completed') {
                    $toStock = $toWarehouse->getProductStock($product);
                    $toWarehouse->setProductStock($product, $toStock + $qty);
                }

                $product->syncTotalStock();
            }
        });

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

                if ($oldStatus === 'pending' && $newStatus === 'in_transit') {
                    // Deduct from source
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, max(0, $fromStock - $qty));
                } elseif ($oldStatus === 'in_transit' && $newStatus === 'completed') {
                    // Add to destination
                    $toStock = $toWarehouse->getProductStock($product);
                    $toWarehouse->setProductStock($product, $toStock + $qty);
                } elseif ($oldStatus === 'pending' && $newStatus === 'completed') {
                    // Deduct from source and add to destination
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $toStock = $toWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, max(0, $fromStock - $qty));
                    $toWarehouse->setProductStock($product, $toStock + $qty);
                } elseif ($oldStatus === 'in_transit' && $newStatus === 'cancelled') {
                    // Return stock to source
                    $fromStock = $fromWarehouse->getProductStock($product);
                    $fromWarehouse->setProductStock($product, $fromStock + $qty);
                }

                $product->syncTotalStock();
            }

            $transfer->update(['status' => $newStatus]);
        });

        return back()->with('success', "Stock transfer status updated to '{$newStatus}'.");
    }
}
