<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): View
    {
        $query = Role::where('guard_name', 'backend')
            ->where('name', '!=', 'customer')
            ->with(['users.profile', 'permissions'])
            ->withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'ilike', "%{$search}%");
        }

        $roles = $query->orderBy('name')->get();

        return view('backend.settings.roles.index', compact('roles'));
    }

    /**
     * Display details of a specific role.
     */
    public function show(Role $role): View
    {
        $role->load(['users', 'permissions']);

        return view('backend.settings.roles.show', compact('role'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $permissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();

        $groupedPermissions = AdminMenu::groupPermissionsDynamically($permissions);

        return view('backend.settings.roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'not_in:customer,superadmin'],
            'guard_name' => ['required', 'string', 'in:backend,frontend,web'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ], [
            'name.not_in' => 'The superadmin and customer roles cannot be created.',
        ]);

        $role = Role::firstOrCreate([
            'name' => $request->input('name'),
            'guard_name' => $request->input('guard_name', 'backend'),
        ]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' [{$role->guard_name}] created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): View
    {
        $permissions = Permission::where('guard_name', $role->guard_name)->orderBy('name')->get();
        if ($permissions->isEmpty()) {
            $permissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();
        }

        $groupedPermissions = AdminMenu::groupPermissionsDynamically($permissions);

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('backend.settings.roles.edit', compact('role', 'permissions', 'groupedPermissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'not_in:customer'],
            'guard_name' => ['required', 'string', 'in:backend,frontend,web'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if (! in_array($role->name, ['superadmin', 'admin'])) {
            $role->name = $request->input('name');
            $role->guard_name = $request->input('guard_name');
            $role->save();
        }

        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->name}' [{$role->guard_name}] updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['superadmin', 'admin', 'customer'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', "System core role '{$role->name}' cannot be deleted.");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
