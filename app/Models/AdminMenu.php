<?php

namespace App\Models;

use Database\Seeders\BackendMenuSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

class AdminMenu extends Model
{
    use HasFactory;

    /**
     * Get the permissions associated directly with this menu.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'menu_id');
    }

    protected $fillable = [
        'title',
        'slug',
        'parent_slug',
        'section',
        'route_name',
        'icon',
        'view_permission',
        'actions',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'actions' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Scope query to only active menus.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query ordered by sort_order and id.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Check if this menu is a parent container with no direct route or actions.
     */
    public function isParentDropdown(): bool
    {
        return empty($this->route_name) || empty($this->actions) || static::where('parent_slug', $this->slug)->exists();
    }

    /**
     * Clear menu cache when saved or deleted.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Get all active menus keyed by slug.
     *
     * @return array<string, bool>
     */
    public static function getActiveStatusMap(): array
    {
        return Cache::remember('admin_active_menus', 3600, function () {
            try {
                return static::pluck('is_active', 'slug')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Get all menu records keyed by slug.
     *
     * @return Collection<string, AdminMenu>
     */
    public static function getAllMenusMap(): Collection
    {
        return Cache::remember('admin_menus_all_map', 3600, function () {
            try {
                if (static::count() === 0) {
                    static::syncSystemMenusAndPermissions();
                }

                return static::all()->keyBy('slug');
            } catch (\Throwable $e) {
                return collect();
            }
        });
    }

    /**
     * Check if a specific menu slug is active.
     */
    public static function isMenuActive(string $slug): bool
    {
        $map = static::getActiveStatusMap();

        return $map[$slug] ?? true;
    }

    /**
     * Clear the cached active status map.
     */
    public static function flushCache(): void
    {
        Cache::forget('admin_active_menus');
        Cache::forget('admin_menus_all_map');
    }

    /**
     * Check if a menu is active for the current user's session.
     */
    public static function isMenuActiveForSession(int|string|self $menu): bool
    {
        $id = $menu instanceof self ? $menu->id : (is_numeric($menu) ? (int) $menu : null);
        $slug = $menu instanceof self ? $menu->slug : (is_string($menu) ? $menu : null);

        $disabled = session('admin_disabled_menus', []);

        if ($id !== null && in_array($id, $disabled, true)) {
            return false;
        }

        if ($slug !== null && in_array($slug, $disabled, true)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a user has a specific permission safely across guards.
     */
    public static function userHasPermission(User $adminUser, string $permissionString): bool
    {
        $perms = explode('|', $permissionString);
        foreach ($perms as $p) {
            $p = trim($p);
            if (empty($p)) {
                continue;
            }

            if ($adminUser->can($p)) {
                return true;
            }

            try {
                if ($adminUser->hasPermissionTo($p, 'backend') || $adminUser->hasPermissionTo($p, 'web')) {
                    return true;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return false;
    }

    /**
     * Group permissions dynamically by Menu Setup modules.
     * Pure parent dropdown menus (Products, Manage Stock, Warehouses, Settings) are skipped
     * so individual child menus have their own clean cards.
     *
     * @param  Collection<int, Permission>  $permissions
     * @return array<string, Collection<int, Permission>>
     */
    public static function groupPermissionsDynamically(Collection $permissions): array
    {
        if (static::count() === 0) {
            static::syncSystemMenusAndPermissions();
        }

        $allMenus = static::ordered()->get();
        $grouped = [];
        $assignedIds = [];

        $parentMenus = $allMenus->filter(fn ($m) => empty($m->parent_slug) && $allMenus->contains('parent_slug', $m->slug));
        $standaloneMenus = $allMenus->filter(fn ($m) => empty($m->parent_slug) && ! $allMenus->contains('parent_slug', $m->slug));
        $settingsChildren = $allMenus->filter(fn ($m) => $m->parent_slug === 'settings');

        // Define clean, unified module cards in logical order
        $modules = [];

        // 1. Dashboard
        if ($dash = $standaloneMenus->firstWhere('slug', 'dashboard')) {
            $modules[] = ['title' => 'Dashboard', 'menus' => collect([$dash])];
        }

        // 2. Manage Products (Consolidate All Products, Add Product, Upload Images)
        if ($prodParent = $parentMenus->firstWhere('slug', 'products')) {
            $children = $allMenus->filter(fn ($m) => $m->parent_slug === 'products');
            $modules[] = ['title' => $prodParent->title, 'menus' => collect([$prodParent])->merge($children)];
        }

        // 3. Categories
        if ($cat = $standaloneMenus->firstWhere('slug', 'categories')) {
            $modules[] = ['title' => $cat->title, 'menus' => collect([$cat])];
        }

        // 4. Brands
        if ($brand = $standaloneMenus->firstWhere('slug', 'brands')) {
            $modules[] = ['title' => $brand->title, 'menus' => collect([$brand])];
        }

        // 5. Manage Warehouses (Consolidate All Warehouses, Add Warehouse)
        if ($whParent = $parentMenus->firstWhere('slug', 'warehouses')) {
            $children = $allMenus->filter(fn ($m) => $m->parent_slug === 'warehouses');
            $modules[] = ['title' => $whParent->title, 'menus' => collect([$whParent])->merge($children)];
        }

        // 6. Orders
        if ($orders = $standaloneMenus->firstWhere('slug', 'orders')) {
            $modules[] = ['title' => $orders->title, 'menus' => collect([$orders])];
        }

        // 7. Customers
        if ($cust = $standaloneMenus->firstWhere('slug', 'customers')) {
            $modules[] = ['title' => 'Customers', 'menus' => collect([$cust])];
        }

        // 8. Suppliers
        if ($sup = $standaloneMenus->firstWhere('slug', 'suppliers')) {
            $modules[] = ['title' => $sup->title, 'menus' => collect([$sup])];
        }

        // 9. Users
        if ($users = $settingsChildren->firstWhere('slug', 'users')) {
            $modules[] = ['title' => 'Users', 'menus' => collect([$users])];
        }

        // 10. Roles
        if ($roles = $settingsChildren->firstWhere('slug', 'roles')) {
            $modules[] = ['title' => 'Roles', 'menus' => collect([$roles])];
        }

        // 11. Menu Setup
        if ($menuSetup = $settingsChildren->firstWhere('slug', 'menu_setup')) {
            $modules[] = ['title' => 'Menu Setup', 'menus' => collect([$menuSetup])];
        }

        foreach ($modules as $mod) {
            $modPermNames = [];
            foreach ($mod['menus'] as $m) {
                if (! empty($m->view_permission)) {
                    foreach (explode('|', $m->view_permission) as $p) {
                        $modPermNames[] = trim($p);
                    }
                }
                if (! empty($m->actions)) {
                    foreach ($m->actions as $act) {
                        if (! empty($act['permission'])) {
                            foreach (explode('|', $act['permission']) as $p) {
                                $modPermNames[] = trim($p);
                            }
                        }
                    }
                }
            }

            if ($mod['title'] === 'Dashboard') {
                $modPermNames[] = 'view dashboard';
            }

            $modPermNames = array_unique(array_filter($modPermNames));

            $matched = $permissions->filter(function ($perm) use ($modPermNames, $mod, &$assignedIds) {
                if (in_array($perm->id, $assignedIds, true)) {
                    return false;
                }

                if (in_array($perm->name, $modPermNames, true)) {
                    return true;
                }

                $cleanTitle = strtolower(str_replace(['manage', '-', '_', ' '], '', $mod['title']));
                $cleanPerm = strtolower(str_replace(['manage', 'view', 'create', 'edit', 'delete', 'approve', '-', '_', ' '], '', $perm->name));
                if ($cleanTitle !== '' && $cleanPerm !== '' && (str_contains($cleanPerm, $cleanTitle) || str_contains($cleanTitle, $cleanPerm))) {
                    return true;
                }

                return false;
            });

            if ($matched->isNotEmpty()) {
                foreach ($matched as $m) {
                    $assignedIds[] = $m->id;
                }
                $grouped[$mod['title']] = $matched->values();
            }
        }

        $unassigned = $permissions->reject(fn ($p) => in_array($p->id, $assignedIds, true));
        if ($unassigned->isNotEmpty()) {
            $grouped['General & Custom'] = $unassigned->values();
        }

        return $grouped;
    }

    /**
     * Map all permissions to their target action routes.
     *
     * @return array<string, string>
     */
    public static function getPermissionActionRouteMap(): array
    {
        $map = [
            'view dashboard' => 'admin.dashboard',
            'view products' => 'admin.products.index',
            'create products' => 'admin.products.create',
            'edit products' => 'admin.products.edit',
            'delete products' => 'admin.products.destroy',
            'view categories' => 'admin.categories.index',
            'create categories' => 'admin.categories.create',
            'edit categories' => 'admin.categories.edit',
            'delete categories' => 'admin.categories.destroy',
            'view brands' => 'admin.brands.index',
            'create brands' => 'admin.brands.create',
            'edit brands' => 'admin.brands.edit',
            'delete brands' => 'admin.brands.destroy',
            'view makes' => 'admin.makes.index',
            'create makes' => 'admin.makes.create',
            'edit makes' => 'admin.makes.edit',
            'delete makes' => 'admin.makes.destroy',
            'view warehouses' => 'admin.warehouses.index',
            'create warehouses' => 'admin.warehouses.create',
            'edit warehouses' => 'admin.warehouses.edit',
            'delete warehouses' => 'admin.warehouses.destroy',
            'view orders' => 'admin.orders.index',
            'edit orders' => 'admin.orders.status',
            'view customers' => 'admin.customers.index',
            'edit customers' => 'admin.customers.toggle-special',
            'view suppliers' => 'admin.suppliers.index',
            'create suppliers' => 'admin.suppliers.create',
            'edit suppliers' => 'admin.suppliers.edit',
            'delete suppliers' => 'admin.suppliers.destroy',
            'view users' => 'admin.users.index',
            'edit users' => 'admin.users.edit',
            'approve users' => 'admin.users.toggle-approval',
            'delete users' => 'admin.users.destroy',
            'view roles' => 'admin.roles.index',
            'create roles' => 'admin.roles.create',
            'edit roles' => 'admin.roles.edit',
            'delete roles' => 'admin.roles.destroy',
            'view menus' => 'admin.menus.index',
            'edit menus' => 'admin.menus.edit',
            'view settings' => 'admin.menus.index',
            'edit settings' => 'admin.menus.edit',
        ];

        try {
            $menus = static::all();
            foreach ($menus as $menu) {
                if (! empty($menu->view_permission) && ! empty($menu->route_name)) {
                    $map[$menu->view_permission] = $menu->route_name;
                }

                if (! empty($menu->actions)) {
                    foreach ($menu->actions as $act) {
                        if (! empty($act['permission']) && ! empty($act['route_name'])) {
                            foreach (explode('|', $act['permission']) as $p) {
                                $map[trim($p)] = $act['route_name'];
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // fallback to default map
        }

        return $map;
    }

    /**
     * Auto-discover and synchronize all system menus and Spatie permissions.
     */
    public static function syncSystemMenusAndPermissions(): void
    {
        app(BackendMenuSeeder::class)->run();
    }

    /**
     * Build dynamic sidebar navigation tree directly from Menu Setup based on active menus and role permissions.
     * Child menus (parent_slug set) are cleanly nested inside their parent dropdown menus.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function getSidebarTree(?User $adminUser = null): array
    {
        if (! $adminUser) {
            return [];
        }

        if (static::count() === 0) {
            static::syncSystemMenusAndPermissions();
        }

        $isSuperOrAdmin = $adminUser->hasRole('superadmin', 'backend')
            || $adminUser->hasRole('admin', 'backend')
            || $adminUser->hasRole('superadmin')
            || $adminUser->hasRole('admin');

        $disabledMenuIds = session('admin_disabled_menus', []);
        $activeMenus = static::active()->ordered()->get()->reject(function ($m) use ($disabledMenuIds) {
            return in_array($m->id, $disabledMenuIds, true) || in_array($m->slug, $disabledMenuIds, true);
        });
        $childrenByParent = $activeMenus->whereNotNull('parent_slug')->where('parent_slug', '!=', '')->groupBy('parent_slug');
        $topLevelMenus = $activeMenus->filter(fn ($m) => empty($m->parent_slug));

        $sections = [];

        foreach ($topLevelMenus as $menu) {
            $subItems = [];

            // Case 1: Menu has child menus linked via parent_slug (e.g. Products, Stock, Warehouses, Settings)
            if ($childrenByParent->has($menu->slug)) {
                $childMenus = $childrenByParent->get($menu->slug);
                foreach ($childMenus as $child) {
                    if (! $isSuperOrAdmin && ! empty($child->view_permission)) {
                        if (! static::userHasPermission($adminUser, $child->view_permission)) {
                            continue;
                        }
                    }

                    $childParams = [];
                    if (str_contains($child->slug, '_low')) {
                        $childParams = ['filter' => 'low'];
                    } elseif (str_contains($child->slug, '_out')) {
                        $childParams = ['filter' => 'out'];
                    }

                    $childUrl = $child->route_name && Route::has($child->route_name) ? route($child->route_name, $childParams) : '#';

                    $isChildActive = false;
                    if ($child->route_name) {
                        $isExact = request()->routeIs($child->route_name);
                        if ($isExact) {
                            if (str_contains($child->slug, '_low')) {
                                $isChildActive = request('filter') === 'low';
                            } elseif (str_contains($child->slug, '_out')) {
                                $isChildActive = request('filter') === 'out';
                            } else {
                                $isChildActive = ! request()->has('filter');
                            }
                        } else {
                            if (str_ends_with($child->route_name, '.index')) {
                                $base = substr($child->route_name, 0, -6);
                                $isChildActive = request()->routeIs($base.'.show') || request()->routeIs($base.'.edit');
                            }
                        }
                    }

                    $subItems[] = [
                        'title' => $child->title,
                        'url' => $childUrl,
                        'route_name' => $child->route_name,
                        'icon' => $child->icon ?? 'bi-circle',
                        'is_active' => $isChildActive,
                    ];
                }

                // If user cannot access any children and parent is not superadmin, skip
                if (empty($subItems) && ! $isSuperOrAdmin) {
                    continue;
                }
            } else {
                // Case 2: Regular top-level menu with action sub-items
                if (! $isSuperOrAdmin && ! empty($menu->view_permission)) {
                    $hasPerm = static::userHasPermission($adminUser, $menu->view_permission);

                    if (! $hasPerm && ! empty($menu->actions)) {
                        foreach ($menu->actions as $act) {
                            if (! empty($act['permission']) && static::userHasPermission($adminUser, $act['permission'])) {
                                $hasPerm = true;
                                break;
                            }
                        }
                    }

                    if (! $hasPerm) {
                        continue;
                    }
                }

                $subItems = static::resolveSubMenuItems($menu, $adminUser, $isSuperOrAdmin);
            }

            $url = '#';
            if ($menu->route_name && Route::has($menu->route_name)) {
                $url = route($menu->route_name);
            }

            $isActiveRoute = false;
            if ($menu->route_name) {
                $prefix = preg_replace('/\.index$|\.show$|\.create$/', '.*', $menu->route_name);
                $isActiveRoute = request()->routeIs($menu->route_name) || request()->routeIs($prefix);
            }

            if (! empty($subItems)) {
                foreach ($subItems as $sub) {
                    if ($sub['is_active']) {
                        $isActiveRoute = true;
                        break;
                    }
                }
            }

            $menuData = [
                'id' => $menu->id,
                'title' => $menu->title,
                'slug' => $menu->slug,
                'icon' => $menu->icon,
                'route_name' => $menu->route_name,
                'url' => $url,
                'is_active' => $isActiveRoute,
                'sub_items' => $subItems,
            ];

            $sectionKey = $menu->section ?: 'Main';
            $sections[$sectionKey][] = $menuData;
        }

        return $sections;
    }

    /**
     * Resolve child links dynamically from Menu Setup actions.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function resolveSubMenuItems(self $menu, User $adminUser, bool $isSuperOrAdmin): array
    {
        $items = [];
        $actions = $menu->actions ?? [];

        foreach ($actions as $action) {
            if (empty($action['show_in_menu'])) {
                continue;
            }

            if (! $isSuperOrAdmin && ! empty($action['permission'])) {
                if (! static::userHasPermission($adminUser, $action['permission'])) {
                    continue;
                }
            }

            $routeName = $action['route_name'] ?? '';
            $params = $action['params'] ?? [];
            $url = $routeName && Route::has($routeName) ? route($routeName, $params) : '#';

            $isActive = false;
            if ($routeName) {
                $isExact = request()->routeIs($routeName);
                if ($isExact) {
                    if (isset($params['filter'])) {
                        $isActive = request('filter') === $params['filter'];
                    } else {
                        $isActive = ! request()->has('filter');
                    }
                } else {
                    if (str_ends_with($routeName, '.index')) {
                        $base = substr($routeName, 0, -6);
                        $isActive = request()->routeIs($base.'.show') || request()->routeIs($base.'.edit');
                    }
                }
            }

            $items[] = [
                'title' => $action['name'],
                'url' => $url,
                'route_name' => $routeName,
                'icon' => $action['icon'] ?? 'bi-dot',
                'is_active' => $isActive,
            ];
        }

        return $items;
    }
}
