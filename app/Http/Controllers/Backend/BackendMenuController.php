<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackendMenuController extends Controller
{
    /**
     * Display the menu & action control center.
     */
    public function index(): View
    {
        // Auto-synchronize initial menus and permissions if database is empty
        if (AdminMenu::count() === 0) {
            AdminMenu::syncSystemMenusAndPermissions();
        }

        $menus = AdminMenu::ordered()->get();
        $disabledMenuIds = session('admin_disabled_menus', []);

        foreach ($menus as $menu) {
            $menu->is_active_for_session = AdminMenu::isMenuActiveForSession($menu);
        }

        $groupedMenus = $menus->groupBy('section');

        $totalMenus = $menus->count();
        $activeMenus = $menus->where('is_active_for_session', true)->count();
        $disabledMenus = $totalMenus - $activeMenus;

        $totalActions = $menus->reduce(function ($carry, $menu) {
            return $carry + count($menu->actions ?? []);
        }, 0);

        // Fetch backend roles and permissions to display role coverage per action
        $roles = Role::where('guard_name', 'backend')->with('permissions')->get();
        $availablePermissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();

        return view('backend.settings.menus.index', compact(
            'menus',
            'groupedMenus',
            'totalMenus',
            'activeMenus',
            'disabledMenus',
            'totalActions',
            'roles',
            'availablePermissions',
        ));
    }

    /**
     * Restore visibility for all menus for the current user session.
     */
    public function resetVisibility(): RedirectResponse
    {
        session()->forget('admin_disabled_menus');

        return back()->with('success', 'All hidden menus have been restored and are now visible in your navigation.');
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(): View
    {
        $availablePermissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();
        $roles = Role::where('guard_name', 'backend')->with('permissions')->get();

        // Potential parent menus: top-level menus
        $parentMenus = AdminMenu::where(function ($query) {
            $query->whereNull('parent_slug')->orWhere('parent_slug', '');
        })
            ->ordered()
            ->get();

        $defaultSortOrder = (AdminMenu::max('sort_order') ?? 0) + 1;

        return view('backend.settings.menus.create', compact('availablePermissions', 'roles', 'parentMenus', 'defaultSortOrder'));
    }

    /**
     * Store a newly created menu in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalize empty parent_slug to null
        $request->merge([
            'parent_slug' => $request->filled('parent_slug') ? $request->input('parent_slug') : null,
        ]);

        // Auto-generate base slug from title if not explicitly provided
        if (! $request->filled('slug') && $request->filled('title')) {
            $baseSlug = Str::slug($request->input('title'), '_');
            $request->merge(['slug' => $baseSlug ?: 'menu_'.time()]);
        }

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('admin_menus', 'title')->where(function ($query) use ($request) {
                    $parent = $request->input('parent_slug');

                    return $parent ? $query->where('parent_slug', $parent) : $query->whereNull('parent_slug');
                }),
            ],
            'slug' => ['required', 'string', 'max:100', 'unique:admin_menus,slug'],
            'parent_slug' => ['nullable', 'string', 'max:100', 'exists:admin_menus,slug'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:50'],
            'view_permission' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.unique' => "A menu with the title ':input' already exists under the selected parent menu.",
            'slug.unique' => "A menu with the unique identifier (slug) ':input' already exists.",
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['parent_slug'] = ! empty($validated['parent_slug']) ? $validated['parent_slug'] : null;

        // Keep or inherit section so database integrity is preserved
        if (empty($validated['section'])) {
            if ($validated['parent_slug']) {
                $parent = AdminMenu::where('slug', $validated['parent_slug'])->first();
                $validated['section'] = $parent?->section ?? 'Main';
            } else {
                $validated['section'] = 'Main';
            }
        }

        // Configure default actions if route is specified
        if (! empty($validated['route_name'])) {
            $validated['actions'] = [
                [
                    'name' => 'View',
                    'action_type' => 'view',
                    'route_name' => $validated['route_name'],
                    'permission' => $validated['view_permission'] ?? null,
                ],
            ];
        } else {
            $validated['actions'] = [];
        }

        $menu = DB::transaction(function () use ($validated) {
            return AdminMenu::create($validated);
        });

        AdminMenu::flushCache();

        return redirect()->route('admin.menus.index')
            ->with('success', "Menu '{$menu->title}' created successfully.");
    }

    /**
     * Show the form for editing the specified menu.
     */
    public function edit(AdminMenu $menu): View
    {
        $availablePermissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();
        $roles = Role::where('guard_name', 'backend')->with('permissions')->get();

        // Potential parent menus: top-level menus excluding this menu itself
        $parentMenus = AdminMenu::where('id', '!=', $menu->id)
            ->where(function ($query) {
                $query->whereNull('parent_slug')->orWhere('parent_slug', '');
            })
            ->ordered()
            ->get();

        return view('backend.settings.menus.edit', compact('menu', 'availablePermissions', 'roles', 'parentMenus'));
    }

    /**
     * Update the specified menu in storage.
     */
    public function update(Request $request, AdminMenu $menu): RedirectResponse
    {
        // Normalize empty parent_slug to null
        $request->merge([
            'parent_slug' => $request->filled('parent_slug') ? $request->input('parent_slug') : null,
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'parent_slug' => ['nullable', 'string', 'max:100', 'exists:admin_menus,slug'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'section' => ['nullable', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:50'],
            'view_permission' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['parent_slug'] = ! empty($validated['parent_slug']) ? $validated['parent_slug'] : null;

        // Prevent setting itself as parent
        if ($validated['parent_slug'] && $validated['parent_slug'] === $menu->slug) {
            return back()->withErrors(['parent_slug' => 'A menu cannot be its own parent.'])->withInput();
        }

        // Prevent setting a parent if this menu has children (to maintain a clean 2-level hierarchy)
        if ($validated['parent_slug'] && AdminMenu::where('parent_slug', $menu->slug)->exists()) {
            return back()->withErrors(['parent_slug' => 'This menu is currently a parent group with sub-items. Please reassign its child menus before assigning it to a parent.'])->withInput();
        }

        // Keep or inherit section so database integrity is preserved
        if (empty($validated['section'])) {
            if ($validated['parent_slug']) {
                $parent = AdminMenu::where('slug', $validated['parent_slug'])->first();
                $validated['section'] = $parent?->section ?? $menu->section ?? 'Main';
            } else {
                $validated['section'] = $menu->section ?? 'Main';
            }
        }

        // Normalize route_name
        $validated['route_name'] = ! empty($validated['route_name']) ? $validated['route_name'] : null;

        // If route is cleared, clear actions so no active route targets remain
        if (empty($validated['route_name'])) {
            $validated['actions'] = [];
        } else {
            // Update or initialize actions
            if (empty($menu->actions)) {
                $validated['actions'] = [
                    [
                        'name' => 'View',
                        'action_type' => 'view',
                        'route_name' => $validated['route_name'],
                        'permission' => $validated['view_permission'] ?? null,
                    ],
                ];
            } else {
                $actions = $menu->actions;
                $hasView = false;
                foreach ($actions as &$act) {
                    if (($act['action_type'] ?? '') === 'view') {
                        $act['route_name'] = $validated['route_name'];
                        if (isset($validated['view_permission'])) {
                            $act['permission'] = $validated['view_permission'];
                        }
                        $hasView = true;
                    }
                }
                if (! $hasView) {
                    $actions[] = [
                        'name' => 'View',
                        'action_type' => 'view',
                        'route_name' => $validated['route_name'],
                        'permission' => $validated['view_permission'] ?? null,
                    ];
                }
                $validated['actions'] = $actions;
            }
        }

        $menu->update($validated);

        return redirect()->route('admin.menus.index')
            ->with('success', "Menu '{$menu->title}' updated successfully.");
    }

    /**
     * Remove the specified menu from storage.
     */
    public function destroy(Request $request, AdminMenu $menu): RedirectResponse|JsonResponse
    {
        // 1. Prevent deleting protected core system components
        if (AdminMenu::isProtected($menu)) {
            $msg = "The '{$menu->title}' menu is a core system component and cannot be deleted.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // 2. Prevent deleting menu if it still has an active route target
        if (! empty($menu->route_name)) {
            $msg = "Cannot delete menu '{$menu->title}' because it has an active system route ('{$menu->route_name}'). Please edit the menu to remove/delete the route target before deleting.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // Check if actions contain any active route targets
        $hasActionRoutes = collect($menu->actions ?? [])->contains(fn ($act) => ! empty($act['route_name']));
        if ($hasActionRoutes) {
            $msg = "Cannot delete menu '{$menu->title}' because it contains associated action routes. Please edit the menu to clear action routes before deleting.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // 3. Prevent deleting if it contains child sub-menus
        $childCount = AdminMenu::where('parent_slug', $menu->slug)->count();
        if ($childCount > 0) {
            $msg = "Cannot delete menu '{$menu->title}' because it contains {$childCount} sub-menu(s). Please reassign or delete its child menus first.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $title = $menu->title;
        $menu->delete();

        // Clean up user session disabled list if present
        $disabled = session('admin_disabled_menus', []);
        if (in_array($menu->id, $disabled, true) || in_array($menu->slug, $disabled, true)) {
            session(['admin_disabled_menus' => array_values(array_filter($disabled, fn ($id) => $id !== $menu->id && $id !== $menu->slug))]);
        }

        $msg = "Menu '{$title}' deleted successfully.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('admin.menus.index')->with('success', $msg);
    }

    /**
     * Toggle the active/inactive status of a menu item for the current user session.
     * Protected core system menus (Dashboard, Settings, Menu Setup) cannot be hidden.
     * When toggling a parent menu, all non-protected child menus are also automatically toggled.
     */
    public function toggleActive(Request $request, AdminMenu $menu): RedirectResponse|JsonResponse
    {
        if (AdminMenu::isProtected($menu)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "The '{$menu->title}' menu is a core system component and cannot be hidden.",
                ], 422);
            }

            return back()->with('error', "The '{$menu->title}' menu is a core system component and cannot be hidden.");
        }

        $disabled = session('admin_disabled_menus', []);

        // Find non-protected child menus belonging to this menu if it is a parent
        $childMenus = AdminMenu::where('parent_slug', $menu->slug)->get();
        $childIds = $childMenus->reject(fn ($c) => AdminMenu::isProtected($c))->pluck('id')->toArray();
        $childSlugs = $childMenus->reject(fn ($c) => AdminMenu::isProtected($c))->pluck('slug')->toArray();
        $allTargetIds = array_merge([$menu->id], $childIds);
        $allTargetSlugs = array_merge([$menu->slug], $childSlugs);

        $isCurrentlyDisabled = in_array($menu->id, $disabled, true) || in_array($menu->slug, $disabled, true);

        if ($isCurrentlyDisabled) {
            // Re-enable parent AND all of its children for this user
            $disabled = array_values(array_filter($disabled, function ($val) use ($allTargetIds, $allTargetSlugs) {
                return ! in_array($val, $allTargetIds, true) && ! in_array($val, $allTargetSlugs, true);
            }));
            $status = 'enabled for your session';
            $isActive = true;
        } else {
            // Hide parent AND all of its children for this user
            $disabled = array_values(array_unique(array_merge($disabled, $allTargetIds)));
            $status = 'hidden for your session';
            $isActive = false;
        }

        session(['admin_disabled_menus' => $disabled]);

        if ($request->wantsJson() || $request->ajax()) {
            $totalMenus = AdminMenu::count();
            $disabledCount = count(array_filter($disabled, fn ($d) => is_numeric($d)));
            $activeCount = max(0, $totalMenus - $disabledCount);

            return response()->json([
                'success' => true,
                'is_active' => $isActive,
                'status' => $status,
                'menu_id' => $menu->id,
                'menu_slug' => $menu->slug,
                'child_ids' => $childIds,
                'is_parent' => ! empty($childIds),
                'message' => "Menu '{$menu->title}' is now {$status}.",
                'active_count' => $activeCount,
                'disabled_count' => $disabledCount,
            ]);
        }

        return back()->with('success', "Menu '{$menu->title}' is now {$status}.");
    }

    /**
     * Quick update sort order of a menu item.
     */
    public function updateSort(Request $request, AdminMenu $menu): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $menu->update(['sort_order' => $validated['sort_order']]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sort order for '{$menu->title}' updated to {$menu->sort_order}.",
                'sort_order' => $menu->sort_order,
            ]);
        }

        return back()->with('success', "Sort order for '{$menu->title}' updated to {$menu->sort_order}.");
    }

    /**
     * Re-synchronize all codebase menus and Spatie permissions.
     */
    public function sync(): RedirectResponse
    {
        AdminMenu::syncSystemMenusAndPermissions();

        return redirect()->route('admin.menus.index')
            ->with('success', 'All system menus, modules, and role permissions have been synchronized successfully.');
    }
}
