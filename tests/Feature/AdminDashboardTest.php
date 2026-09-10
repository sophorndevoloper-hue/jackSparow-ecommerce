<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\FrontendUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'frontend']);
});

it('prevents unauthenticated guests and normal users from accessing admin dashboard', function () {
    $guestResponse = $this->get(route('admin.dashboard'));
    $guestResponse->assertRedirect(route('admin.login'));

    $normalCustomer = FrontendUser::factory()->create();
    $normalCustomer->assignRole(Role::findByName('customer', 'frontend'));

    $userResponse = $this->actingAs($normalCustomer, 'frontend')->get(route('admin.dashboard'));
    $userResponse->assertRedirect(route('admin.login'));
});

it('allows admin to view the dashboard with live hardware KPIs', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create(['name' => 'Processors (CPU)']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $order = Order::factory()->create([
        'user_id' => $admin->id,
        'total_amount' => 1250.00,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Hardware Admin Dashboard');
    $response->assertSee('Paid Revenue');
    $response->assertSee('$1,250.00');
    $response->assertSee('Low Stock Inventory');
});

it('allows admin to create hardware products with specifications', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create(['name' => 'Graphics Cards (GPU)', 'slug' => 'gpu']);
    $brand = Brand::factory()->create(['name' => 'ASUS', 'slug' => 'asus']);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'ASUS ROG Strix RTX 4070 Ti Super',
        'sku' => 'RTX-4070-TI-SUP',
        'price' => 849.99,
        'cost_price' => 710.00,
        'stock_quantity' => 15,
        'low_stock_threshold' => 3,
        'is_featured' => 1,
        'spec_keys' => ['VRAM', 'Boost Clock'],
        'spec_values' => ['16GB GDDR6X', '2610 MHz'],
    ]);

    $newProduct = Product::where('sku', 'RTX-4070-TI-SUP')->first();
    expect($newProduct)->not->toBeNull();
    $response->assertRedirect(route('admin.products.images.index', ['product_id' => $newProduct->id]));
    expect($newProduct->specifications)->toBe(['VRAM' => '16GB GDDR6X', 'Boost Clock' => '2610 MHz']);
});

it('allows admin to view order details and update fulfillment status', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);

    $showResponse = $this->actingAs($admin, 'backend')->get(route('admin.orders.show', $order->id));
    $showResponse->assertStatus(200);
    $showResponse->assertSee($order->order_number);

    $patchResponse = $this->actingAs($admin, 'backend')->patch(route('admin.orders.status', $order->id), [
        'status' => 'shipped',
        'payment_status' => 'paid',
        'admin_notes' => 'Tracking number: 1Z9999999999999999',
    ]);

    $patchResponse->assertRedirect(route('admin.orders.show', $order->id));

    $order->refresh();
    expect($order->status)->toBe('shipped');
    expect($order->payment_status)->toBe('paid');
    expect($order->admin_notes)->toBe('Tracking number: 1Z9999999999999999');
});

it('allows users to register and admin to inspect them', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $user = User::factory()->create([
        'name' => 'Registered Staff',
        'email' => 'registered@admin.test',
        'is_approved' => false,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.users.index'));
    $response->assertStatus(200);
    $response->assertSee('Registered Staff');
});

it('allows admin to view users and apply permissions to user', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $targetUser = User::factory()->create(['name' => 'Tech Specialist', 'email' => 'tech@example.com']);
    Permission::firstOrCreate(['name' => 'create products', 'guard_name' => 'backend']);
    Permission::firstOrCreate(['name' => 'edit products', 'guard_name' => 'backend']);

    $indexResponse = $this->actingAs($admin, 'backend')->get(route('admin.users.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertSee('Tech Specialist');

    $editResponse = $this->actingAs($admin, 'backend')->get(route('admin.users.edit', $targetUser->id));
    $editResponse->assertStatus(200);
    $editResponse->assertSee('create products');

    $updateResponse = $this->actingAs($admin, 'backend')->put(route('admin.users.update', $targetUser->id), [
        'roles' => ['admin'],
        'permissions' => ['create products', 'edit products'],
    ]);

    $updateResponse->assertRedirect(route('admin.users.index'));

    $targetUser->refresh();
    expect($targetUser->hasRole('admin', 'backend'))->toBeTrue();
    expect($targetUser->hasDirectPermission('create products'))->toBeTrue();
    expect($targetUser->hasDirectPermission('edit products'))->toBeTrue();
});

it('allows admin to manage system roles and permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    Permission::firstOrCreate(['name' => 'view products', 'guard_name' => 'backend']);

    $rolesResponse = $this->actingAs($admin, 'backend')->get(route('admin.roles.index'));
    $rolesResponse->assertStatus(200);

    $createRoleResponse = $this->actingAs($admin, 'backend')->post(route('admin.roles.store'), [
        'name' => 'store-manager',
        'guard_name' => 'backend',
        'permissions' => ['view products'],
    ]);
    $createRoleResponse->assertRedirect(route('admin.roles.index'));

    $newRole = Role::where('name', 'store-manager')->where('guard_name', 'backend')->first();
    expect($newRole)->not->toBeNull();
    expect($newRole->hasPermissionTo('view products'))->toBeTrue();
});
