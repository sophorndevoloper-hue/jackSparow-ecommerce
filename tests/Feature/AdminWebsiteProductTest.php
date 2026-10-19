<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\FeaturedComponent;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\StorefrontProduct;
use App\Models\User;
use App\Models\UserActivity;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\WebsiteSettingSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    (new RoleAndPermissionSeeder)->run();
    (new BackendMenuSeeder)->run();
    (new WebsiteSettingSeeder)->run();
});

test('admin can access website products management page under website setup', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin, 'backend')
        ->get(route('admin.website-products.index'));

    $response->assertOk()
        ->assertSee('Website Products Showcase')
        ->assertSee('Featured Components')
        ->assertSee('Flash Deals &amp; Clearance', false)
        ->assertSee('Best Sellers')
        ->assertDontSee('New Arrivals')
        ->assertDontSee('Product Collections')
        ->assertDontSee('Product Promotions');
});

test('admin can add a catalog product to website showcase', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'NVIDIA RTX 5090 Founders Edition',
        'price' => 1999.00,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.website-products.store'), [
            'product_id' => $product->id,
            'section' => 'featured',
            'badge' => 'EXCLUSIVE',
            'custom_title' => 'Flagship Beast RTX 5090',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('admin.website-products.index', ['section' => 'featured']));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('storefront_products', [
        'product_id' => $product->id,
        'section' => 'featured',
        'badge' => 'EXCLUSIVE',
        'custom_title' => 'Flagship Beast RTX 5090',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    // Verify activity logging
    $activity = UserActivity::latest()->first();
    expect($activity)->not->toBeNull();
    expect($activity->module)->toBe('Website Products');
    expect($activity->action)->toBe('CREATE');
});

