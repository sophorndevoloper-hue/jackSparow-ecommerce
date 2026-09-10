<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the admin overview dashboard with live hardware KPIs, or redirect staff to their first permitted section.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth('backend')->user();

        // If user is superadmin or admin, or holds explicit dashboard access, show the dashboard
        $canViewDashboard = ! $user
            || $user->hasRole('superadmin', 'backend')
            || $user->hasRole('admin', 'backend')
            || $user->can('view dashboard');

        if (! $canViewDashboard) {
            // Dynamically redirect user to the first section they have permission for
            if ($user->canAny(['view products', 'create products', 'edit products', 'delete products'])) {
                return redirect()->route('admin.products.index');
            }
            if ($user->canAny(['view warehouses', 'create warehouses', 'edit warehouses', 'delete warehouses'])) {
                return redirect()->route('admin.warehouses.index');
            }
            if ($user->canAny(['view categories', 'create categories', 'edit categories', 'delete categories'])) {
                return redirect()->route('admin.categories.index');
            }
            if ($user->canAny(['view brands', 'create brands', 'edit brands', 'delete brands'])) {
                return redirect()->route('admin.brands.index');
            }
            if ($user->canAny(['view orders', 'edit orders'])) {
                return redirect()->route('admin.orders.index');
            }
            if ($user->canAny(['view customers', 'edit customers'])) {
                return redirect()->route('admin.customers.index');
            }
            if ($user->canAny(['view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'])) {
                return redirect()->route('admin.suppliers.index');
            }
            if ($user->canAny(['view users', 'edit users', 'approve users', 'delete users'])) {
                return redirect()->route('admin.users.index');
            }
            if ($user->canAny(['view roles', 'create roles', 'edit roles', 'delete roles'])) {
                return redirect()->route('admin.roles.index');
            }
            if ($user->canAny(['view settings', 'edit settings'])) {
                return redirect()->route('admin.menus.index');
            }

            // Fallback to their profile page
            return redirect()->route('admin.profile.edit');
        }
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->count();
        if ($totalCustomers === 0) {
            $totalCustomers = User::count();
        }

        $lowStockCount = Product::query()->lowStock()->count();
        $outOfStockCount = Product::query()->outOfStock()->count();

        $recentOrders = Order::query()
            ->with(['user'])
            ->latest()
            ->take(6)
            ->get();

        $lowStockProducts = Product::query()
            ->where(function ($q) {
                $q->lowStock()->orWhere->outOfStock();
            })
            ->with(['category', 'brand'])
            ->take(5)
            ->get();

        return view('backend.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'totalProducts',
            'totalCustomers',
            'lowStockCount',
            'outOfStockCount',
            'recentOrders',
            'lowStockProducts',
        ));
    }
}
