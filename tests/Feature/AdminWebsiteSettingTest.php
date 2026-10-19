<?php

use App\Models\AdminMenu;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\WebsiteSetting;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\WebsiteSettingSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Seed system menus, roles, and settings
    (new RoleAndPermissionSeeder)->run();
    (new BackendMenuSeeder)->run();
    (new WebsiteSettingSeeder)->run();
});

test('website settings routes redirect and sidebar tree shows only menus and products', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    // Website settings index redirects
    $response = $this->actingAs($admin, 'backend')
        ->get(route('admin.website-settings.index'));
    $response->assertRedirect(route('admin.storefront-menus.index'));

    // Verify AdminMenu sidebar tree includes Website Setup section with 2 items (Menus and Products)
    $sidebar = AdminMenu::getSidebarTree($admin);
    expect($sidebar)->toHaveKey('Website Setup');
    $websiteSetupItems = $sidebar['Website Setup'];
    expect($websiteSetupItems)->toHaveCount(2);
    expect($websiteSetupItems[0]['title'])->toBe('Website Menus');
    expect($websiteSetupItems[1]['title'])->toBe('Website Products');

    // All former sub-tab routes redirect to storefront menus
    $this->actingAs($admin, 'backend')->get(route('admin.website-settings.hero'))->assertRedirect(route('admin.storefront-menus.index'));
    $this->actingAs($admin, 'backend')->get(route('admin.website-settings.branding'))->assertRedirect(route('admin.storefront-menus.index'));
    $this->actingAs($admin, 'backend')->get(route('admin.website-settings.features'))->assertRedirect(route('admin.storefront-menus.index'));
    $this->actingAs($admin, 'backend')->get(route('admin.website-settings.footer'))->assertRedirect(route('admin.storefront-menus.index'));
});

test('admin can update hero and announcement settings and logs activity', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.website-settings.update'), [
            'active_tab' => 'hero',
            'announcement_enabled' => '1',
            'announcement_text' => 'FLASH SALE: 30% OFF ALL DDR5 MEMORY TODAY',
            'announcement_subtext' => 'Authorized Hardware Reseller Guarantee',
            'hero_badge' => 'Exclusive Titan Series',
            'hero_title' => 'Assemble The Ultimate Battle Station',
            'hero_subtitle' => 'High powered GPUs and liquid cooling units built for esports champions.',
            'hero_primary_btn_text' => 'Explore Battlestations',
            'hero_primary_btn_url' => '/shop?category=desktops',
        ]);

    $response->assertRedirect(route('admin.storefront-menus.index'));
    $response->assertSessionHas('success');

    expect(WebsiteSetting::get('announcement_text'))->toBe('FLASH SALE: 30% OFF ALL DDR5 MEMORY TODAY');
    expect(WebsiteSetting::get('hero_title'))->toBe('Assemble The Ultimate Battle Station');
    expect(WebsiteSetting::get('hero_badge'))->toBe('Exclusive Titan Series');

    // Verify activity logged
    $latestActivity = UserActivity::latest()->first();
    expect($latestActivity)->not->toBeNull();
    expect($latestActivity->module)->toBe('Website Settings');
    expect($latestActivity->action)->toBe('UPDATE');
});

test('admin can update feature highlights settings and logs activity', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.website-settings.update'), [
            'active_tab' => 'features',
            'feature_1_title' => 'Cutting-edge RTX GPUs',
            'feature_1_desc' => 'Next-gen raytracing power for creators',
        ]);

    $response->assertRedirect(route('admin.storefront-menus.index'));

    expect(WebsiteSetting::get('feature_1_title'))->toBe('Cutting-edge RTX GPUs');
    expect(WebsiteSetting::get('feature_1_desc'))->toBe('Next-gen raytracing power for creators');
});

test('storefront reflects updated website settings dynamically', function () {
    // Set custom settings
    WebsiteSetting::set('site_name', 'Quantum Hardware Works', 'branding');
    WebsiteSetting::set('site_tagline', 'Next-Generation Silicon & Liquid Cooling', 'branding');
    WebsiteSetting::set('hero_title', 'Craft Your Dream Quantum Workstation', 'hero');
    WebsiteSetting::set('hero_badge', 'Quantum Overclocked 2026', 'hero');
    WebsiteSetting::set('announcement_text', 'Worldwide Air Cargo Shipping Available Now', 'announcement');
    WebsiteSetting::flushCache();

    // Visit Storefront Homepage
    $response = $this->get(route('home'));
    $response->assertOk()
        ->assertSee('Quantum Hardware Works')
        ->assertSee('Next-Generation Silicon & Liquid Cooling')
        ->assertSee('Craft Your Dream Quantum Workstation')
        ->assertSee('Quantum Overclocked 2026')
        ->assertSee('Worldwide Air Cargo Shipping Available Now');
});

test('unauthorized staff user without edit website settings permission cannot update settings', function () {
    $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'backend']);
    $viewPerm = Permission::firstOrCreate(['name' => 'view website settings', 'guard_name' => 'backend']);
    $staffRole->syncPermissions([$viewPerm]);

    $staff = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $staff->assignRole('staff');

    // Index redirects
    $viewResp = $this->actingAs($staff, 'backend')->get(route('admin.website-settings.index'));
    $viewResp->assertRedirect(route('admin.storefront-menus.index'));

    // Cannot update
    $updateResp = $this->actingAs($staff, 'backend')->post(route('admin.website-settings.update'), [
        'active_tab' => 'hero',
        'hero_title' => 'Malicious Headline Change',
    ]);
    $updateResp->assertForbidden();

    expect(WebsiteSetting::get('hero_title'))->not->toBe('Malicious Headline Change');
});
