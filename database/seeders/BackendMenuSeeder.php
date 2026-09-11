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
        $jsonPath = database_path('data/backend_menus.json');
        if (file_exists($jsonPath)) {
            $decoded = json_decode(file_get_contents($jsonPath), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
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

        $validSlugs = array_column($definitions, 'slug');
        AdminMenu::whereNotIn('slug', $validSlugs)->delete();

        // Map core permissions to their menus
        $dashMenu = AdminMenu::where('slug', 'dashboard')->first();
        if ($dashMenu) {
            $menuPermMap['view dashboard'] = $dashMenu->id;
        }
        $usersMenu = AdminMenu::where('slug', 'users')->first();
        if ($usersMenu) {
            $menuPermMap['approve users'] = $usersMenu->id;
            $menuPermMap['manage users'] = $usersMenu->id;
        }
        $rolesMenu = AdminMenu::where('slug', 'roles')->first();
        if ($rolesMenu) {
            $menuPermMap['manage roles'] = $rolesMenu->id;
        }
        $menuSetupMenu = AdminMenu::where('slug', 'menu_setup')->first();
        if ($menuSetupMenu) {
            $menuPermMap['view menus'] = $menuSetupMenu->id;
            $menuPermMap['edit menus'] = $menuSetupMenu->id;
            $menuPermMap['view settings'] = $menuSetupMenu->id;
            $menuPermMap['edit settings'] = $menuSetupMenu->id;
        }
        $stockMenu = AdminMenu::where('slug', 'stock_overview')->first();
        if ($stockMenu) {
            $menuPermMap['view stock'] = $stockMenu->id;
            $menuPermMap['create stock'] = $stockMenu->id;
            $menuPermMap['edit stock'] = $stockMenu->id;
            $menuPermMap['delete stock'] = $stockMenu->id;
        }

        $allPermissions[] = 'view dashboard';
        $allPermissions[] = 'view menus';
        $allPermissions[] = 'edit menus';
        $allPermissions[] = 'view settings';
        $allPermissions[] = 'edit settings';
        $allPermissions[] = 'view stock';
        $allPermissions[] = 'create stock';
        $allPermissions[] = 'edit stock';
        $allPermissions[] = 'delete stock';
        $allPermissions[] = 'approve users';
        $allPermissions[] = 'manage users';
        $allPermissions[] = 'manage roles';
        $allPermissions[] = 'manage suppliers';
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
