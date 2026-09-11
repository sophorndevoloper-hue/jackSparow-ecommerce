<?php

use App\Models\AdminMenu;
use App\Models\User;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(BackendMenuSeeder::class);
});

it('allows admin and superadmin to view the menu setup dashboard', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.menus.index'));

    $response->assertStatus(200);
    $response->assertSee('Menu &amp; Action Setup', false);
    $response->assertSee('All Products');
    $response->assertSee('Categories');
    $response->assertSee('Brands');
    $response->assertSee('Orders');
    $response->assertSee('Customers');
    $response->assertSee('Suppliers');
    $response->assertSee('Settings');
    $response->assertSee('Menu Setup');
});

it('forbids users without view settings permission from accessing menu setup', function () {
    $staff = User::factory()->create(['is_approved' => true]);

    $response = $this->actingAs($staff, 'backend')->get(route('admin.menus.index'));

    $response->assertStatus(403);
});

it('allows admin to edit and update menu properties', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $menu = AdminMenu::where('slug', 'products')->first();

    // Access edit view
    $editResponse = $this->actingAs($admin, 'backend')->get(route('admin.menus.edit', $menu->id));
    $editResponse->assertStatus(200);
    $editResponse->assertSee('Edit Menu: Manage Products');

    // Update menu title and icon
    $updateResponse = $this->actingAs($admin, 'backend')->put(route('admin.menus.update', $menu->id), [
        'title' => 'Hardware Inventory Catalog',
        'section' => 'Inventory',
        'icon' => 'bi-pc-display',
        'view_permission' => 'view products',
        'sort_order' => 15,
        'is_active' => true,
    ]);

    $updateResponse->assertRedirect(route('admin.menus.index'));
    $updateResponse->assertSessionHas('success');

    $menu->refresh();
    expect($menu->title)->toBe('Hardware Inventory Catalog');
    expect($menu->icon)->toBe('bi-pc-display');
    expect($menu->sort_order)->toBe(15);
});

it('allows admin to toggle menu visibility per user session without affecting other users', function () {
    $adminA = User::factory()->create(['is_approved' => true]);
    $adminA->assignRole(Role::findByName('admin', 'backend'));

    $adminB = User::factory()->create(['is_approved' => true]);
    $adminB->assignRole(Role::findByName('admin', 'backend'));

    $categoriesMenu = AdminMenu::where('slug', 'categories')->first();

    // Verify Categories is visible in sidebar for User A
    $dashboardBefore = $this->actingAs($adminA, 'backend')->get(route('admin.dashboard'));
    $dashboardBefore->assertSee('Categories');

    // User A hides Categories
    $toggleResponse = $this->actingAs($adminA, 'backend')
        ->patch(route('admin.menus.toggle-active', $categoriesMenu->id));

    $toggleResponse->assertRedirect();
    $toggleResponse->assertSessionHas('success');

    // Verify Categories is now hidden for User A
    $dashboardAfterUserA = $this->actingAs($adminA, 'backend')->get(route('admin.dashboard'));
    $dashboardAfterUserA->assertDontSee('href="'.route('admin.categories.index').'"', false);

    // Verify Categories is STILL visible for User B
    $this->flushSession();
    $dashboardUserB = $this->actingAs($adminB, 'backend')->get(route('admin.dashboard'));
    $dashboardUserB->assertSee('Categories');
});

it('allows admin to quickly update menu sort order via dedicated sort endpoint', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $menu = AdminMenu::where('slug', 'brands')->first();

    $response = $this->actingAs($admin, 'backend')
        ->patch(route('admin.menus.sort', $menu->id), [
            'sort_order' => 99,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $menu->refresh();
    expect($menu->sort_order)->toBe(99);

    // JSON request test
    $jsonResponse = $this->actingAs($admin, 'backend')
        ->patchJson(route('admin.menus.sort', $menu->id), [
            'sort_order' => 5,
        ]);

    $jsonResponse->assertStatus(200);
    $jsonResponse->assertJson([
        'success' => true,
        'sort_order' => 5,
    ]);

    $menu->refresh();
    expect($menu->sort_order)->toBe(5);
});

it('disallows hiding core protected menus like menu_setup and settings', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $menuSetup = AdminMenu::where('slug', 'menu_setup')->first();
    $settings = AdminMenu::where('slug', 'settings')->first();

    // Try hiding Menu Setup via JSON
    $response = $this->actingAs($admin, 'backend')
        ->patchJson(route('admin.menus.toggle-active', $menuSetup->id));

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);

    // Try hiding Settings via regular form request
    $formResponse = $this->actingAs($admin, 'backend')
        ->patch(route('admin.menus.toggle-active', $settings->id));

    $formResponse->assertSessionHas('error');

    // Verify Menu Setup is STILL visible in sidebar
    $dashboard = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $dashboard->assertSee('Menu Setup');
});

it('allows restoring all hidden menus via reset-visibility endpoint', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $categories = AdminMenu::where('slug', 'categories')->first();

    // Hide categories
    $this->actingAs($admin, 'backend')
        ->patch(route('admin.menus.toggle-active', $categories->id));

    $dashboardBefore = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $dashboardBefore->assertDontSee('href="'.route('admin.categories.index').'"', false);

    // Call reset-visibility
    $resetResponse = $this->actingAs($admin, 'backend')
        ->post(route('admin.menus.reset-visibility'));

    $resetResponse->assertRedirect();
    $resetResponse->assertSessionHas('success');

    // Verify categories is visible again
    $dashboardAfter = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $dashboardAfter->assertSee('href="'.route('admin.categories.index').'"', false);
});
