<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingMethodController extends Controller
{
    /**
     * Display a listing of shipping methods.
     */
    public function index(): View
    {
        $shippingMethods = ShippingMethod::query()
            ->withCount(['deliveries', 'orders'])
            ->ordered()
            ->get();

        $totalMethods = $shippingMethods->count();
        $activeMethods = $shippingMethods->where('is_active', true)->count();
        $freeShippingAvailable = $shippingMethods->whereNotNull('min_order_amount')->count();

        return view('backend.shipping-methods.index', compact(
            'shippingMethods',
            'totalMethods',
            'activeMethods',
            'freeShippingAvailable'
        ));
    }

    /**
     * Store a newly created shipping method in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:shipping_methods,code'],
            'carrier' => ['nullable', 'string', 'max:100'],
            'base_cost' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $method = ShippingMethod::create($validated);

        return redirect()->route('admin.shipping-methods.index')
            ->with('success', "Shipping method '{$method->name}' created successfully.");
    }

    /**
     * Update the specified shipping method in storage.
     */
    public function update(Request $request, ShippingMethod $shippingMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('shipping_methods', 'code')->ignore($shippingMethod->id)],
            'carrier' => ['nullable', 'string', 'max:100'],
            'base_cost' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $shippingMethod->update($validated);

        return redirect()->route('admin.shipping-methods.index')
            ->with('success', "Shipping method '{$shippingMethod->name}' updated successfully.");
    }

    /**
     * Toggle the active status of a shipping method.
     */
    public function toggleActive(ShippingMethod $shippingMethod): RedirectResponse
    {
        $shippingMethod->toggleActive();
        $status = $shippingMethod->isActive() ? 'activated' : 'deactivated';

        return back()->with('success', "Shipping method '{$shippingMethod->name}' has been {$status}.");
    }

    /**
     * Remove the specified shipping method from storage.
     */
    public function destroy(ShippingMethod $shippingMethod): RedirectResponse
    {
        if ($shippingMethod->deliveries()->exists() || $shippingMethod->orders()->exists()) {
            // Soft safety: if in use, deactivate instead of hard delete
            $shippingMethod->update(['is_active' => false]);

            return back()->with('success', "Shipping method '{$shippingMethod->name}' has active history, so it was deactivated instead of deleted.");
        }

        $name = $shippingMethod->name;
        $shippingMethod->delete();

        return redirect()->route('admin.shipping-methods.index')
            ->with('success', "Shipping method '{$name}' deleted successfully.");
    }
}
