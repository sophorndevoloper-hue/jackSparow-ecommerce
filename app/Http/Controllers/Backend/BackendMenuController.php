<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $menu->is_active_for_session = ! in_array($menu->id, $disabledMenuIds, true) && ! in_array($menu->slug, $disabledMenuIds, true);
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
     * Show the form for editing the specified menu.
     */
    public function edit(AdminMenu $menu): View
    {
        $availablePermissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();
        $roles = Role::where('guard_name', 'backend')->with('permissions')->get();

        return view('backend.settings.menus.edit', compact('menu', 'availablePermissions', 'roles'));
    }

    /**
     * Update the specified menu in storage.
     */
    public function update(Request $request, AdminMenu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'section' => ['required', 'string', 'max:100'],
            'icon' => ['required', 'string', 'max:50'],
            'view_permission' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $menu->update($validated);

        return redirect()->route('admin.menus.index')
            ->with('success', "Menu '{$menu->title}' updated successfully.");
    }

    /**
     * Toggle the active/inactive status of a menu item for the current user session.
     * When toggling a parent menu, all child menus are also automatically toggled.
     */
    public function toggleActive(Request $request, AdminMenu $menu): RedirectResponse|JsonResponse
    {
        $disabled = session('admin_disabled_menus', []);

        // Find any child menus belonging to this menu if it is a parent
        $childIds = AdminMenu::where('parent_slug', $menu->slug)->pluck('id')->toArray();
        $childSlugs = AdminMenu::where('parent_slug', $menu->slug)->pluck('slug')->toArray();
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
