<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerGroupDataTable;
use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    /**
     * Display listing of customer groups.
     */
    public function index(Request $request, CustomerGroupDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $groups = CustomerGroup::withCount(['frontendUsers', 'priceTiers'])
            ->latest('updated_at')
            ->get();

        return view('backend.customer-groups.index', compact('groups', 'dataTable'));
    }

    /**
     * Store newly created customer group.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:customer_groups,code'],
            'description' => ['nullable', 'string'],
            'default_discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'tax_exempt' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = str()->slug($validated['name']);
        $validated['tax_exempt'] = $request->boolean('tax_exempt');
        $validated['is_active'] = $request->boolean('is_active', true);

        CustomerGroup::create($validated);

        return redirect()->route('admin.customer-groups.index')->with('success', "Customer group '{$validated['name']}' created successfully.");
    }

    /**
     * Update customer group.
     */
    public function update(Request $request, CustomerGroup $customerGroup): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50', 'unique:customer_groups,code,'.$customerGroup->id],
            'description' => ['nullable', 'string'],
            'default_discount_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0'],
            'tax_exempt' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = str()->slug($validated['name']);
        $validated['tax_exempt'] = $request->boolean('tax_exempt');
        $validated['is_active'] = $request->boolean('is_active');

        $customerGroup->update($validated);

        return redirect()->route('admin.customer-groups.index')->with('success', "Customer group '{$customerGroup->name}' updated successfully.");
    }

    /**
     * Delete customer group.
     */
    public function destroy(CustomerGroup $customerGroup): RedirectResponse
    {
        if ($customerGroup->frontendUsers()->exists()) {
            return back()->with('error', 'Cannot delete customer group that has assigned users.');
        }

        $customerGroup->delete();

        return redirect()->route('admin.customer-groups.index')->with('success', 'Customer group deleted successfully.');
    }
}
