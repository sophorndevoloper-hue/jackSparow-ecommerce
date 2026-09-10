<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $permissions = [
        'view dashboard',
        'view warehouses',
        'create warehouses',
        'edit warehouses',
        'delete warehouses',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'backend']);
    }

    $adminRole->syncPermissions(Permission::where('guard_name', 'backend')->get());
});

it('allows admin to view warehouses listing', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create([
        'name' => 'Test Regional Hub',
        'code' => 'WH-TEST-1',
        'city' => 'Chicago, IL',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.warehouses.index'));

    $response->assertStatus(200);
    $response->assertSee('Test Regional Hub');
    $response->assertSee('WH-TEST-1');
});

it('allows admin to create a new warehouse', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->post(route('admin.warehouses.store'), [
        'name' => 'South Depot Facility',
        'code' => 'WH-SOUTH',
        'phone' => '+1 555-0123',
        'city' => 'Houston, TX',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.warehouses.index'));
    $this->assertDatabaseHas('warehouses', ['code' => 'WH-SOUTH', 'name' => 'South Depot Facility']);
});

it('allows admin to view warehouse inventory details', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create([
        'name' => 'North Central Hub',
        'code' => 'WH-NORTH',
        'is_active' => true,
    ]);

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'NVIDIA RTX 4090 GPU',
        'price' => 1599.99,
    ]);

    $warehouse->products()->attach($product->id, ['quantity' => 12]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.warehouses.show', $warehouse->id));

    $response->assertStatus(200);
    $response->assertSee('North Central Hub');
    $response->assertSee('NVIDIA RTX 4090 GPU');
    $response->assertSee('12 Units');
});

it('prevents deletion of default warehouse or warehouse with stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $defaultWh = Warehouse::create([
        'name' => 'Protected Default Depot',
        'code' => 'WH-PROTECT',
        'is_default' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')->delete(route('admin.warehouses.destroy', $defaultWh->id));
    $response->assertSessionHas('error');
    $this->assertDatabaseHas('warehouses', ['id' => $defaultWh->id]);
});
