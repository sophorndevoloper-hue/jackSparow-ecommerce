<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerGroupDataTable;
use App\Http\Controllers\Controller;
use App\Models\Backend\CustomerGroupPaymentTerm;
use App\Models\CustomerGroup;
use App\Models\FrontendUser;
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
            ->with(['frontendUsers' => fn ($q) => $q->orderBy('name')])
            ->latest('updated_at')
            ->get();

        $allCustomers = FrontendUser::orderBy('name')->get();

        return view('backend.customer-groups.index', compact('groups', 'allCustomers', 'dataTable'));
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

        $group = CustomerGroup::create($validated);

        if (! empty($validated['payment_terms_days']) && (int) $validated['payment_terms_days'] > 0) {
            CustomerGroupPaymentTerm::firstOrCreate(
                [
                    'customer_group_id' => $group->id,
                    'days' => (int) $validated['payment_terms_days'],
                ],
                [
                    'name' => "{$group->name} - Net {$validated['payment_terms_days']} Days",
                    'code' => strtoupper(str_replace([' ', '-'], '_', $group->code)).'_NET_'.$validated['payment_terms_days'],
                    'description' => "Default payment term automatically configured for {$group->name}.",
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }

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

        if (! empty($validated['payment_terms_days']) && (int) $validated['payment_terms_days'] > 0) {
            CustomerGroupPaymentTerm::firstOrCreate(
                [
                    'customer_group_id' => $customerGroup->id,
                    'days' => (int) $validated['payment_terms_days'],
                ],
                [
                    'name' => "{$customerGroup->name} - Net {$validated['payment_terms_days']} Days",
                    'code' => strtoupper(str_replace([' ', '-'], '_', $customerGroup->code)).'_NET_'.$validated['payment_terms_days'],
                    'description' => "Default payment term automatically configured for {$customerGroup->name}.",
                    'is_default' => true,
                    'is_active' => true,
                ]
            );
        }

        return redirect()->route('admin.customer-groups.index')->with('success', "Customer group '{$customerGroup->name}' updated successfully.");
    }

    /**
     * Assign one or multiple customers to the customer group.
     */
    public function assignUsers(Request $request, CustomerGroup $customerGroup): RedirectResponse
    {
        $validated = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'exists:frontend_users,id'],
        ]);

        $count = FrontendUser::whereIn('id', $validated['customer_ids'])
            ->update(['customer_group_id' => $customerGroup->id]);

        return back()->with('success', "{$count} ".str('customer')->plural($count)." successfully added to '{$customerGroup->name}'.");
    }

    /**
     * Remove a customer from the customer group.
     */
    public function removeUser(CustomerGroup $customerGroup, FrontendUser $customer): RedirectResponse
    {
        if ($customer->customer_group_id === $customerGroup->id) {
            $customer->update(['customer_group_id' => null]);
        }

        return back()->with('success', "Customer '{$customer->name}' removed from '{$customerGroup->name}'.");
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
