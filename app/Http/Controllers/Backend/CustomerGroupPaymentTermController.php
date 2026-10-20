<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\CustomerGroupPaymentTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerGroupPaymentTermController extends Controller
{
    /**
     * Display listing of customer group payment terms.
     */
    public function index(Request $request): View
    {
        $query = CustomerGroupPaymentTerm::with('customerGroup');

        // Filter: Search by name, code, or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter: Customer Group
        if ($groupId = $request->input('customer_group_id')) {
            if ($groupId === 'global') {
                $query->whereNull('customer_group_id');
            } else {
                $query->where('customer_group_id', $groupId);
            }
        }

        // Filter: Status
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $paymentTerms = $query->orderByRaw('customer_group_id IS NULL DESC')
            ->orderBy('days')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $customerGroups = CustomerGroup::orderBy('name')->get();

        // Summary KPI Metrics
        $totalTerms = CustomerGroupPaymentTerm::count();
        $activeTerms = CustomerGroupPaymentTerm::where('is_active', true)->count();
        $groupSpecificTerms = CustomerGroupPaymentTerm::whereNotNull('customer_group_id')->count();
        $discountTerms = CustomerGroupPaymentTerm::where('early_payment_discount_percentage', '>', 0)->count();

        return view('backend.customer-group-payment-terms.index', compact(
            'paymentTerms',
            'customerGroups',
            'totalTerms',
            'activeTerms',
            'groupSpecificTerms',
            'discountTerms'
        ));
    }

    /**
     * Store a newly created customer group payment term.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'days' => ['required', 'integer', 'min:0', 'max:365'],
            'description' => ['nullable', 'string', 'max:1000'],
            'early_payment_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'early_payment_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:365',
                'lte:days',
                Rule::requiredIf(fn () => (float) $request->input('early_payment_discount_percentage') > 0),
            ],
            'late_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'require_deposit' => ['nullable', 'boolean'],
            'deposit_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                Rule::requiredIf(fn () => $request->boolean('require_deposit')),
            ],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'deposit_percentage.required_if' => 'Please provide a deposit percentage when upfront deposit is required.',
            'early_payment_days.required_if' => 'Please specify the number of days eligible for the early settlement discount.',
            'early_payment_days.lte' => 'Early payment discount days cannot exceed the total payment settlement days.',
        ]);

        $validated['code'] = strtoupper(str_replace(' ', '_', trim($validated['code'])));
        $validated['require_deposit'] = $request->boolean('require_deposit');
        $validated['deposit_percentage'] = $validated['require_deposit'] ? ($validated['deposit_percentage'] ?? 0) : 0;
        $validated['early_payment_discount_percentage'] = $validated['early_payment_discount_percentage'] ?? 0;
        $validated['early_payment_days'] = $validated['early_payment_days'] ?? 0;
        $validated['late_fee_percentage'] = $validated['late_fee_percentage'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        // If marked as default for a specific group, remove previous default
        if ($validated['is_default'] && ! empty($validated['customer_group_id'])) {
            CustomerGroupPaymentTerm::where('customer_group_id', $validated['customer_group_id'])
                ->update(['is_default' => false]);
        }

        $term = CustomerGroupPaymentTerm::create($validated);

        return redirect()->route('admin.customer-group-payment-terms.index')
            ->with('success', "Payment term '{$term->name}' created successfully.");
    }

    /**
     * Update the specified payment term.
     */
    public function update(Request $request, CustomerGroupPaymentTerm $customerGroupPaymentTerm): RedirectResponse
    {
        $validated = $request->validate([
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'days' => ['required', 'integer', 'min:0', 'max:365'],
            'description' => ['nullable', 'string', 'max:1000'],
            'early_payment_discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'early_payment_days' => [
                'nullable',
                'integer',
                'min:0',
                'max:365',
                'lte:days',
                Rule::requiredIf(fn () => (float) $request->input('early_payment_discount_percentage') > 0),
            ],
            'late_fee_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'require_deposit' => ['nullable', 'boolean'],
            'deposit_percentage' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                Rule::requiredIf(fn () => $request->boolean('require_deposit')),
            ],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'deposit_percentage.required_if' => 'Please provide a deposit percentage when upfront deposit is required.',
            'early_payment_days.required_if' => 'Please specify the number of days eligible for the early settlement discount.',
            'early_payment_days.lte' => 'Early payment discount days cannot exceed the total payment settlement days.',
        ]);

        $validated['code'] = strtoupper(str_replace(' ', '_', trim($validated['code'])));
        $validated['require_deposit'] = $request->boolean('require_deposit');
        $validated['deposit_percentage'] = $validated['require_deposit'] ? ($validated['deposit_percentage'] ?? 0) : 0;
        $validated['early_payment_discount_percentage'] = $validated['early_payment_discount_percentage'] ?? 0;
        $validated['early_payment_days'] = $validated['early_payment_days'] ?? 0;
        $validated['late_fee_percentage'] = $validated['late_fee_percentage'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');
        $validated['is_active'] = $request->boolean('is_active', true);

        // If marked as default for a specific group, unset other defaults
        if ($validated['is_default'] && ! empty($validated['customer_group_id'])) {
            CustomerGroupPaymentTerm::where('customer_group_id', $validated['customer_group_id'])
                ->where('id', '!=', $customerGroupPaymentTerm->id)
                ->update(['is_default' => false]);
        }

        $customerGroupPaymentTerm->update($validated);

        return redirect()->route('admin.customer-group-payment-terms.index')
            ->with('success', "Payment term '{$customerGroupPaymentTerm->name}' updated successfully.");
    }

    /**
     * Remove the specified payment term.
     */
    public function destroy(CustomerGroupPaymentTerm $customerGroupPaymentTerm): RedirectResponse
    {
        $name = $customerGroupPaymentTerm->name;
        $customerGroupPaymentTerm->delete();

        return redirect()->route('admin.customer-group-payment-terms.index')
            ->with('success', "Payment term '{$name}' deleted successfully.");
    }

    /**
     * Toggle the active status of a payment term.
     */
    public function toggleStatus(CustomerGroupPaymentTerm $customerGroupPaymentTerm): RedirectResponse
    {
        $customerGroupPaymentTerm->update([
            'is_active' => ! $customerGroupPaymentTerm->is_active,
        ]);

        $statusText = $customerGroupPaymentTerm->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.customer-group-payment-terms.index')
            ->with('success', "Payment term '{$customerGroupPaymentTerm->name}' has been {$statusText}.");
    }
}
