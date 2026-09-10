<?php

namespace App\Http\Middleware;

use App\Models\AdminMenu;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBackendPermission
{
    /**
     * Handle an incoming request and control role/permission access before any menu action.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = Auth::guard('backend')->user();

        // 1. Ensure user is authenticated
        if (! $user) {
            return redirect()->route('admin.login');
        }

        // 2. Ensure user is approved
        if (! $user->is_approved) {
            Auth::guard('backend')->logout();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Your account is pending administrator approval.',
            ]);
        }

        // 3. Superadmin and Admin roles have full access to all menus & actions
        if ($user->hasRole('superadmin', 'backend') || $user->hasRole('admin', 'backend') || $user->hasRole('superadmin') || $user->hasRole('admin')) {
            return $next($request);
        }

        // 4. Resolve route and check active status in Menu Setup
        $routeName = $request->route() ? $request->route()->getName() : '';
        $menuSlug = $this->resolveMenuSlugFromRoute($routeName);

        if ($menuSlug && ! AdminMenu::isMenuActive($menuSlug)) {
            abort(403, 'This menu module has been temporarily disabled in Menu Setup.');
        }

        // 5. Determine required permissions (explicit or auto-resolved from route action)
        $requiredPermissions = [];
        if (! empty($permission)) {
            $requiredPermissions = array_map('trim', explode('|', $permission));
        } else {
            $requiredPermissions = $this->resolvePermissionsFromRoute($routeName, $request->method());
        }

        // If no specific permission is required for this route (e.g. profile, dashboard home), allow access
        if (empty($requiredPermissions)) {
            return $next($request);
        }

        // 6. Verify if user has at least one of the required permissions
        $hasAccess = false;
        foreach ($requiredPermissions as $perm) {
            try {
                if ($user->can($perm) || $user->hasPermissionTo($perm, 'backend') || $user->hasPermissionTo($perm)) {
                    $hasAccess = true;
                    break;
                }
            } catch (\Throwable $e) {
                // Ignore if permission record is not registered yet
            }
        }

        if (! $hasAccess) {
            abort(403, 'Unauthorized: You do not have permission to access this menu or perform this action.');
        }

        return $next($request);
    }

    /**
     * Map a route name to its primary menu slug in Menu Setup.
     */
    protected function resolveMenuSlugFromRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        if (str_starts_with($routeName, 'admin.products.images.') || str_starts_with($routeName, 'admin.products.')) {
            return 'products';
        }
        if (str_starts_with($routeName, 'admin.categories.')) {
            return 'categories';
        }
        if (str_starts_with($routeName, 'admin.brands.')) {
            return 'brands';
        }
        if (str_starts_with($routeName, 'admin.stock.adjustments.') || str_starts_with($routeName, 'admin.stock.transfers.') || str_starts_with($routeName, 'admin.stock.') || str_starts_with($routeName, 'admin.serial-numbers.')) {
            return 'stock';
        }
        if (str_starts_with($routeName, 'admin.warehouses.')) {
            return 'warehouses';
        }
        if (str_starts_with($routeName, 'admin.orders.')) {
            return 'orders';
        }
        if (str_starts_with($routeName, 'admin.customers.')) {
            return 'customers';
        }
        if (str_starts_with($routeName, 'admin.suppliers.')) {
            return 'suppliers';
        }
        if (str_starts_with($routeName, 'admin.users.')) {
            return 'users';
        }
        if (str_starts_with($routeName, 'admin.roles.')) {
            return 'roles';
        }
        if (str_starts_with($routeName, 'admin.permissions.')) {
            return 'roles';
        }
        if (str_starts_with($routeName, 'admin.menus.')) {
            return 'menus';
        }
        if (str_starts_with($routeName, 'admin.activity-logs.')) {
            return 'activity-logs';
        }
        if (str_starts_with($routeName, 'admin.cache.')) {
            return 'clear-cache';
        }

        return null;
    }

    /**
     * Auto-resolve required permissions based on route name and HTTP method.
     *
     * @return array<int, string>
     */
    protected function resolvePermissionsFromRoute(?string $routeName, string $method): array
    {
        if (! $routeName) {
            return [];
        }

        // Dashboard, Profile & Clear Cache (no permission required for authenticated admin users)
        if ($routeName === 'admin.dashboard' || str_starts_with($routeName, 'admin.profile.') || str_starts_with($routeName, 'admin.cache.')) {
            return [];
        }

        // Products
        if (in_array($routeName, ['admin.products.index', 'admin.products.show'])) {
            return ['view products', 'create products', 'edit products', 'delete products'];
        }
        if (in_array($routeName, ['admin.products.create', 'admin.products.store'])) {
            return ['create products'];
        }
        if (in_array($routeName, ['admin.products.edit', 'admin.products.update']) || str_starts_with($routeName, 'admin.products.images.')) {
            return ['edit products', 'create products'];
        }
        if ($routeName === 'admin.products.destroy') {
            return ['delete products'];
        }

        // Categories
        if (in_array($routeName, ['admin.categories.index', 'admin.categories.show'])) {
            return ['view categories', 'create categories', 'edit categories', 'delete categories'];
        }
        if (in_array($routeName, ['admin.categories.create', 'admin.categories.store'])) {
            return ['create categories'];
        }
        if (in_array($routeName, ['admin.categories.edit', 'admin.categories.update'])) {
            return ['edit categories'];
        }
        if ($routeName === 'admin.categories.destroy') {
            return ['delete categories'];
        }

        // Brands
        if (in_array($routeName, ['admin.brands.index', 'admin.brands.show'])) {
            return ['view brands', 'create brands', 'edit brands', 'delete brands'];
        }
        if (in_array($routeName, ['admin.brands.create', 'admin.brands.store'])) {
            return ['create brands'];
        }
        if (in_array($routeName, ['admin.brands.edit', 'admin.brands.update'])) {
            return ['edit brands'];
        }
        if ($routeName === 'admin.brands.destroy') {
            return ['delete brands'];
        }

        // Makes
        if (in_array($routeName, ['admin.makes.index', 'admin.makes.show'])) {
            return ['view makes', 'create makes', 'edit makes', 'delete makes'];
        }
        if (in_array($routeName, ['admin.makes.create', 'admin.makes.store'])) {
            return ['create makes'];
        }
        if (in_array($routeName, ['admin.makes.edit', 'admin.makes.update'])) {
            return ['edit makes'];
        }
        if ($routeName === 'admin.makes.destroy') {
            return ['delete makes'];
        }

        // Warehouses
        if (str_starts_with($routeName, 'admin.warehouses.')) {
            if ($routeName === 'admin.warehouses.destroy') {
                return ['delete warehouses'];
            }
            if (in_array($routeName, ['admin.warehouses.create', 'admin.warehouses.store'])) {
                return ['create warehouses'];
            }
            if (in_array($routeName, ['admin.warehouses.edit', 'admin.warehouses.update'])) {
                return ['edit warehouses'];
            }

            return ['view warehouses', 'create warehouses', 'edit warehouses', 'delete warehouses'];
        }

        // Stock
        if (str_starts_with($routeName, 'admin.stock.')) {
            if (str_contains($routeName, 'destroy')) {
                return ['delete products'];
            }
            if (str_contains($routeName, 'create') || str_contains($routeName, 'store')) {
                return ['create products', 'edit products'];
            }
            if (str_contains($routeName, 'edit') || str_contains($routeName, 'update') || str_contains($routeName, 'status')) {
                return ['edit products'];
            }

            return ['view products', 'edit products'];
        }

        // Serial Numbers
        if (str_starts_with($routeName, 'admin.serial-numbers.')) {
            if ($routeName === 'admin.serial-numbers.destroy') {
                return ['delete products', 'edit products'];
            }
            if ($routeName === 'admin.serial-numbers.store') {
                return ['create products', 'edit products'];
            }
            if ($routeName === 'admin.serial-numbers.update') {
                return ['edit products'];
            }

            return ['view products', 'edit products'];
        }

        // Orders
        if (in_array($routeName, ['admin.orders.index', 'admin.orders.show'])) {
            return ['view orders', 'edit orders'];
        }
        if (str_starts_with($routeName, 'admin.orders.')) {
            return ['edit orders'];
        }

        // Customers
        if (str_starts_with($routeName, 'admin.customers.')) {
            return ['view customers', 'edit customers'];
        }

        // Suppliers
        if (str_starts_with($routeName, 'admin.suppliers.')) {
            if ($routeName === 'admin.suppliers.destroy') {
                return ['delete suppliers'];
            }
            if (in_array($routeName, ['admin.suppliers.create', 'admin.suppliers.store'])) {
                return ['create suppliers'];
            }
            if (in_array($routeName, ['admin.suppliers.edit', 'admin.suppliers.update'])) {
                return ['edit suppliers'];
            }

            return ['view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'];
        }

        // Users
        if ($routeName === 'admin.users.toggle-approval') {
            return ['approve users', 'edit users'];
        }
        if ($routeName === 'admin.users.destroy') {
            return ['delete users'];
        }
        if (in_array($routeName, ['admin.users.edit', 'admin.users.update'])) {
            return ['edit users'];
        }
        if (in_array($routeName, ['admin.users.index', 'admin.users.show'])) {
            return ['view users', 'edit users', 'approve users', 'delete users'];
        }

        // Roles & Access Control
        if (str_starts_with($routeName, 'admin.roles.') || str_starts_with($routeName, 'admin.permissions.')) {
            if ($routeName === 'admin.roles.destroy') {
                return ['delete roles'];
            }
            if (in_array($routeName, ['admin.roles.create', 'admin.roles.store'])) {
                return ['create roles'];
            }
            if (in_array($routeName, ['admin.roles.edit', 'admin.roles.update'])) {
                return ['edit roles'];
            }

            return ['view roles', 'create roles', 'edit roles', 'delete roles'];
        }

        // Menu Setup & System Access
        if (str_starts_with($routeName, 'admin.menus.') || str_starts_with($routeName, 'admin.activity-logs.')) {
            if (in_array($routeName, ['admin.menus.index', 'admin.menus.show'])) {
                return ['view menus', 'edit menus', 'view settings', 'edit settings'];
            }

            return ['edit menus', 'edit settings'];
        }

        return [];
    }
}
