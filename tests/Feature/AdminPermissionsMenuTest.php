<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Ensure roles and permissions are initialized for backend guard
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'backend']);

    $permissions = [
        'view dashboard',
        'view products',
        'create products',
        'edit products',
        'delete products',
        'view categories',
        'create categories',
        'edit categories',
        'delete categories',
        'view brands',
        'create brands',
        'edit brands',
        'delete brands',
        'view makes',
        'create makes',
        'edit makes',
        'delete makes',
        'view warehouses',
        'create warehouses',
        'edit warehouses',
        'delete warehouses',
        'view orders',
        'edit orders',
        'delete orders',
        'view customers',
        'create customers',
        'edit customers',
        'delete customers',
        'view customer groups',
        'create customer groups',
        'edit customer groups',
        'delete customer groups',
        'view suppliers',
        'create suppliers',
        'edit suppliers',
        'delete suppliers',
        'manage suppliers',
        'view users',
        'edit users',
        'approve users',
        'delete users',
        'manage users',
        'view roles',
        'create roles',
        'edit roles',
        'delete roles',
        'manage roles',
        'view menus',
        'edit menus',
        'view settings',
        'edit settings',
        'view stock',
        'create stock',
        'edit stock',
        'delete stock',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'backend']);
    }

    $allBackend = Permission::where('guard_name', 'backend')->get();
    $adminRole->syncPermissions($allBackend);
    $superadminRole->syncPermissions($allBackend);
});

it('grants admin and superadmin access to all menus and actions', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // Admin can access all functional areas
    $this->actingAs($admin, 'backend')->get(route('admin.dashboard'))->assertStatus(200);
    $this->actingAs($admin, 'backend')->get(route('admin.products.index'))->assertStatus(200);
    $this->actingAs($admin, 'backend')->get(route('admin.orders.index'))->assertStatus(200);
    $this->actingAs($admin, 'backend')->get(route('admin.customers.index'))->assertStatus(200);
    $this->actingAs($admin, 'backend')->get(route('admin.suppliers.index'))->assertStatus(200);
    $this->actingAs($admin, 'backend')->get(route('admin.users.index'))->assertStatus(200);

    // Sidebar shows all menus for admin
    $dashboardResponse = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $dashboardResponse->assertSee('All Products');
    $dashboardResponse->assertSee('Orders');
    $dashboardResponse->assertSee('Customers (Simple/VIP)');
    $dashboardResponse->assertSee('Suppliers');
    $dashboardResponse->assertSee('Users');
});

it('protects menu actions via middleware when user does not have permission', function () {
    $staff = User::factory()->create(['is_approved' => true]);
    // Staff only has 'view dashboard' and 'view products'
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));
    $staff->givePermissionTo(Permission::findByName('view products', 'backend'));

    // Allowed: Products
    $this->actingAs($staff, 'backend')->get(route('admin.products.index'))->assertStatus(200);

    // Forbidden: Orders (403)
    $this->actingAs($staff, 'backend')->get(route('admin.orders.index'))->assertStatus(403);

    // Forbidden: Customers (403)
    $this->actingAs($staff, 'backend')->get(route('admin.customers.index'))->assertStatus(403);

    // Forbidden: Users & Settings (403)
    $this->actingAs($staff, 'backend')->get(route('admin.users.index'))->assertStatus(403);
});

it('hides forbidden menus from the sidebar for restricted users', function () {
    $staff = User::factory()->create(['is_approved' => true]);
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));
    $staff->givePermissionTo(Permission::findByName('view products', 'backend'));

    $response = $this->actingAs($staff, 'backend')->get(route('admin.dashboard'));
    $response->assertStatus(200);

    // Should see products
    $response->assertSee('All Products');

    // Should NOT see Orders, Customers, Suppliers, Users, Roles, Menus
    $response->assertDontSee('Sales & Fulfillment');
    $response->assertDontSee('Customers (Simple/VIP)');
    $response->assertDontSee('Suppliers');
    $response->assertDontSee('Menu Setup');
    $response->assertDontSee('System Roles');
});

it('prevents non-superadmin from editing permissions of an admin or superadmin', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $staff = User::factory()->create(['is_approved' => true]);
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));
    $staff->givePermissionTo(Permission::findByName('view users', 'backend'));
    $staff->givePermissionTo(Permission::findByName('edit users', 'backend'));

    // Staff cannot edit admin permissions (403)
    $this->actingAs($staff, 'backend')
        ->get(route('admin.users.edit', $admin->id))
        ->assertStatus(403);

    $this->actingAs($staff, 'backend')
        ->put(route('admin.users.update', $admin->id), ['roles' => ['admin']])
        ->assertStatus(403);

    // Admin also cannot edit superadmin permissions (403)
    $this->actingAs($admin, 'backend')
        ->get(route('admin.users.edit', $superadmin->id))
        ->assertStatus(403);

    // SuperAdmin CAN edit admin permissions
    $this->actingAs($superadmin, 'backend')
        ->get(route('admin.users.edit', $admin->id))
        ->assertStatus(200);
});

