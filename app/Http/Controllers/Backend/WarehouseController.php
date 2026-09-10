<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\WarehouseDataTable;
use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    /**
     * Display a listing of all storage warehouses.
     */
    public function index(Request $request, WarehouseDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $warehouses = Warehouse::query()
            ->withCount('products')
            ->latest('updated_at')
            ->paginate(15);

        return view('backend.warehouses.index', compact('warehouses', 'dataTable'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create(): View
    {
        return view('backend.warehouses.create');
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_default'] = $request->boolean('is_default', false);

        if ($validated['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        Warehouse::create($validated);

        return redirect()->route('admin.warehouses.index')->with('success', "Warehouse '{$validated['name']}' created successfully.");
    }

    /**
     * Display the specified warehouse details and current stock inventory.
     */
    public function show(Warehouse $warehouse): View
    {
        $warehouse->load(['products' => function ($query) {
            $query->with(['category', 'brand', 'primaryImage'])->orderBy('name');
        }]);

        $totalUnits = (int) $warehouse->products->sum('pivot.quantity');
        $totalValuation = $warehouse->products->sum(function ($p) {
            return (float) $p->pivot->quantity * (float) $p->price;
        });

        return view('backend.warehouses.show', compact('warehouse', 'totalUnits', 'totalValuation'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function edit(Warehouse $warehouse): View
    {
        return view('backend.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        if ($validated['is_default']) {
            Warehouse::where('id', '!=', $warehouse->id)->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.index')->with('success', "Warehouse '{$warehouse->name}' updated successfully.");
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->is_default) {
            return back()->with('error', 'The default primary warehouse cannot be deleted.');
        }

        if ($warehouse->total_stock > 0) {
            return back()->with('error', "Cannot delete warehouse '{$warehouse->name}' while it contains {$warehouse->total_stock} stock units. Please transfer stock first.");
        }

        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }
}
