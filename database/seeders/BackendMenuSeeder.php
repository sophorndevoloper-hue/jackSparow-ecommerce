<?php

namespace Database\Seeders;

use App\Models\AdminMenu;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class BackendMenuSeeder extends Seeder
{
    /**
     * Standard System Menu & Action Definitions:
     * - Parent dropdown menus (Manage Products, Manage Stock, Manage Warehouses, Settings) have NO direct actions (actions: []).
     * - Child menus have their respective specific CRUD actions and permissions.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSystemMenuDefinitions(): array
    {
        return [
            // Main Section
            [
                'slug' => 'dashboard',
                'parent_slug' => null,
                'title' => 'Dashboard',
                'section' => 'Main',
                'route_name' => 'admin.dashboard',
                'icon' => 'bi-speedometer2',
                'view_permission' => 'view dashboard',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view dashboard', 'action_type' => 'view'],
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],

            // 1. Parent Dropdown: Products
            [
                'slug' => 'products',
                'parent_slug' => null,
                'title' => 'Manage Products',
                'section' => 'Inventory',
                'route_name' => null,
                'icon' => 'bi-cpu',
                'view_permission' => null,
                'actions' => [],
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'slug' => 'products_list',
                'parent_slug' => 'products',
                'title' => 'All Products',
                'section' => 'Inventory',
                'route_name' => 'admin.products.index',
                'icon' => 'bi-list-ul',
                'view_permission' => 'view products',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view products', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit products', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete products', 'action_type' => 'delete'],
                ],
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'slug' => 'products_create',
                'parent_slug' => 'products',
                'title' => 'Add Product',
                'section' => 'Inventory',
                'route_name' => 'admin.products.create',
                'icon' => 'bi-plus-circle',
                'view_permission' => 'create products',
                'actions' => [
                    ['name' => 'Create', 'permission' => 'create products', 'action_type' => 'create'],
                ],
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'slug' => 'products_images',
                'parent_slug' => 'products',
                'title' => 'Upload Product Img',
                'section' => 'Inventory',
                'route_name' => 'admin.products.images.index',
                'icon' => 'bi-images',
                'view_permission' => 'create products|edit products',
                'actions' => [
                    ['name' => 'Create', 'permission' => 'create products', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit products', 'action_type' => 'edit'],
                ],
                'sort_order' => 13,
                'is_active' => true,
            ],

            // 2. Parent Dropdown: Manage Stock
            [
                'slug' => 'stock',
                'parent_slug' => null,
                'title' => 'Manage Stock',
                'section' => 'Inventory',
                'route_name' => null,
                'icon' => 'bi-box-seam',
                'view_permission' => null,
                'actions' => [],
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'slug' => 'stock_overview',
                'parent_slug' => 'stock',
                'title' => 'Stock Overview',
                'section' => 'Inventory',
                'route_name' => 'admin.stock.index',
                'icon' => 'bi-boxes',
                'view_permission' => 'view products',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view products', 'action_type' => 'view'],
                ],
                'sort_order' => 21,
                'is_active' => true,
            ],
            [
                'slug' => 'serial_numbers',
                'parent_slug' => 'stock',
                'title' => 'Serial Registry & Warranties',
                'section' => 'Inventory',
                'route_name' => 'admin.serial-numbers.index',
                'icon' => 'bi-upc-scan',
                'view_permission' => 'view products',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view products', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit products', 'action_type' => 'edit'],
                ],
                'sort_order' => 22,
                'is_active' => true,
            ],
            [
                'slug' => 'stock_adjustments',
                'parent_slug' => 'stock',
                'title' => 'Stock Adjustments',
                'section' => 'Inventory',
                'route_name' => 'admin.stock.adjustments.index',
                'icon' => 'bi-sliders',
                'view_permission' => 'edit products',
                'actions' => [
                    ['name' => 'Create', 'permission' => 'create products', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit products', 'action_type' => 'edit'],
                ],
                'sort_order' => 23,
                'is_active' => true,
            ],
            [
                'slug' => 'stock_transfers',
                'parent_slug' => 'stock',
                'title' => 'Stock Transfers',
                'section' => 'Inventory',
                'route_name' => 'admin.stock.transfers.index',
                'icon' => 'bi-arrow-left-right',
                'view_permission' => 'edit products',
                'actions' => [
                    ['name' => 'Create', 'permission' => 'create products', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit products', 'action_type' => 'edit'],
                ],
                'sort_order' => 24,
                'is_active' => true,
            ],
            [
                'slug' => 'stock_low',
                'parent_slug' => 'stock',
                'title' => 'Low Stock Alerts',
                'section' => 'Inventory',
                'route_name' => 'admin.stock.index',
                'icon' => 'bi-exclamation-triangle-fill text-warning',
                'view_permission' => 'view products',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view products', 'action_type' => 'view'],
                ],
                'sort_order' => 25,
                'is_active' => true,
            ],
            [
                'slug' => 'stock_out',
                'parent_slug' => 'stock',
                'title' => 'Out of Stock',
                'section' => 'Inventory',
                'route_name' => 'admin.stock.index',
                'icon' => 'bi-x-octagon-fill text-danger',
                'view_permission' => 'view products',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view products', 'action_type' => 'view'],
                ],
                'sort_order' => 26,
                'is_active' => true,
            ],

            // 3. Parent Dropdown: Warehouses
            [
                'slug' => 'warehouses',
                'parent_slug' => null,
                'title' => 'Manage Warehouses',
                'section' => 'Inventory',
                'route_name' => null,
                'icon' => 'bi-buildings',
                'view_permission' => null,
                'actions' => [],
                'sort_order' => 27,
                'is_active' => true,
            ],
            [
                'slug' => 'warehouses_list',
                'parent_slug' => 'warehouses',
                'title' => 'All Warehouses',
                'section' => 'Inventory',
                'route_name' => 'admin.warehouses.index',
                'icon' => 'bi-list-ul',
                'view_permission' => 'view warehouses',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view warehouses', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit warehouses', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete warehouses', 'action_type' => 'delete'],
                ],
                'sort_order' => 28,
                'is_active' => true,
            ],
            [
                'slug' => 'warehouses_create',
                'parent_slug' => 'warehouses',
                'title' => 'Add Warehouse',
                'section' => 'Inventory',
                'route_name' => 'admin.warehouses.create',
                'icon' => 'bi-plus-circle',
                'view_permission' => 'create warehouses',
                'actions' => [
                    ['name' => 'Create', 'permission' => 'create warehouses', 'action_type' => 'create'],
                ],
                'sort_order' => 29,
                'is_active' => true,
            ],

            // Standalone Inventory Menus
            [
                'slug' => 'categories',
                'parent_slug' => null,
                'title' => 'Categories',
                'section' => 'Inventory',
                'route_name' => 'admin.categories.index',
                'icon' => 'bi-grid-3x3-gap',
                'view_permission' => 'view categories',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view categories', 'action_type' => 'view'],
                    ['name' => 'Create', 'permission' => 'create categories', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit categories', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete categories', 'action_type' => 'delete'],
                ],
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'slug' => 'brands',
                'parent_slug' => null,
                'title' => 'Brands',
                'section' => 'Inventory',
                'route_name' => 'admin.brands.index',
                'icon' => 'bi-patch-check',
                'view_permission' => 'view brands',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view brands', 'action_type' => 'view'],
                    ['name' => 'Create', 'permission' => 'create brands', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit brands', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete brands', 'action_type' => 'delete'],
                ],
                'sort_order' => 35,
                'is_active' => true,
            ],
            [
                'slug' => 'makes',
                'parent_slug' => null,
                'title' => 'Makes',
                'section' => 'Inventory',
                'route_name' => 'admin.makes.index',
                'icon' => 'bi-tools',
                'view_permission' => 'view makes',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view makes', 'action_type' => 'view'],
                    ['name' => 'Create', 'permission' => 'create makes', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit makes', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete makes', 'action_type' => 'delete'],
                ],
                'sort_order' => 36,
                'is_active' => true,
            ],

            // Sales & Fulfillment Section
            [
                'slug' => 'orders',
                'parent_slug' => null,
                'title' => 'Orders',
                'section' => 'Sales & Fulfillment',
                'route_name' => 'admin.orders.index',
                'icon' => 'bi-cart-check',
                'view_permission' => 'view orders',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view orders', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit orders', 'action_type' => 'edit'],
                ],
                'sort_order' => 40,
                'is_active' => true,
            ],

            // People Section
            [
                'slug' => 'customer_groups',
                'parent_slug' => null,
                'title' => 'Customer Groups (B2B)',
                'section' => 'People',
                'route_name' => 'admin.customer-groups.index',
                'icon' => 'bi-tags-fill',
                'view_permission' => 'view customers',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view customers', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit customers', 'action_type' => 'edit'],
                ],
                'sort_order' => 48,
                'is_active' => true,
            ],
            [
                'slug' => 'customers',
                'parent_slug' => null,
                'title' => 'Customers (Simple/VIP)',
                'section' => 'People',
                'route_name' => 'admin.customers.index',
                'icon' => 'bi-person-badge-fill',
                'view_permission' => 'view customers',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view customers', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit customers', 'action_type' => 'edit'],
                ],
                'sort_order' => 50,
                'is_active' => true,
            ],
            [
                'slug' => 'suppliers',
                'parent_slug' => null,
                'title' => 'Suppliers',
                'section' => 'People',
                'route_name' => 'admin.suppliers.index',
                'icon' => 'bi-truck',
                'view_permission' => 'view suppliers',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view suppliers', 'action_type' => 'view'],
                    ['name' => 'Create', 'permission' => 'create suppliers', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit suppliers', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete suppliers', 'action_type' => 'delete'],
                ],
                'sort_order' => 55,
                'is_active' => true,
            ],

            // 4. Parent Dropdown: Settings (no direct actions)
            [
                'slug' => 'settings',
                'parent_slug' => null,
                'title' => 'Settings',
                'section' => 'System Access',
                'route_name' => null,
                'icon' => 'bi-gear-wide-connected',
                'view_permission' => null,
                'actions' => [],
                'sort_order' => 60,
                'is_active' => true,
            ],
            [
                'slug' => 'users',
                'parent_slug' => 'settings',
                'title' => 'Users',
                'section' => 'System Access',
                'route_name' => 'admin.users.index',
                'icon' => 'bi-people-fill',
                'view_permission' => 'view users',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view users', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit users', 'action_type' => 'edit'],
                    ['name' => 'Approve', 'permission' => 'approve users', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete users', 'action_type' => 'delete'],
                ],
                'sort_order' => 61,
                'is_active' => true,
            ],
            [
                'slug' => 'roles',
                'parent_slug' => 'settings',
                'title' => 'Roles',
                'section' => 'System Access',
                'route_name' => 'admin.roles.index',
                'icon' => 'bi-shield-lock-fill',
                'view_permission' => 'view roles',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view roles', 'action_type' => 'view'],
                    ['name' => 'Create', 'permission' => 'create roles', 'action_type' => 'create'],
                    ['name' => 'Edit', 'permission' => 'edit roles', 'action_type' => 'edit'],
                    ['name' => 'Delete', 'permission' => 'delete roles', 'action_type' => 'delete'],
                ],
                'sort_order' => 62,
                'is_active' => true,
            ],
            [
                'slug' => 'menu_setup',
                'parent_slug' => 'settings',
                'title' => 'Menu Setup',
                'section' => 'System Access',
                'route_name' => 'admin.menus.index',
                'icon' => 'bi-sliders',
                'view_permission' => 'view menus',
                'actions' => [
                    ['name' => 'View', 'permission' => 'view menus', 'action_type' => 'view'],
                    ['name' => 'Edit', 'permission' => 'edit menus', 'action_type' => 'edit'],
                ],
                'sort_order' => 64,
                'is_active' => true,
            ],

            [
                'slug' => 'clear-cache',
                'parent_slug' => null,
                'title' => 'Clear Cache',
                'section' => 'System Access',
                'route_name' => 'admin.cache.clear',
                'icon' => 'bi-arrow-repeat',
                'view_permission' => null,
                'actions' => [
                    ['name' => 'Clear Cache', 'route_name' => 'admin.cache.clear', 'icon' => 'bi-arrow-repeat', 'permission' => null, 'action_type' => 'edit'],
                ],
                'sort_order' => 90,
                'is_active' => true,
            ],
        ];
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $definitions = static::getSystemMenuDefinitions();

        $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);

        $allPermissions = [];
        $menuPermMap = [];

        foreach ($definitions as $def) {
            $menu = AdminMenu::firstOrNew(['slug' => $def['slug']]);
            $menu->title = $def['title'];
            $menu->parent_slug = $def['parent_slug'] ?? null;
            $menu->section = $def['section'];
            $menu->route_name = $def['route_name'];
            $menu->icon = $def['icon'];
            $menu->view_permission = $def['view_permission'];
            $menu->actions = $def['actions'];
            $menu->sort_order = $def['sort_order'];

            if (! $menu->exists) {
                $menu->is_active = $def['is_active'];
            }
            $menu->save();

            if (! empty($def['view_permission'])) {
                foreach (explode('|', $def['view_permission']) as $vp) {
                    $pName = trim($vp);
                    $allPermissions[] = $pName;
                    if (! isset($menuPermMap[$pName])) {
                        $menuPermMap[$pName] = $menu->id;
                    }
                }
            }

            if (! empty($def['actions'])) {
                foreach ($def['actions'] as $act) {
                    if (! empty($act['permission'])) {
                        foreach (explode('|', $act['permission']) as $p) {
                            $pName = trim($p);
                            $allPermissions[] = $pName;
                            if (! isset($menuPermMap[$pName])) {
                                $menuPermMap[$pName] = $menu->id;
                            }
                        }
                    }
                }
            }
        }

        // Map core permissions to their menus
        $dashMenu = AdminMenu::where('slug', 'dashboard')->first();
        if ($dashMenu) {
            $menuPermMap['view dashboard'] = $dashMenu->id;
        }
        $usersMenu = AdminMenu::where('slug', 'users')->first();
        if ($usersMenu) {
            $menuPermMap['approve users'] = $usersMenu->id;
        }
        $menuSetupMenu = AdminMenu::where('slug', 'menu_setup')->first();
        if ($menuSetupMenu) {
            $menuPermMap['view menus'] = $menuSetupMenu->id;
            $menuPermMap['edit menus'] = $menuSetupMenu->id;
        }

        $allPermissions[] = 'view dashboard';
        $allPermissions[] = 'view menus';
        $allPermissions[] = 'edit menus';
        $allPermissions[] = 'approve users';
        $allPermissions = array_unique(array_filter($allPermissions));

        // Clean up legacy export and orphan permissions
        Permission::where('name', 'LIKE', 'export %')->delete();
        Permission::where('guard_name', 'backend')
            ->whereNotIn('name', $allPermissions)
            ->delete();

        $routeMap = AdminMenu::getPermissionActionRouteMap();

        foreach ($allPermissions as $permName) {
            $perm = Permission::firstOrNew(['name' => $permName, 'guard_name' => 'backend']);
            if (isset($routeMap[$permName])) {
                $perm->action_route = $routeMap[$permName];
            }
            if (isset($menuPermMap[$permName])) {
                $perm->menu_id = $menuPermMap[$permName];
            }
            $perm->save();
        }

        $allBackendPerms = Permission::where('guard_name', 'backend')->get();
        $superadminRole->syncPermissions($allBackendPerms);
        $adminRole->syncPermissions($allBackendPerms);

        AdminMenu::flushCache();
        Cache::forget('spatie.permission.cache');
    }
}