it('allows admin to delete other users but prevents deleting self or superadmin', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $staffUser = User::factory()->create(['is_approved' => true]);

    // Admin deletes staffUser
    $deleteResponse = $this->actingAs($admin, 'backend')
        ->delete(route('admin.users.destroy', $staffUser->id));

    $deleteResponse->assertRedirect(route('admin.users.index'));
    expect(User::find($staffUser->id))->toBeNull();

    // Admin cannot delete themselves
    $selfDeleteResponse = $this->actingAs($admin, 'backend')
        ->delete(route('admin.users.destroy', $admin->id));
    $selfDeleteResponse->assertSessionHas('error');
    expect(User::find($admin->id))->not->toBeNull();

    // Admin cannot delete superadmin
    $superDeleteResponse = $this->actingAs($admin, 'backend')
        ->delete(route('admin.users.destroy', $superadmin->id));
    $superDeleteResponse->assertSessionHas('error');
    expect(User::find($superadmin->id))->not->toBeNull();
});

it('allows a user with only direct permissions to log in and access the dashboard without access denied', function () {
    $tester = User::factory()->create([
        'email' => 'tester_direct@gmail.com',
        'password' => Hash::make('password123'),
        'is_approved' => true,
    ]);
    // Assign only direct permission, NO roles
    $tester->givePermissionTo(Permission::findByName('view dashboard', 'backend'));

    expect($tester->roles()->count())->toBe(0);
    expect($tester->permissions()->count())->toBe(1);

    // Login via admin.login
    $response = $this->post(route('admin.login'), [
        'email' => 'tester_direct@gmail.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated('backend');
    $response->assertRedirect(route('admin.dashboard'));

    // Access dashboard
    $dashboardResponse = $this->actingAs($tester, 'backend')->get(route('admin.dashboard'));
    $dashboardResponse->assertStatus(200);
    $dashboardResponse->assertSee('Dashboard');
});

it('renders each backend permission only once on the user edit screen without duplicates', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    $targetUser = User::factory()->create(['is_approved' => true]);

    $response = $this->actingAs($superadmin, 'backend')
        ->get(route('admin.users.edit', $targetUser->id));

    $response->assertStatus(200);

    // View data permissions
    $permissions = $response->original->getData()['permissions'];
    $permissionNames = $permissions->pluck('name')->toArray();

    // Check no duplicate names
    expect(count($permissionNames))->toBe(count(array_unique($permissionNames)));
});

it('prevents users with only view permissions from creating, updating, or deleting', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $viewer = User::factory()->create(['is_approved' => true]);
    $viewer->givePermissionTo(Permission::findByName('view products', 'backend'));
    $viewer->givePermissionTo(Permission::findByName('view categories', 'backend'));
    $viewer->givePermissionTo(Permission::findByName('view brands', 'backend'));

    // Viewer can view products list (200)
    $this->actingAs($viewer, 'backend')
        ->get(route('admin.products.index'))
        ->assertStatus(200)
        ->assertDontSee('Add Computer Part')
        ->assertDontSee('title="Edit Component"', false)
        ->assertDontSee('title="Delete"', false);

    // Cannot access create form (403)
    $this->actingAs($viewer, 'backend')
        ->get(route('admin.products.create'))
        ->assertStatus(403);

    // Cannot post store (403)
    $this->actingAs($viewer, 'backend')
        ->post(route('admin.products.store'), [
            'name' => 'Unauthorized Product',
            'sku' => 'UNAUTH-001',
            'price' => 100,
            'stock_quantity' => 5,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ])
        ->assertStatus(403);

    // Cannot access edit form (403)
    $this->actingAs($viewer, 'backend')
        ->get(route('admin.products.edit', $product->id))
        ->assertStatus(403);

    // Cannot update (403)
    $this->actingAs($viewer, 'backend')
        ->put(route('admin.products.update', $product->id), [
            'name' => 'Updated Hack',
        ])
        ->assertStatus(403);

    // Cannot delete (403)
    $this->actingAs($viewer, 'backend')
        ->delete(route('admin.products.destroy', $product->id))
        ->assertStatus(403);

    // Categories: cannot create, edit, or delete (403)
    $this->actingAs($viewer, 'backend')->get(route('admin.categories.create'))->assertStatus(403);
    $this->actingAs($viewer, 'backend')->get(route('admin.categories.edit', $category->id))->assertStatus(403);
    $this->actingAs($viewer, 'backend')->delete(route('admin.categories.destroy', $category->id))->assertStatus(403);

    // Brands: cannot create, edit, or delete (403)
    $this->actingAs($viewer, 'backend')->get(route('admin.brands.create'))->assertStatus(403);
    $this->actingAs($viewer, 'backend')->get(route('admin.brands.edit', $brand->id))->assertStatus(403);
    $this->actingAs($viewer, 'backend')->delete(route('admin.brands.destroy', $brand->id))->assertStatus(403);
});

