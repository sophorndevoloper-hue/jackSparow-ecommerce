<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Models\AdminMenu;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users with their roles and permissions.
     */
    public function index(Request $request, UserDataTable $dataTable): mixed
    {
        if ($request->ajax()) {
            return $dataTable->ajax();
        }

        $query = User::query()->with(['profile', 'roles.permissions', 'permissions', 'orders']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('guard')) {
            $guard = $request->input('guard');
            $query->whereHas('roles', fn ($q) => $q->where('guard_name', $guard));
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        $users = $query->latest('updated_at')->paginate(15)->withQueryString();
        $roles = Role::where('guard_name', 'backend')->orderBy('name')->get();

        return view('backend.settings.users.index', compact('users', 'roles', 'dataTable'));
    }

    /**
     * Display detailed access and profile information for a user.
     */
    public function show(User $user): View
    {
        $user->load(['profile', 'roles.permissions', 'permissions', 'orders.items']);

        return view('backend.settings.users.show', compact('user'));
    }

    /**
     * Show the form for editing user roles and directly applied permissions.
     */
    public function edit(User $user): View
    {
        $user->load('profile');
        $authUser = auth('backend')->user();
        $isTargetAdmin = $user->hasRole('admin', 'backend') || $user->hasRole('superadmin', 'backend');
        $isTargetSuperAdmin = $user->hasRole('superadmin', 'backend');

        // Other users cannot edit permissions of an admin or superadmin
        if ($isTargetAdmin && ! ($authUser && $authUser->hasRole('superadmin', 'backend'))) {
            abort(403, 'Unauthorized: You do not have permission to edit permissions or roles of an administrator or superadmin.');
        }

        // Superadmin role cannot be applied to another user (only the existing superadmin can have it)
        $rolesQuery = Role::where('guard_name', 'backend');
        if (! $isTargetSuperAdmin) {
            $rolesQuery->where('name', '!=', 'superadmin');
        }
        $roles = $rolesQuery->get();

        $permissions = Permission::where('guard_name', 'backend')->orderBy('name')->get();

        // Group permissions by functional module for clean UI presentation
        $groupedPermissions = AdminMenu::groupPermissionsDynamically($permissions);

        $userDirectPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        $userRolePermissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();

        return view('backend.settings.users.edit', compact(
            'user',
            'roles',
            'permissions',
            'groupedPermissions',
            'userDirectPermissions',
            'userRolePermissions',
        ));
    }

    /**
     * Update user roles and direct permissions.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $authUser = auth('backend')->user();
        $isTargetAdmin = $user->hasRole('admin', 'backend') || $user->hasRole('superadmin', 'backend');
        $isTargetSuperAdmin = $user->hasRole('superadmin', 'backend');

        // Other users cannot edit permissions of an admin or superadmin
        if ($isTargetAdmin && ! ($authUser && $authUser->hasRole('superadmin', 'backend'))) {
            abort(403, 'Unauthorized: You do not have permission to edit permissions or roles of an administrator or superadmin.');
        }

        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $rolesInput = $request->input('roles', []);
        $permsInput = $request->input('permissions', []);

        // Superadmin role cannot be applied to another user
        if ($isTargetSuperAdmin) {
            if (! in_array('superadmin', $rolesInput)) {
                $rolesInput[] = 'superadmin';
            }
        } else {
            $rolesInput = array_diff($rolesInput, ['superadmin']);
        }

        // Sync assigned backend roles
        $backendRoles = Role::where('guard_name', 'backend')->whereIn('name', $rolesInput)->get();
        $user->syncRoles($backendRoles);

        // Sync directly applied backend permissions
        $backendPerms = Permission::where('guard_name', 'backend')->whereIn('name', $permsInput)->get();
        $user->syncPermissions($backendPerms);

        return redirect()->route('admin.users.index')
            ->with('success', "Roles and permissions updated successfully for {$user->name}.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        $authUser = auth('backend')->user();
        $isSuperAdmin = $authUser && $authUser->hasRole('superadmin', 'backend');
        $isAdmin = $authUser && ($isSuperAdmin || $authUser->hasRole('admin', 'backend'));

        // Prevent self-deletion
        if ($authUser && $user->id === $authUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // The primary superadmin account cannot be deleted
        if ($user->hasRole('superadmin', 'backend')) {
            return back()->with('error', 'The primary superadmin account cannot be deleted.');
        }

        // Only superadmin can delete an admin account
        $isTargetProtected = $user->hasRole('admin', 'backend');
        if ($isTargetProtected && ! $isSuperAdmin) {
            return back()->with('error', 'You do not have permission to delete an administrator account.');
        }

        // Only admins and superadmins can delete users
        if (! $isAdmin) {
            abort(403, 'Unauthorized: Only administrators can delete users.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' has been deleted successfully.");
    }

    /**
     * Toggle administrator approval status for the given user.
     */
    public function toggleApproval(User $user): RedirectResponse
    {
        $authUser = auth('backend')->user();
        $isSuperAdmin = $authUser && $authUser->hasRole('superadmin', 'backend');
        $isAdmin = $authUser && ($isSuperAdmin || $authUser->hasRole('admin', 'backend'));
        $hasApprovePermission = $authUser && $authUser->can('approve users');

        // 1. Only admin, superadmin, or users explicitly granted 'approve users' permission can approve/revoke
        if (! ($isAdmin || $hasApprovePermission)) {
            abort(403, 'Unauthorized: You do not have permission to approve or revoke users.');
        }

        // 2. Prevent altering self
        if ($authUser && $user->id === $authUser->id) {
            return back()->with('error', 'You cannot alter your own administrator approval status.');
        }

        // 3. Superadmin account can NEVER be revoked by anyone other than a superadmin
        if ($user->hasRole('superadmin', 'backend') && ! $isSuperAdmin) {
            return back()->with('error', 'You do not have permission to alter approval for a superadmin account.');
        }

        // 4. Admin accounts are protected: other users (non-admins) cannot revoke an admin
        if ($user->hasRole('admin', 'backend') && ! $isAdmin) {
            return back()->with('error', 'Only administrators or superadmins can alter approval for an administrator account.');
        }

        if ($user->is_approved) {
            $user->revokeApproval();
            $status = 'revoked';
        } else {
            $user->approve();
            $status = 'approved';
        }

        return back()->with('success', "Approval status for '{$user->name}' has been {$status}.");
    }
}
