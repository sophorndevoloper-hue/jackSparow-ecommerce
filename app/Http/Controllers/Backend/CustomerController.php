<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerDataTable;
use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\FrontendUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of registered customers.
     */
    public function index(Request $request, CustomerDataTable $dataTable): View
    {
        $query = FrontendUser::query()
            ->withCount('orders')
            ->with(['orders' => fn ($q) => $q->latest()->take(3), 'customerGroup']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($request->input('status') === 'active') {
                $query->where('is_blocked', false);
            }
        }

        $customers = $query->latest('updated_at')->paginate(15)->withQueryString();

        // Customer Metrics
        $totalCount = FrontendUser::count();
        $activeBuyersCount = FrontendUser::where('orders_count', '>', 0)->count();
        $totalRevenue = (float) FrontendUser::sum('total_spent');
        $totalOrdersCount = (int) FrontendUser::sum('orders_count');
        $blockedCount = FrontendUser::where('is_blocked', true)->count();

        // Customer Groups for modal selects
        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        return view('backend.customers.index', compact(
            'customers',
            'totalCount',
            'activeBuyersCount',
            'totalRevenue',
            'totalOrdersCount',
            'blockedCount',
            'customerGroups',
            'dataTable'
        ));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        return view('backend.customers.create', compact('customerGroups'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:frontend_users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'customer_type' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_vat_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['country'] = $validated['country'] ?? 'United States';
        $validated['customer_type'] = $validated['customer_type'] ?? 'simple';
        $validated['email_verified_at'] = now();

        $customer = FrontendUser::create($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer '{$customer->name}' created successfully.");
    }

    /**
     * Display detailed profile and order history of a customer.
     */
    public function show(FrontendUser $customer): View
    {
        $customer->load(['orders.items.product', 'customerGroup']);

        return view('backend.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(FrontendUser $customer): View
    {
        $customerGroups = CustomerGroup::where('is_active', true)->orderBy('name')->get();

        return view('backend.customers.edit', compact('customer', 'customerGroups'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, FrontendUser $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('frontend_users', 'email')->ignore($customer->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
            'customer_type' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'tax_vat_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $customer->update($validated);

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer '{$customer->name}' updated successfully.");
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(FrontendUser $customer): RedirectResponse
    {
        $name = $customer->name;
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer '{$name}' deleted successfully.");
    }

    /**
     * Toggle customer tier between Simple and Special.
     */
    public function toggleSpecial(FrontendUser $customer): RedirectResponse
    {
        $oldType = $customer->customer_type;
        $newType = $oldType === 'special' ? 'simple' : 'special';

        $customer->customer_type = $newType;
        $customer->save();

        $label = $newType === 'special' ? 'Special Customer (VIP)' : 'Simple Customer';

        return back()->with('success', "Customer '{$customer->name}' status updated to {$label}.");
    }

    /**
     * Toggle the blocked/unblocked status for the specified customer.
     */
    public function toggleBlock(FrontendUser $customer): RedirectResponse
    {
        $isBlocked = $customer->toggleBlock();
        $statusLabel = $isBlocked ? 'blocked' : 'unblocked';

        return back()->with('success', "Customer '{$customer->name}' has been {$statusLabel} successfully.");
    }

    /**
     * Bulk assign selected customers to a customer group.
     */
    public function bulkAssignGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'exists:frontend_users,id'],
            'customer_group_id' => ['nullable', 'exists:customer_groups,id'],
        ]);

        $groupId = $validated['customer_group_id'] ?? null;
        $count = FrontendUser::whereIn('id', $validated['customer_ids'])
            ->update(['customer_group_id' => $groupId]);

        if ($groupId) {
            $group = CustomerGroup::find($groupId);
            $message = "{$count} ".str('customer')->plural($count)." successfully assigned to '{$group->name}'.";
        } else {
            $message = "{$count} ".str('customer')->plural($count).' removed from customer groups.';
        }

        return back()->with('success', $message);
    }
}
