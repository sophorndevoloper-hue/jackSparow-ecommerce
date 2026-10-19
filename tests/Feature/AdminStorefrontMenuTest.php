<?php

use App\Models\StorefrontMenu;
use App\Models\User;
use App\Models\UserActivity;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\StorefrontMenuSeeder;
use Database\Seeders\WebsiteSettingSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    (new RoleAndPermissionSeeder)->run();
    (new BackendMenuSeeder)->run();
    (new WebsiteSettingSeeder)->run();
    (new StorefrontMenuSeeder)->run();
});

test('admin can view storefront menu management tabs', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    // Main location default
    $response = $this->actingAs($admin, 'backend')
        ->get(route('admin.storefront-menus.index', ['location' => 'main']));
    $response->assertOk()
        ->assertSee('Storefront Website Menus')
        ->assertSee('Main Navbar Menu')
        ->assertSee('PC Components')
        ->assertSee('Processors (CPUs)');

    // Top utility location
    $topResp = $this->actingAs($admin, 'backend')
        ->get(route('admin.storefront-menus.index', ['location' => 'top']));
    $topResp->assertOk()
        ->assertSee('Top Utility Menu (Bar)')
        ->assertSee('About Us')
        ->assertSee('Contact Support');

    // Footer location
    $footerResp = $this->actingAs($admin, 'backend')
        ->get(route('admin.storefront-menus.index', ['location' => 'footer']));
    $footerResp->assertOk()
        ->assertSee('Footer Columns Menu')
        ->assertSee('Company Info')
        ->assertSee('Popular Hardware');
});

test('admin can create parent and child menu items with activity logging', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    // 1. Create top-level parent menu item
    $parentResp = $this->actingAs($admin, 'backend')
        ->post(route('admin.storefront-menus.store'), [
            'location' => 'main',
            'title' => 'Custom Watercooling',
            'url' => '/shop?category=cooling-systems',
            'target' => '_self',
            'icon' => 'bi-droplet-half',
            'badge' => 'CUSTOM',
            'sort_order' => 10,
            'is_active' => '1',
        ]);
    $parentResp->assertRedirect(route('admin.storefront-menus.index', ['location' => 'main']));

    $parent = StorefrontMenu::where('title', 'Custom Watercooling')->first();
    expect($parent)->not->toBeNull();
    expect($parent->location)->toBe('main');
    expect($parent->parent_id)->toBeNull();

    // 2. Create child menu item linked to the parent
    $childResp = $this->actingAs($admin, 'backend')
        ->post(route('admin.storefront-menus.store'), [
            'location' => 'main',
            'parent_id' => $parent->id,
            'title' => 'Hardline Tubing Kits',
            'url' => '/shop?category=tubing-kits',
            'target' => '_self',
            'icon' => 'bi-funnel',
            'sort_order' => 1,
            'is_active' => '1',
        ]);
    $childResp->assertRedirect(route('admin.storefront-menus.index', ['location' => 'main']));

    $child = StorefrontMenu::where('title', 'Hardline Tubing Kits')->first();
    expect($child)->not->toBeNull();
    expect($child->parent_id)->toBe($parent->id);

    // Verify activity logged
    $latestActivity = UserActivity::latest()->first();
    expect($latestActivity)->not->toBeNull();
    expect($latestActivity->module)->toBe('Website Menus');
    expect($latestActivity->action)->toBe('CREATE');
});

test('admin can update storefront menu item and toggle status', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $menu = StorefrontMenu::where('title', 'About Us')->where('location', 'top')->firstOrFail();

    // Update
    $updateResp = $this->actingAs($admin, 'backend')
        ->put(route('admin.storefront-menus.update', $menu), [
            'title' => 'About Our Company',
            'url' => '/about-us',
            'target' => '_blank',
            'icon' => 'bi-building',
            'badge' => 'INFO',
            'sort_order' => 5,
            'is_active' => '1',
        ]);
    $updateResp->assertRedirect(route('admin.storefront-menus.index', ['location' => 'top']));

    $menu->refresh();
    expect($menu->title)->toBe('About Our Company');
    expect($menu->target)->toBe('_blank');
    expect($menu->badge)->toBe('INFO');

    // Toggle active
    $toggleResp = $this->actingAs($admin, 'backend')
        ->patch(route('admin.storefront-menus.toggle-active', $menu));
    $toggleResp->assertRedirect();

    $menu->refresh();
    expect($menu->is_active)->toBeFalse();
});

test('admin can delete menu item and cascading children', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $parent = StorefrontMenu::where('title', 'PC Components')->firstOrFail();
    $childrenCount = $parent->allChildren()->count();
    expect($childrenCount)->toBeGreaterThan(0);

    $deleteResp = $this->actingAs($admin, 'backend')
        ->delete(route('admin.storefront-menus.destroy', $parent));
    $deleteResp->assertRedirect(route('admin.storefront-menus.index', ['location' => 'main']));

    expect(StorefrontMenu::find($parent->id))->toBeNull();
});

test('unauthorized staff cannot modify storefront menus', function () {
    $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'backend']);
    $viewPerm = Permission::firstOrCreate(['name' => 'view website settings', 'guard_name' => 'backend']);
    $staffRole->syncPermissions([$viewPerm]);

    $staff = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $staff->assignRole('staff');

    // View allowed
    $viewResp = $this->actingAs($staff, 'backend')
        ->get(route('admin.storefront-menus.index'));
    $viewResp->assertOk();

    // Store rejected
    $storeResp = $this->actingAs($staff, 'backend')
        ->post(route('admin.storefront-menus.store'), [
            'location' => 'main',
            'title' => 'Unauthorized Menu',
            'url' => '/hack',
            'target' => '_self',
        ]);
    $storeResp->assertForbidden();

    expect(StorefrontMenu::where('title', 'Unauthorized Menu')->exists())->toBeFalse();
});

test('storefront layout renders top, main and footer menus dynamically', function () {
    // Top menu item
    StorefrontMenu::create([
        'location' => 'top',
        'title' => 'VIP Club Concierge',
        'url' => '/vip',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    // Main parent & child
    $parent = StorefrontMenu::create([
        'location' => 'main',
        'title' => 'Extreme Overclocking',
        'url' => '/shop?category=overclocking',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    StorefrontMenu::create([
        'location' => 'main',
        'parent_id' => $parent->id,
        'title' => 'Liquid Nitrogen Pots',
        'url' => '/shop?category=ln2',
        'badge' => 'LN2',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    // Footer menu
    $footerCol = StorefrontMenu::create([
        'location' => 'footer',
        'title' => 'Community & Esports',
        'url' => '#',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    StorefrontMenu::create([
        'location' => 'footer',
        'parent_id' => $footerCol->id,
        'title' => 'Esports League Tournaments',
        'url' => '/esports',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    StorefrontMenu::flushCache();

    // Verify Homepage renders all dynamic menus
    $response = $this->get(route('home'));
    $response->assertOk()
        ->assertSee('VIP Club Concierge')
        ->assertSee('Extreme Overclocking')
        ->assertSee('Liquid Nitrogen Pots')
        ->assertSee('Community &amp; Esports', false)
        ->assertSee('Esports League Tournaments');
});

test('public about and contact pages render successfully', function () {
    $aboutResp = $this->get(route('about'));
    $aboutResp->assertOk()
        ->assertSee('About')
        ->assertSee('Built by Gamers, Tailored for Extreme Performance');

    $contactResp = $this->get(route('contact'));
    $contactResp->assertOk()
        ->assertSee('Contact')
        ->assertSee('Send Us an Inquiry or RFQ');
});
