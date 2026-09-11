<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $permissions = [
        'view dashboard',
        'view collections',
        'create collections',
        'edit collections',
        'delete collections',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'backend']);
    }

    $adminRole->syncPermissions(Permission::where('guard_name', 'backend')->get());
});

it('allows admin to view collections listing', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $collection = ProductCollection::create([
        'name' => 'Flagship Battlestations',
        'slug' => 'flagship-battlestations',
        'description' => 'High performance hardware bundles',
        'is_active' => true,
        'is_featured' => true,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.collections.index'));

    $response->assertStatus(200);
    $response->assertSee('Flagship Battlestations');
    $response->assertSee('flagship-battlestations');
});

it('allows admin to view create page with products list', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'name' => 'NVIDIA RTX 4090 OC',
        'sku' => 'GPU-RTX4090-OC',
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.collections.create'));

    $response->assertStatus(200);
    $response->assertSee('NVIDIA RTX 4090 OC');
    $response->assertSee('GPU-RTX4090-OC');
});

it('allows admin to create a new product collection with assigned products and banner upload', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $prod1 = Product::factory()->create(['category_id' => $category->id]);
    $prod2 = Product::factory()->create(['category_id' => $category->id]);

    $file = UploadedFile::fake()->image('banner.jpg', 1200, 400);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.collections.store'), [
        'name' => 'Gaming Bundles 2026',
        'slug' => 'gaming-bundles-2026',
        'description' => 'Top tier builds for enthusiast gamers',
        'is_active' => '1',
        'is_featured' => '1',
        'sort_order' => 5,
        'image_file' => $file,
        'product_ids' => [$prod1->id, $prod2->id],
    ]);

    $response->assertRedirect(route('admin.collections.index'));
    $this->assertDatabaseHas('product_collections', [
        'name' => 'Gaming Bundles 2026',
        'slug' => 'gaming-bundles-2026',
        'is_active' => true,
        'is_featured' => true,
    ]);

    $collection = ProductCollection::where('slug', 'gaming-bundles-2026')->first();
    expect($collection->products)->toHaveCount(2);
    expect($collection->image)->not->toBeNull();
    expect($collection->image_url)->toContain('storage/collections/');
    Storage::disk('public')->assertExists($collection->image);
});

it('allows admin to view edit page and update collection details, remove image, and update products', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $prod1 = Product::factory()->create(['category_id' => $category->id]);
    $prod2 = Product::factory()->create(['category_id' => $category->id]);

    $collection = ProductCollection::create([
        'name' => 'Budget Builds',
        'slug' => 'budget-builds',
        'image' => 'collections/old-banner.jpg',
        'is_active' => true,
    ]);
    Storage::disk('public')->put('collections/old-banner.jpg', 'dummy');
    $collection->products()->attach([$prod1->id]);

    // View edit page
    $editResponse = $this->actingAs($admin, 'backend')->get(route('admin.collections.edit', $collection));
    $editResponse->assertStatus(200);
    $editResponse->assertSee('Budget Builds');

    // Update collection with remove_image and replace products
    $updateResponse = $this->actingAs($admin, 'backend')->put(route('admin.collections.update', $collection), [
        'name' => 'Mid-Range & Budget Builds',
        'slug' => 'budget-builds',
        'description' => 'Updated description for value hardware',
        'is_active' => '1',
        'remove_image' => '1',
        'product_ids' => [$prod2->id],
    ]);

    $updateResponse->assertRedirect(route('admin.collections.index'));
    $this->assertDatabaseHas('product_collections', [
        'id' => $collection->id,
        'name' => 'Mid-Range & Budget Builds',
        'image' => null,
    ]);

    $collection->refresh();
    expect($collection->image)->toBeNull();
    expect($collection->image_url)->toBeNull();
    expect($collection->products->pluck('id')->toArray())->toEqual([$prod2->id]);
    Storage::disk('public')->assertMissing('collections/old-banner.jpg');
});

it('allows admin to clear all products from collection', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $prod = Product::factory()->create(['category_id' => $category->id]);

    $collection = ProductCollection::create([
        'name' => 'Empty Soon',
        'slug' => 'empty-soon',
        'is_active' => true,
    ]);
    $collection->products()->attach([$prod->id]);
    expect($collection->products)->toHaveCount(1);

    $response = $this->actingAs($admin, 'backend')->put(route('admin.collections.update', $collection), [
        'name' => 'Empty Now',
        'slug' => 'empty-soon',
    ]);

    $response->assertRedirect(route('admin.collections.index'));
    $collection->refresh();
    expect($collection->products)->toHaveCount(0);
});

it('allows admin to delete a product collection', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $collection = ProductCollection::create([
        'name' => 'Old Legacy Collection',
        'slug' => 'old-legacy',
        'is_active' => false,
    ]);

    $response = $this->actingAs($admin, 'backend')->delete(route('admin.collections.destroy', $collection));

    $response->assertRedirect(route('admin.collections.index'));
    $this->assertDatabaseMissing('product_collections', [
        'id' => $collection->id,
    ]);
});
