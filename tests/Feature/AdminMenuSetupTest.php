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
    $response->assertSee('Products');
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

it('allows updating a menu parent menu and removes section requirement', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $brandsMenu = AdminMenu::where('slug', 'brands')->first();
    $brandsMenu->update(['parent_slug' => null]);
    expect($brandsMenu->parent_slug)->toBeNull();

    // 1. Assign brands to products parent without sending section
    $response = $this->actingAs($admin, 'backend')->put(route('admin.menus.update', $brandsMenu->id), [
        'title' => 'Product Brands',
        'parent_slug' => 'products',
        'icon' => 'bi-award',
        'sort_order' => 25,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.menus.index'));
    $response->assertSessionHas('success');

    $brandsMenu->refresh();
    expect($brandsMenu->parent_slug)->toBe('products');
    expect($brandsMenu->title)->toBe('Product Brands');

    // 2. Remove parent menu (set to top-level) by passing empty string
    $responseTopLevel = $this->actingAs($admin, 'backend')->put(route('admin.menus.update', $brandsMenu->id), [
        'title' => 'Top Level Brands',
        'parent_slug' => '',
        'icon' => 'bi-award',
        'sort_order' => 26,
        'is_active' => true,
    ]);

    $responseTopLevel->assertRedirect(route('admin.menus.index'));
    $brandsMenu->refresh();
    expect($brandsMenu->parent_slug)->toBeNull();
});

it('prevents a menu from selecting itself as its own parent', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $orders = AdminMenu::where('slug', 'orders')->first();

    $response = $this->actingAs($admin, 'backend')->put(route('admin.menus.update', $orders->id), [
        'title' => 'Orders',
        'parent_slug' => 'orders',
        'icon' => 'bi-cart',
        'sort_order' => 40,
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('parent_slug');
});

it('renders menu setup in a unified hierarchy without section category headers', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));
    $response = $this->actingAs($admin, 'backend')->get(route('admin.menus.index'));

    $response->assertStatus(200);
    $response->assertSee('Menus &amp; Navigation Hierarchy', false);
    $response->assertDontSee('Main Section');
    $response->assertDontSee('Inventory Section');
});

it('allows admin to view the add menu page', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.menus.create'));

    $response->assertStatus(200);
    $response->assertSee('Add New Menu');
    $response->assertSee('Parent Menu');
    $response->assertSee('Bootstrap Icon Class');
});

it('allows admin to create a new top-level or child menu item', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // Create a new menu under products
    $response = $this->actingAs($admin, 'backend')->post(route('admin.menus.store'), [
        'title' => 'Product Warranties',
        'parent_slug' => 'products',
        'route_name' => 'admin.products.index',
        'icon' => 'bi-shield-check',
        'sort_order' => 19,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('admin.menus.index'));
    $response->assertSessionHas('success');

    $menu = AdminMenu::where('slug', 'product_warranties')->first();
    expect($menu)->not->toBeNull();
    expect($menu->title)->toBe('Product Warranties');
    expect($menu->parent_slug)->toBe('products');
    expect($menu->icon)->toBe('bi-shield-check');
    expect($menu->sort_order)->toBe(19);
});

it('validates required fields when creating a menu', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->post(route('admin.menus.store'), [
        'title' => '',
        'icon' => '',
        'sort_order' => '',
    ]);

    $response->assertSessionHasErrors(['title', 'icon', 'sort_order']);
});

it('prevents deleting a menu if it has an active route and allows delete after route is cleared', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // Create a custom menu that has a route
    $menu = AdminMenu::create([
        'title' => 'Custom Promo Page',
        'slug' => 'custom_promo_page',
        'route_name' => 'admin.products.index',
        'icon' => 'bi-tag',
        'sort_order' => 88,
        'is_active' => true,
        'actions' => [
            ['name' => 'View', 'action_type' => 'view', 'route_name' => 'admin.products.index'],
        ],
    ]);

    // 1. Attempt delete while route is active -> fails with clear message
    $deleteResponse = $this->actingAs($admin, 'backend')->delete(route('admin.menus.destroy', $menu->id));
    $deleteResponse->assertSessionHas('error');
    expect(session('error'))->toContain("active system route ('admin.products.index')");
    expect(AdminMenu::where('id', $menu->id)->exists())->toBeTrue();

    // 2. Edit menu and clear the route_name
    $updateResponse = $this->actingAs($admin, 'backend')->put(route('admin.menus.update', $menu->id), [
        'title' => 'Custom Promo Page',
        'route_name' => '', // Cleared!
        'icon' => 'bi-tag',
        'sort_order' => 88,
        'is_active' => true,
    ]);

    $updateResponse->assertRedirect(route('admin.menus.index'));
    $menu->refresh();
    expect($menu->route_name)->toBeNull();
    expect($menu->actions)->toBeEmpty();

    // 3. Delete again now that route is cleared -> succeeds!
    $deleteSuccessResponse = $this->actingAs($admin, 'backend')->delete(route('admin.menus.destroy', $menu->id));
    $deleteSuccessResponse->assertRedirect(route('admin.menus.index'));
    $deleteSuccessResponse->assertSessionHas('success');
    expect(AdminMenu::where('id', $menu->id)->exists())->toBeFalse();
});

