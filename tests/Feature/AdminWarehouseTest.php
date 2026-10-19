<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\Warehouse;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        BackendMenuSeeder::class,
    ]);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole(Role::findByName('admin', 'backend'));
});

it('allows admin to view warehouses listing', function () {
    $admin = $this->admin;

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
    $this->assertDatabaseHas('user_activities', [
        'action' => 'CREATE',
        'module' => 'Warehouse',
    ]);
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

it('records UserActivity when a warehouse is updated and deleted', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create([
        'name' => 'Original East Hub',
        'code' => 'WH-EAST-1',
        'phone' => '+1 111 222',
        'city' => 'Boston, MA',
        'is_active' => true,
        'is_default' => false,
    ]);

    // 1. Update warehouse
    $updateResponse = $this->actingAs($admin, 'backend')->put(route('admin.warehouses.update', $warehouse), [
        'name' => 'Renovated East Hub',
        'code' => 'WH-EAST-1',
        'phone' => '+1 333 444',
        'city' => 'Boston, MA',
        'is_active' => '1',
    ]);

    $updateResponse->assertRedirect(route('admin.warehouses.index'));
    $this->assertDatabaseHas('user_activities', [
        'action' => 'UPDATE',
        'module' => 'Warehouse',
        'entity_id' => (string) $warehouse->id,
    ]);

    $updateLog = UserActivity::where('action', 'UPDATE')
        ->where('module', 'Warehouse')
        ->where('entity_id', (string) $warehouse->id)
        ->first();

    expect($updateLog)->not->toBeNull();
    expect($updateLog->properties['changes'])->toHaveKey('name');
    expect($updateLog->properties['changes']['name']['old'])->toBe('Original East Hub');
    expect($updateLog->properties['changes']['name']['new'])->toBe('Renovated East Hub');

    // 2. Delete warehouse
    $deleteResponse = $this->actingAs($admin, 'backend')->delete(route('admin.warehouses.destroy', $warehouse));
    $deleteResponse->assertRedirect(route('admin.warehouses.index'));

    $this->assertDatabaseHas('user_activities', [
        'action' => 'DELETE',
        'module' => 'Warehouse',
        'entity_id' => (string) $warehouse->id,
    ]);
});

it('records UserActivity across inventory stock adjustments and transfers', function () {
    $admin = $this->admin;

    $warehouseA = Warehouse::create([
        'name' => 'Central Hub',
        'code' => 'WH-CTR',
        'is_active' => true,
    ]);

    $warehouseB = Warehouse::create([
        'name' => 'West Satellite',
        'code' => 'WH-WST',
        'is_active' => true,
    ]);

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Gigabyte Z790 Motherboard',
        'stock_quantity' => 50,
        'requires_serial_tracking' => false,
    ]);

    $warehouseA->setProductStock($product, 50);

    // 1. Stock Adjustment
    $adjResponse = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouseA->id,
        'type' => 'addition',
        'reason' => 'Annual inventory audit surplus',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 5,
            ],
        ],
    ]);

    $adjResponse->assertRedirect(route('admin.stock.adjustments.index'));
    $this->assertDatabaseHas('user_activities', [
        'action' => 'CREATE',
        'module' => 'StockAdjustment',
    ]);

    // 2. Stock Transfer
    $trfResponse = $this->actingAs($admin, 'backend')->post(route('admin.stock.transfers.store'), [
        'from_warehouse_id' => $warehouseA->id,
        'to_warehouse_id' => $warehouseB->id,
        'status' => 'pending',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 10,
            ],
        ],
    ]);

    $trfResponse->assertRedirect(route('admin.stock.transfers.index'));
    $this->assertDatabaseHas('user_activities', [
        'action' => 'CREATE',
        'module' => 'StockTransfer',
    ]);

    $transfer = StockTransfer::where('from_warehouse_id', $warehouseA->id)->first();
    expect($transfer)->not->toBeNull();

    // 3. Update transfer status
    $statusResponse = $this->actingAs($admin, 'backend')->patch(route('admin.stock.transfers.status', $transfer), [
        'status' => 'in_transit',
    ]);

    $statusResponse->assertRedirect();
    $this->assertDatabaseHas('user_activities', [
        'action' => 'UPDATE',
        'module' => 'StockTransfer',
        'entity_id' => (string) $transfer->id,
    ]);
});
