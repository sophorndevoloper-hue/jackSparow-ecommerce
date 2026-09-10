<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    Permission::firstOrCreate(['name' => 'view dashboard', 'guard_name' => 'backend']);
    Permission::firstOrCreate(['name' => 'approve users', 'guard_name' => 'backend']);
    Permission::firstOrCreate(['name' => 'edit users', 'guard_name' => 'backend']);
});

it('renders the backend admin register screen', function () {
    $response = $this->get(route('admin.register'));

    $response->assertStatus(200);
    $response->assertSee('Create Admin Account');
    $response->assertSee('JackSparow TECH');
});

it('registers an admin as pending approval by default', function () {
    $response = $this->post(route('admin.register'), [
        'name' => 'Pending Admin User',
        'email' => 'newadmin@tech.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('admin.login'));
    $response->assertSessionHas('status');

    $user = User::where('email', 'newadmin@tech.test')->first();
    expect($user)->not->toBeNull();
    expect($user->is_approved)->toBeFalse();
    expect($user->isApproved())->toBeFalse();
    expect($user->roles)->toBeEmpty();
});

it('prevents an unapproved admin user from logging in', function () {
    $user = User::factory()->create([
        'email' => 'unapproved@tech.test',
        'password' => 'password123',
        'is_approved' => false,
    ]);
    $user->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->post(route('admin.login'), [
        'email' => 'unapproved@tech.test',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('backend');
});

it('allows an approved admin user to log in to the admin dashboard', function () {
    $user = User::factory()->create([
        'email' => 'approved@tech.test',
        'password' => 'password123',
        'is_approved' => true,
    ]);
    $user->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->post(route('admin.login'), [
        'email' => 'approved@tech.test',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated('backend');
    $response->assertRedirect(route('admin.dashboard'));
});

it('allows a superadmin to approve a pending admin user from Settings > Users', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('admin', 'backend'));

    $pendingAdmin = User::factory()->create([
        'name' => 'Pending Staff',
        'is_approved' => false,
    ]);
    $pendingAdmin->assignRole(Role::findByName('admin', 'backend'));

    // View in table shows pending approval
    $indexResponse = $this->actingAs($superadmin, 'backend')->get(route('admin.users.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertSee('Pending Approval');

    // Superadmin toggles approval
    $toggleResponse = $this->actingAs($superadmin, 'backend')
        ->patch(route('admin.users.toggle-approval', $pendingAdmin->id));

    $toggleResponse->assertRedirect();
    expect($pendingAdmin->fresh()->is_approved)->toBeTrue();
    expect($pendingAdmin->fresh()->approved_at)->not->toBeNull();

    // Superadmin logs out
    Auth::guard('backend')->logout();

    // Now the approved user can log in!
    $loginResponse = $this->post(route('admin.login'), [
        'email' => $pendingAdmin->email,
        'password' => 'password',
    ]);
    $loginResponse->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticated('backend');
});

it('allows a superadmin to revoke approval for an admin user', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    $activeAdmin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $activeAdmin->assignRole(Role::findByName('admin', 'backend'));

    $toggleResponse = $this->actingAs($superadmin, 'backend')
        ->patch(route('admin.users.toggle-approval', $activeAdmin->id));

    $toggleResponse->assertRedirect();
    expect($activeAdmin->fresh()->is_approved)->toBeFalse();
    expect($activeAdmin->fresh()->approved_at)->toBeNull();
});

it('prevents a superadmin from revoking their own approval', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    $toggleResponse = $this->actingAs($superadmin, 'backend')
        ->patch(route('admin.users.toggle-approval', $superadmin->id));

    $toggleResponse->assertSessionHas('error');
    expect($superadmin->fresh()->is_approved)->toBeTrue();
});

it('prevents a staff user without approve users permission from toggling approval', function () {
    $staff = User::factory()->create(['is_approved' => true]);
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));

    $targetUser = User::factory()->create(['is_approved' => false]);

    $response = $this->actingAs($staff, 'backend')
        ->patch(route('admin.users.toggle-approval', $targetUser->id));

    $response->assertStatus(403);
    expect($targetUser->fresh()->is_approved)->toBeFalse();
});

it('allows a staff user with approve users permission to approve a regular user but not an admin', function () {
    $staff = User::factory()->create(['is_approved' => true]);
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));
    $staff->givePermissionTo(Permission::findByName('approve users', 'backend'));

    $regularUser = User::factory()->create(['is_approved' => false]);
    $adminUser = User::factory()->create(['is_approved' => true]);
    $adminUser->assignRole(Role::findByName('admin', 'backend'));

    // Staff can approve regular user
    $approveResponse = $this->actingAs($staff, 'backend')
        ->patch(route('admin.users.toggle-approval', $regularUser->id));
    $approveResponse->assertRedirect();
    expect($regularUser->fresh()->is_approved)->toBeTrue();

    // Staff CANNOT revoke adminUser (protected)
    $revokeAdminResponse = $this->actingAs($staff, 'backend')
        ->patch(route('admin.users.toggle-approval', $adminUser->id));
    $revokeAdminResponse->assertSessionHas('error');
    expect($adminUser->fresh()->is_approved)->toBeTrue();
});
