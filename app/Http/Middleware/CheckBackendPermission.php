<?php

namespace App\Http\Middleware;

use App\Models\Backend\AdminMenu;
use App\Models\Backend\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBackendPermission
{
    /**
     * Handle an incoming request.
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

        // 4. Resolve route and check active status in Menu Setup dynamically
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
     * Map a route name dynamically to its primary menu slug in Menu Setup.
     * Fully dynamic: automatically detects newly added menus without code changes.
     */
    protected function resolveMenuSlugFromRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        // 1. Direct dynamic match from AdminMenu routes and actions
        $dynamicMap = AdminMenu::getDynamicRouteToMenuSlugMap();
        if (isset($dynamicMap[$routeName])) {
            return $dynamicMap[$routeName];
        }

        // 2. Dynamic resource prefix matching (e.g. admin.new_module.* -> new_module)
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            $prefix = $parts[0].'.'.$parts[1].'.';
            foreach ($dynamicMap as $rName => $slug) {
                if (str_starts_with($rName, $prefix)) {
                    return $slug;
                }
            }
        }

        // 3. Fallback for nested sub-modules
        if (str_starts_with($routeName, 'admin.products.images.')) {
            return 'products';
        }
        if (str_starts_with($routeName, 'admin.stock.adjustments.') || str_starts_with($routeName, 'admin.stock.transfers.') || str_starts_with($routeName, 'admin.serial-numbers.')) {
            return 'stock';
        }
        if (str_starts_with($routeName, 'admin.website-settings.') || str_starts_with($routeName, 'admin.storefront-menus.') || str_starts_with($routeName, 'admin.website-products.')) {
            return 'website_setup';
        }

        return null;
    }

    /**
     * Auto-resolve required permissions dynamically based on Menu Setup, Spatie DB records, and REST conventions.
     * Fully dynamic: automatically discovers required permissions for newly added menus without code changes.
     *
     * @return array<int, string>
     */
    protected function resolvePermissionsFromRoute(?string $routeName, string $method = 'GET'): array
    {
        if (! $routeName) {
            return [];
        }

        // 1. Free administrative routes (no permission check required)
        if ($routeName === 'admin.dashboard' || str_starts_with($routeName, 'admin.profile.') || str_starts_with($routeName, 'admin.cache.')) {
            return [];
        }

        // 2. Check explicit fallback rules for compound permissions & non-standard action names
        $fallback = $this->resolveFallbackPermissions($routeName);
        if (! empty($fallback)) {
            return $fallback;
        }

        // 3. Dynamic lookup from AdminMenu actions and view_permissions
        $dynamicPermMap = AdminMenu::getDynamicRoutePermissionMap();
        if (isset($dynamicPermMap[$routeName]) && ! empty($dynamicPermMap[$routeName])) {
            return $dynamicPermMap[$routeName];
        }

        // 4. Dynamic lookup from database Permission action_route column
        $dbActionPerms = Permission::where('guard_name', 'backend')
            ->where('action_route', $routeName)
            ->pluck('name')
            ->toArray();
        if (! empty($dbActionPerms)) {
            return $dbActionPerms;
        }

        // 5. Dynamic standard REST convention inference for newly added menus
        // E.g., 'admin.coupons.store' -> extracts 'coupons' and 'store' -> pairs with 'create coupons'
        $parts = explode('.', $routeName);
        if (count($parts) >= 3 && $parts[0] === 'admin') {
            $resource = str_replace(['-', '_'], ' ', $parts[1]);
            $action = end($parts);

            $candidatePerms = match ($action) {
                'create', 'store' => ["create {$resource}"],
                'edit', 'update' => ["edit {$resource}"],
                'destroy' => ["delete {$resource}"],
                'index', 'show' => ["view {$resource}"],
                default => [],
            };

            if (! empty($candidatePerms)) {
                $matchingPerms = Permission::where('guard_name', 'backend')
                    ->whereIn('name', $candidatePerms)
                    ->pluck('name')
                    ->toArray();

                if (! empty($matchingPerms)) {
                    return $matchingPerms;
                }
            }
        }

        return [];
    }

    /**
     * Fallback rules for special, compound, or non-standard route names.
     *
     * @return array<int, string>
     */
    protected function resolveFallbackPermissions(string $routeName): array
    {
        // 1. Non-standard custom routes and audit logs
        if ($routeName === 'admin.users.toggle-approval') {
            return ['approve users', 'edit users', 'manage users'];
        }
        if (in_array($routeName, ['admin.users.index', 'admin.users.show'])) {
            return ['view users', 'edit users', 'approve users', 'delete users', 'manage users'];
        }
        if (str_starts_with($routeName, 'admin.users.')) {
            if ($routeName === 'admin.users.destroy') {
                return ['delete users', 'manage users'];
            }

            return ['edit users', 'manage users'];
        }

        if ($routeName === 'admin.customers.toggle-block') {
            return ['block customers', 'edit customers'];
        }
        if ($routeName === 'admin.customers.toggle-special') {
            return ['edit customers'];
        }

        if (str_starts_with($routeName, 'admin.roles.') || str_starts_with($routeName, 'admin.permissions.')) {
            if (in_array($routeName, ['admin.roles.destroy', 'admin.permissions.destroy'])) {
                return ['delete roles', 'delete permissions', 'manage roles', 'manage permissions'];
            }
            if (in_array($routeName, ['admin.roles.create', 'admin.roles.store', 'admin.permissions.create', 'admin.permissions.store'])) {
                return ['create roles', 'create permissions', 'manage roles', 'manage permissions'];
            }
            if (in_array($routeName, ['admin.roles.edit', 'admin.roles.update'])) {
                return ['edit roles', 'manage roles'];
            }

            return ['view roles', 'view permissions', 'create roles', 'edit roles', 'delete roles', 'manage roles', 'manage permissions'];
        }

        if (str_starts_with($routeName, 'admin.menus.') || str_starts_with($routeName, 'admin.activity-logs.')) {
            if (in_array($routeName, ['admin.menus.index', 'admin.menus.show'])) {
                return ['view menus', 'edit menus', 'view settings', 'edit settings'];
            }

            return ['edit menus', 'edit settings'];
        }

        if (str_starts_with($routeName, 'admin.user-history.')) {
            if ($routeName === 'admin.user-history.clear') {
                return ['delete activity logs', 'manage settings', 'manage users'];
            }

            return ['view user history', 'view users', 'view settings', 'manage users'];
        }

        if (str_starts_with($routeName, 'admin.products.')) {
            if (in_array($routeName, ['admin.products.import', 'admin.products.import-preview', 'admin.products.import.template'])) {
                return ['create products', 'edit products'];
            }
        }

        if (str_starts_with($routeName, 'admin.stock.') || str_starts_with($routeName, 'admin.serial-numbers.')) {
            if (str_contains($routeName, 'destroy')) {
                return ['delete stock', 'delete products', 'edit stock', 'edit products'];
            }
            if (str_contains($routeName, 'create') || str_contains($routeName, 'store') || str_contains($routeName, 'import')) {
                return ['create stock', 'create products', 'edit stock', 'edit products'];
            }
            if (str_contains($routeName, 'edit') || str_contains($routeName, 'update') || str_contains($routeName, 'status')) {
                return ['edit stock', 'edit products'];
            }

            return ['view stock', 'view products', 'edit stock', 'edit products'];
        }

        if (str_starts_with($routeName, 'admin.pricing.')) {
            if (in_array($routeName, ['admin.pricing.update', 'admin.pricing.recalculate'])) {
                return ['edit pricing'];
            }

            return ['view pricing', 'edit pricing'];
        }

        if (str_starts_with($routeName, 'admin.fulfillment.')) {
            if (in_array($routeName, ['admin.fulfillment.allocate', 'admin.fulfillment.ship', 'admin.fulfillment.cancel'])) {
                return ['manage fulfillment', 'edit orders'];
            }

            return ['view fulfillment', 'manage fulfillment', 'view orders'];
        }

        if (str_starts_with($routeName, 'admin.reports.')) {
            return ['view reports'];
        }

        if (str_starts_with($routeName, 'admin.staff-attendance.')) {
            return ['manage attendance', 'view attendance'];
        }

        if (str_starts_with($routeName, 'admin.staff-salaries.')) {
            return ['manage salaries', 'view salaries'];
        }

        if (str_starts_with($routeName, 'admin.website-settings.') || str_starts_with($routeName, 'admin.storefront-menus.') || str_starts_with($routeName, 'admin.website-products.')) {
            if (in_array($routeName, ['admin.website-settings.update', 'admin.storefront-menus.store', 'admin.storefront-menus.update', 'admin.storefront-menus.destroy', 'admin.website-products.store', 'admin.website-products.update', 'admin.website-products.destroy'])) {
                return ['edit website settings'];
            }

            return ['view website settings', 'edit website settings'];
        }

        // 2. Generic REST Resource Pattern:
        // Automatically applies to Products, Categories, Brands, Models, Warehouses, Deliveries,
        // Suppliers, Staff, Customer Groups, Shipping Methods, AND all new menus dynamically!
        $parts = explode('.', $routeName);
        if (count($parts) >= 3 && $parts[0] === 'admin') {
            $resource = str_replace(['-', '_'], ' ', $parts[1]);
            $action = end($parts);

            // Sub-resource routes like admin.products.images.* or admin.quotes.item.delete
            if (str_contains($routeName, '.images.')) {
                return ["edit {$resource}", "create {$resource}"];
            }

            return match ($action) {
                'index', 'show' => ["view {$resource}", "create {$resource}", "edit {$resource}", "delete {$resource}"],
                'create', 'store' => ["create {$resource}"],
                'edit', 'update' => ["edit {$resource}", "create {$resource}"],
                'destroy', 'delete' => ["delete {$resource}"],
                'toggle-status', 'status' => ["edit {$resource}"],
                default => [],
            };
        }

        return [];
    }
}