it('disallows deleting core protected system menus', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $dashboard = AdminMenu::where('slug', 'dashboard')->first();

    $response = $this->actingAs($admin, 'backend')->delete(route('admin.menus.destroy', $dashboard->id));
    $response->assertSessionHas('error');
    expect(AdminMenu::where('slug', 'dashboard')->exists())->toBeTrue();
});

it('orders sidebar navigation strictly by menu sort_order', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $sidebarItems = AdminMenu::getSidebarItems($admin);

    // Verify items in getSidebarItems are ordered strictly in ascending sort_order
    $sortOrders = $sidebarItems->pluck('sort_order')->toArray();
    $sortedCopy = $sortOrders;
    sort($sortedCopy);
    expect($sortOrders)->toEqual($sortedCopy);

    // Check dashboard view renders Manage Products before Manage Stock based on sort_order
    $response = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $response->assertStatus(200);

    $content = $response->getContent();
    $posProducts = strpos($content, 'Manage Products');
    $posStock = strpos($content, 'Manage Stock');

    expect($posProducts)->toBeLessThan($posStock);
});

it('ensures creating a menu does not create duplicates in DB, sidebar, or index table', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // 1. Create a top-level menu
    $response = $this->actingAs($admin, 'backend')->post(route('admin.menus.store'), [
        'title' => 'Discounts & Promos',
        'route_name' => 'admin.products.index',
        'icon' => 'bi-tag',
        'sort_order' => 18,
        'is_active' => true,
    ]);
    $response->assertRedirect(route('admin.menus.index'));

    // Check DB count for this title
    expect(AdminMenu::where('title', 'Discounts & Promos')->count())->toBe(1);

    // Check sidebar items
    $sidebar = AdminMenu::getSidebarItems($admin);
    $sidebarMatches = $sidebar->filter(fn ($item) => $item['title'] === 'Discounts & Promos');
    expect($sidebarMatches->count())->toBe(1);

    // Check rendered index page
    $indexResponse = $this->actingAs($admin, 'backend')->get(route('admin.menus.index'));
    $indexResponse->assertStatus(200);
    $html = $indexResponse->getContent();

    $tbody = substr($html, strpos($html, '<tbody>'), strpos($html, '</tbody>') - strpos($html, '<tbody>'));
    $rowIdCount = substr_count($tbody, 'id="parent-row-discounts_promos"');
    expect($rowIdCount)->toBe(1);

    // 2. Create a child menu under Manage Products
    $response2 = $this->actingAs($admin, 'backend')->post(route('admin.menus.store'), [
        'title' => 'Special Child Item',
        'parent_slug' => 'products',
        'route_name' => 'admin.products.index',
        'icon' => 'bi-circle',
        'sort_order' => 19,
        'is_active' => true,
    ]);
    $response2->assertRedirect(route('admin.menus.index'));

    // Check DB count for this child title
    expect(AdminMenu::where('title', 'Special Child Item')->count())->toBe(1);

    // Check sidebar: should only appear once inside products' sub_items
    $sidebar2 = AdminMenu::getSidebarItems($admin);
    $prodMenu = $sidebar2->firstWhere('slug', 'products');
    expect($prodMenu)->not->toBeNull();
    $childMatches = collect($prodMenu['sub_items'])->filter(fn ($sub) => $sub['title'] === 'Special Child Item');
    expect($childMatches->count())->toBe(1);

    // Verify it is NOT also a top-level item in the sidebar
    $topLevelMatches = $sidebar2->filter(fn ($item) => $item['title'] === 'Special Child Item');
    expect($topLevelMatches->count())->toBe(0);

    // 3. Attempting to create a duplicate menu with the same title under the same parent must fail validation
    $duplicateResponse = $this->actingAs($admin, 'backend')->post(route('admin.menus.store'), [
        'title' => 'Special Child Item',
        'parent_slug' => 'products',
        'route_name' => 'admin.products.index',
        'icon' => 'bi-circle',
        'sort_order' => 19,
        'is_active' => true,
    ]);
    $duplicateResponse->assertSessionHasErrors(['title']);

    // 4. Attempting to create a duplicate top-level menu with the same title must fail validation
    $duplicateTopResponse = $this->actingAs($admin, 'backend')->post(route('admin.menus.store'), [
        'title' => 'Discounts & Promos',
        'route_name' => 'admin.products.index',
        'icon' => 'bi-tag',
        'sort_order' => 18,
        'is_active' => true,
    ]);
    $duplicateTopResponse->assertSessionHasErrors(['title']);

    // Ensure database still has exactly 1 of each
    expect(AdminMenu::where('title', 'Discounts & Promos')->count())->toBe(1);
    expect(AdminMenu::where('title', 'Special Child Item')->count())->toBe(1);
});
