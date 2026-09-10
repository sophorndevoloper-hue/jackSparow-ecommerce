<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of system permissions.
     */
    public function index(Request $request): View
    {
        $query = Permission::with(['roles', 'users']);

        if ($request->filled('guard')) {
            $query->where('guard_name', $request->input('guard'));
        }

        $permissions = $query->orderBy('guard_name')->orderBy('name')->get();

        $groupedPermissions = AdminMenu::groupPermissionsDynamically($permissions);
        $actionRoutes = AdminMenu::getPermissionActionRouteMap();

        return view('backend.settings.permissions.index', compact('permissions', 'groupedPermissions', 'actionRoutes'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): View
    {
        return view('backend.settings.permissions.create');
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'guard_name' => ['required', 'string', 'in:backend,frontend,web'],
            'action_route' => ['nullable', 'string', 'max:255'],
        ]);

        $permission = Permission::firstOrCreate([
            'name' => strtolower(trim($request->input('name'))),
            'guard_name' => $request->input('guard_name', 'backend'),
        ]);

        if ($request->filled('action_route')) {
            $permission->action_route = trim($request->input('action_route'));
            $permission->save();
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$permission->name}' [{$permission->guard_name}] created successfully.");
    }
}