it('ensures superadmin role cannot be assigned to another user', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    // 1. Attempt assigning superadmin role to existing user via update
    $otherUser = User::factory()->create(['is_approved' => true]);
    $otherUser->assignRole(Role::findByName('admin', 'backend'));

    $this->actingAs($superadmin, 'backend')->put(route('admin.users.update', $otherUser->id), [
        'roles' => ['superadmin', 'admin'],
    ]);

    $otherUser->refresh();
    expect($otherUser->hasRole('superadmin', 'backend'))->toBeFalse();

    // 3. Superadmin updating their own account preserves superadmin role
    $this->actingAs($superadmin, 'backend')->put(route('admin.users.update', $superadmin->id), [
        'roles' => ['admin'],
    ]);

    $superadmin->refresh();
    expect($superadmin->hasRole('superadmin', 'backend'))->toBeTrue();
});

it('protects superadmin user and system core roles from deletion', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // Attempt deleting superadmin user
    $response = $this->actingAs($admin, 'backend')->delete(route('admin.users.destroy', $superadmin->id));
    $response->assertSessionHas('error');
    expect(User::find($superadmin->id))->not()->toBeNull();

    // Attempt deleting superadmin role
    $superadminRole = Role::findByName('superadmin', 'backend');
    $this->actingAs($superadmin, 'backend')->delete(route('admin.roles.destroy', $superadminRole->id))
        ->assertSessionHas('error');
    expect(Role::where('name', 'superadmin')->exists())->toBeTrue();

    // Attempt deleting admin role
    $adminRole = Role::findByName('admin', 'backend');
    $this->actingAs($superadmin, 'backend')->delete(route('admin.roles.destroy', $adminRole->id))
        ->assertSessionHas('error');
    expect(Role::where('name', 'admin')->exists())->toBeTrue();
});

it('does not display or allow creating customer or duplicate superadmin role', function () {
    $superadmin = User::factory()->create(['is_approved' => true]);
    $superadmin->assignRole(Role::findByName('superadmin', 'backend'));

    // Ensure customer role is not visible in roles index
    $this->actingAs($superadmin, 'backend')->get(route('admin.roles.index'))
        ->assertStatus(200)
        ->assertDontSee('Customer Role')
        ->assertDontSee('guard: frontend');

    // Attempt creating role named customer (rejected by validation)
    $this->actingAs($superadmin, 'backend')->post(route('admin.roles.store'), [
        'name' => 'customer',
        'guard_name' => 'backend',
    ])->assertSessionHasErrors(['name']);

    // Attempt creating duplicate role named superadmin (rejected by validation)
    $this->actingAs($superadmin, 'backend')->post(route('admin.roles.store'), [
        'name' => 'superadmin',
        'guard_name' => 'backend',
    ])->assertSessionHasErrors(['name']);
});

it('dynamically redirects staff user with view products to products index instead of throwing 403 on dashboard', function () {
    $productViewer = User::factory()->create(['is_approved' => true]);
    $productViewer->givePermissionTo(Permission::findByName('view products', 'backend'));

    // Visiting /admin should gracefully redirect to /admin/products instead of 403
    $this->actingAs($productViewer, 'backend')
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.products.index'));

    // Directly visiting products index succeeds with 200
    $this->actingAs($productViewer, 'backend')
        ->get(route('admin.products.index'))
        ->assertStatus(200)
        ->assertSee('Hardware Products');
});

it('allows any authenticated admin user to clear system cache without permissions', function () {
    $adminUser = User::factory()->create(['is_approved' => true]);

    $response = $this->actingAs($adminUser, 'backend')->post(route('admin.cache.clear'));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

it('protects stock and serial number management with dedicated stock permissions', function () {
    $user = User::factory()->create(['is_approved' => true]);

    // Without permissions, user cannot access stock or serial numbers
    $this->actingAs($user, 'backend')->get(route('admin.stock.index'))->assertStatus(403);
    $this->actingAs($user, 'backend')->get(route('admin.serial-numbers.index'))->assertStatus(403);

    // Give view stock permission
    $user->givePermissionTo(Permission::findByName('view stock', 'backend'));
    $this->actingAs($user, 'backend')->get(route('admin.stock.index'))->assertStatus(200);
    $this->actingAs($user, 'backend')->get(route('admin.serial-numbers.index'))->assertStatus(200);
});
