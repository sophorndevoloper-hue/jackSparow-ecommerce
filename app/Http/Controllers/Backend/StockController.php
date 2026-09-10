<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\StockDataTable;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Make;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display the hardware warehouse stock management dashboard.
     */
    public function index(Request $request, StockDataTable $dataTable): View
    {
        $query = Product::query()->with(['category', 'brand', 'make', 'primaryImage', 'warehouses']);

        $filter = $request->input('filter', 'all');
        $warehouseId = $request->input('warehouse_id');

        if ($filter === 'low') {
            $query->lowStock();
        } elseif ($filter === 'out') {
            $query->outOfStock();
        } elseif ($filter === 'in') {
            $query->where('stock_quantity', '>', 0);
        }

        if ($warehouseId) {
            $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouses.id', $warehouseId);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->input('brand_id'));
        }

        if ($request->filled('make_id')) {
            $query->where('make_id', $request->input('make_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('sku', 'ilike', "%{$search}%");
            });
        }

        $products = $query->latest('updated_at')->paginate(20)->withQueryString();

        // Calculate KPI summary stats
        $totalUnits = Product::sum('stock_quantity');
        $totalValuation = Product::selectRaw('SUM(stock_quantity * price) as val')->value('val') ?? 0;
        $lowStockCount = Product::query()->lowStock()->count();
        $outOfStockCount = Product::query()->outOfStock()->count();
        $totalProducts = Product::count();

        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $makes = Make::active()->orderBy('name')->get();
        $warehouses = Warehouse::active()->orderBy('name')->get();

        return view('backend.stock.index', compact(
            'products',
            'categories',
            'brands',
            'makes',
            'warehouses',
            'warehouseId',
            'totalUnits',
            'totalValuation',
            'lowStockCount',
            'outOfStockCount',
            'totalProducts',
            'filter',
            'dataTable'
        ));
    }

    /**
     * Update stock levels and alert thresholds for a specific product.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
        ]);

        $newQty = $request->integer('stock_quantity');
        $product->update([
            'stock_quantity' => $newQty,
            'low_stock_threshold' => $request->integer('low_stock_threshold'),
        ]);

        $warehouseId = $request->input('warehouse_id');
        if ($warehouseId) {
            $warehouse = Warehouse::find($warehouseId);
            $warehouse?->setProductStock($product, $newQty);
        } else {
            // If no specific warehouse selected, set to default warehouse
            $defaultWarehouse = Warehouse::where('is_default', true)->first() ?? Warehouse::first();
            $defaultWarehouse?->setProductStock($product, $newQty);
        }

        return back()->with('success', "Stock updated for '{$product->name}' (New Quantity: {$product->stock_quantity}).");
    }
}