test('admin can update showcase product details', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $product = Product::factory()->create(['is_active' => true]);
    $item = StorefrontProduct::create([
        'product_id' => $product->id,
        'section' => 'deals',
        'badge' => 'SALE',
        'sort_order' => 5,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->put(route('admin.website-products.update', $item->id), [
            'badge' => 'HOT DEAL',
            'custom_title' => 'Mega Price Drop',
            'sort_order' => 2,
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('admin.website-products.index', ['section' => 'deals']));
    $response->assertSessionHas('success');

    expect($item->fresh()->badge)->toBe('HOT DEAL');
    expect($item->fresh()->custom_title)->toBe('Mega Price Drop');
    expect($item->fresh()->sort_order)->toBe(2);
});

test('admin can toggle active visibility of a product on the website', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $product = Product::factory()->create(['is_active' => true]);
    $item = StorefrontProduct::create([
        'product_id' => $product->id,
        'section' => 'bestseller',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    // AJAX toggle
    $response = $this->actingAs($admin, 'backend')
        ->patchJson(route('admin.website-products.toggle-active', $item->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'is_active' => false,
        ]);

    expect($item->fresh()->is_active)->toBeFalse();

    // Toggle back
    $this->actingAs($admin, 'backend')
        ->patch(route('admin.website-products.toggle-active', $item->id));

    expect($item->fresh()->is_active)->toBeTrue();
});

test('admin can remove product from website showcase without deleting from inventory', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $product = Product::factory()->create(['is_active' => true]);
    $item = StorefrontProduct::create([
        'product_id' => $product->id,
        'section' => 'new_arrivals',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->delete(route('admin.website-products.destroy', $item->id));

    $response->assertRedirect(route('admin.website-products.index', ['section' => 'new_arrivals']));
    $response->assertSessionHas('success');

    // Showcase record deleted
    $this->assertDatabaseMissing('storefront_products', ['id' => $item->id]);

    // Inventory product still exists!
    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('unauthorized staff without edit website settings permission cannot modify website products', function () {
    $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'backend']);
    $viewPerm = Permission::firstOrCreate(['name' => 'view website settings', 'guard_name' => 'backend']);
    $staffRole->syncPermissions([$viewPerm]);

    $staff = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $staff->assignRole('staff');

    $product = Product::factory()->create();

    // View allowed
    $this->actingAs($staff, 'backend')
        ->get(route('admin.website-products.index'))
        ->assertOk();

    // Modification blocked
    $this->actingAs($staff, 'backend')
        ->post(route('admin.website-products.store'), [
            'product_id' => $product->id,
            'section' => 'featured',
        ])
        ->assertForbidden();
});

test('storefront homepage reflects products managed in website setup showcase', function () {
    StorefrontProduct::query()->delete();
    StorefrontProduct::flushCache();

    $category = Category::factory()->create();
    $brand = Brand::factory()->create(['name' => 'ROG Republic of Gamers']);

    $productA = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Custom Watercooled Gaming Beast',
        'is_active' => true,
    ]);

    $productB = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Hidden Stash Component',
        'is_active' => true,
    ]);

    // Curate Product A into Featured showcase with badge
    StorefrontProduct::create([
        'product_id' => $productA->id,
        'section' => 'featured',
        'badge' => 'HOT RIG',
        'custom_title' => 'Custom Watercooled Gaming Beast Overclocked',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    // Product B is inactive on website
    StorefrontProduct::create([
        'product_id' => $productB->id,
        'section' => 'featured',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    StorefrontProduct::flushCache();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Custom Watercooled Gaming Beast Overclocked')
        ->assertSee('HOT RIG')
        ->assertDontSee('Hidden Stash Component');
});

test('storefront homepage does not display removed new arrivals, collections or promotions', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertDontSee('New Arrivals')
        ->assertDontSee('Product Collections')
        ->assertDontSee('Special Product Promotions');
});

test('storefront shop can filter products by brand and collection', function () {
    $brand = Brand::factory()->create(['name' => 'Corsair Gaming', 'slug' => 'corsair-gaming', 'is_active' => true]);
    $collection = ProductCollection::create([
        'name' => 'Liquid Cooled Series',
        'slug' => 'liquid-cooled-series',
        'is_active' => true,
    ]);

    $matchingProduct = Product::factory()->create([
        'name' => 'Corsair HydroX iCUE Link Kit',
        'brand_id' => $brand->id,
        'is_active' => true,
    ]);
    $collection->products()->attach([$matchingProduct->id]);

    $otherProduct = Product::factory()->create([
        'name' => 'Generic Keyboard RGB',
        'is_active' => true,
    ]);

    // Test filter by brand
    $responseBrand = $this->get(route('shop', ['brand' => 'corsair-gaming']));
    $responseBrand->assertOk()
        ->assertSee('Corsair HydroX iCUE Link Kit')
        ->assertDontSee('Generic Keyboard RGB');

    // Test filter by collection
    $responseCol = $this->get(route('shop', ['collection' => 'liquid-cooled-series']));
    $responseCol->assertOk()
        ->assertSee('Corsair HydroX iCUE Link Kit')
        ->assertDontSee('Generic Keyboard RGB');
});

test('admin can add multiple hardware products to website showcase at once', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $p1 = Product::factory()->create(['name' => 'AMD Ryzen 9 7950X3D', 'is_active' => true]);
    $p2 = Product::factory()->create(['name' => 'ASUS ROG RTX 4080', 'is_active' => true]);

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.website-products.store'), [
            'product_ids' => [$p1->id, $p2->id],
            'section' => 'deals',
            'badge' => 'SALE',
            'sort_order' => 10,
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('admin.website-products.index', ['section' => 'deals']));
    $response->assertSessionHas('success', '2 products added to Flash Deals & Clearance showcase successfully.');

    $this->assertDatabaseHas('storefront_products', [
        'product_id' => $p1->id,
        'section' => 'deals',
        'badge' => 'SALE',
        'sort_order' => 10,
    ]);

    $this->assertDatabaseHas('storefront_products', [
        'product_id' => $p2->id,
        'section' => 'deals',
        'badge' => 'SALE',
        'sort_order' => 11,
    ]);
});

test('website products index view renders search combobox and selected hardware box', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    Product::factory()->create(['name' => 'Intel Core i9 14900K', 'sku' => 'CPU-INTEL-14900K', 'is_active' => true]);

    $response = $this->actingAs($admin, 'backend')
        ->get(route('admin.website-products.index'));

    $response->assertOk()
        ->assertSee('hardwareSearchInput')
        ->assertSee('hardwareDropdownMenu')
        ->assertSee('selectedHardwareBox')
        ->assertSee('selectedHardwareList');
});

test('adding products links them polymorphically to section model', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'approved_at' => now(),
    ]);
    $admin->assignRole('admin');

    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($admin, 'backend')
        ->post(route('admin.website-products.store'), [
            'product_id' => $product->id,
            'section' => 'featured',
            'badge' => 'TOP TIER',
        ]);

    $item = StorefrontProduct::where('product_id', $product->id)->where('section', 'featured')->first();
    expect($item)->not->toBeNull();
    expect($item->showcaseable_type)->toBe(FeaturedComponent::class);
    expect($item->showcaseable_id)->not->toBeNull();
    expect($item->showcaseable)->not->toBeNull();
});
