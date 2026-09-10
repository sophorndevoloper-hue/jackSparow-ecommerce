<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerDataTable;
use App\Http\Controllers\Controller;
use App\Models\FrontendUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of registered customers with Simple / Special tier filters.
     */
    public function index(Request $request, CustomerDataTable $dataTable): View
    {
        $query = FrontendUser::query()
            ->withCount('orders')
            ->with(['orders' => fn ($q) => $q->latest()->take(3)]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%")
                    ->orWhere('city', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type') && in_array($request->input('type'), ['simple', 'special'])) {
            $query->where('customer_type', $request->input('type'));
        }

        $customers = $query->latest('updated_at')->paginate(15)->withQueryString();

        // Metrics for Customer Tiers
        $totalCount = FrontendUser::count();
        $simpleCount = FrontendUser::where('customer_type', 'simple')->count();
        $specialCount = FrontendUser::where('customer_type', 'special')->count();
        $totalRevenue = FrontendUser::sum('total_spent');

        return view('backend.customers.index', compact(
            'customers',
            'totalCount',
            'simpleCount',
            'specialCount',
            'totalRevenue',
            'dataTable'
        ));
    }

    /**
     * Display detailed profile and order history of a customer.
     */
    public function show(FrontendUser $customer): View
    {
        $customer->load(['orders.items.product']);

        return view('backend.customers.show', compact('customer'));
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
}
