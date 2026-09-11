<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $permissions = [
        'view dashboard',
        'view products',
        'create products',
        'edit products',
        'delete products',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'backend']);
    }

    $adminRole->syncPermissions(Permission::where('guard_name', 'backend')->get());
});

it('allows transferring stock between two warehouses and moves inventory immediately on completed', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'Warehouse Alpha', 'code' => 'WH-A']);
    $toWh = Warehouse::create(['name' => 'Warehouse Bravo', 'code' => 'WH-B']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 50]);

    $fromWh->products()->attach($product->id, ['quantity' => 50]);
    $toWh->products()->attach($product->id, ['quantity' => 0]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.transfers.store'), [
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'status' => 'completed',
        'transfer_date' => date('Y-m-d'),
        'notes' => 'Urgent replenishment',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 20],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.transfers.index'));
    $response->assertSessionHas('success');

    expect($fromWh->getProductStock($product))->toBe(30);
    expect($toWh->getProductStock($product))->toBe(20);

    $product->refresh();
    expect($product->stock_quantity)->toBe(50);
});

it('allows transferring stock with serial numbers and updates serial warehouse location', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'East Hub', 'code' => 'WH-EAST']);
    $toWh = Warehouse::create(['name' => 'West Hub', 'code' => 'WH-WEST']);

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'requires_serial_tracking' => true,
    ]);

    // Create 3 serial numbers in East Hub
    $sn1 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $fromWh->id,
        'serial_number' => 'GPU-4080-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);
    $sn2 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $fromWh->id,
        'serial_number' => 'GPU-4080-002',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);
    $sn3 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $fromWh->id,
        'serial_number' => 'GPU-4080-003',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $fromWh->products()->attach($product->id, ['quantity' => 3]);
    $toWh->products()->attach($product->id, ['quantity' => 0]);

    // Transfer 2 serial numbers
    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.transfers.store'), [
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'status' => 'completed',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'serials' => 'GPU-4080-001, GPU-4080-002',
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.transfers.index'));
    $response->assertSessionHas('success');

    // Verify warehouse quantities
    expect($fromWh->getProductStock($product))->toBe(1);
    expect($toWh->getProductStock($product))->toBe(2);

    // Verify serial numbers warehouse location
    $sn1->refresh();
    $sn2->refresh();
    $sn3->refresh();

    expect($sn1->warehouse_id)->toBe($toWh->id);
    expect($sn2->warehouse_id)->toBe($toWh->id);
    expect($sn3->warehouse_id)->toBe($fromWh->id); // Remained in East Hub

    // Verify Transfer Item recorded serial_numbers
    $transfer = StockTransfer::latest('id')->first();
    $item = $transfer->items()->first();
    expect($item->serial_numbers)->toEqual(['GPU-4080-001', 'GPU-4080-002']);
    expect($item->quantity)->toBe(2);
});

it('blocks stock transfer if serial number does not exist in source warehouse', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'South Depot', 'code' => 'WH-SOUTH']);
    $toWh = Warehouse::create(['name' => 'North Depot', 'code' => 'WH-NORTH']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $fromWh->products()->attach($product->id, ['quantity' => 5]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.transfers.store'), [
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'status' => 'completed',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'serials' => 'NON-EXISTENT-SN-999',
            ],
        ],
    ]);

    $response->assertSessionHas('error');
    expect($fromWh->getProductStock($product))->toBe(5);
    expect($toWh->getProductStock($product))->toBe(0);
});

it('fetches available serial numbers in source warehouse via JSON API', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Central Hub', 'code' => 'WH-CENTRAL']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'CENTRAL-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')->getJson(route('admin.stock.transfers.product-serials', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
    ]));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'count' => 1,
    ]);
    expect($response->json('serials.0.serial_number'))->toBe('CENTRAL-001');
});

it('allows transitioning in_transit transfer with serials to completed status and updates serial warehouse', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'Warehouse Origin', 'code' => 'WH-OG']);
    $toWh = Warehouse::create(['name' => 'Warehouse Destination', 'code' => 'WH-DEST']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $sn = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $fromWh->id,
        'serial_number' => 'TRANSIT-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $fromWh->products()->attach($product->id, ['quantity' => 10]);
    $toWh->products()->attach($product->id, ['quantity' => 0]);

    $transfer = StockTransfer::create([
        'reference_number' => StockTransfer::generateReference(),
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'user_id' => $admin->id,
        'status' => 'in_transit',
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'serial_numbers' => ['TRANSIT-001'],
    ]);

    // Update status to completed
    $response = $this->actingAs($admin, 'backend')->patch(route('admin.stock.transfers.status', $transfer->id), [
        'status' => 'completed',
    ]);

    $response->assertSessionHas('success');
    expect($toWh->getProductStock($product))->toBe(1);

    $sn->refresh();
    expect($sn->warehouse_id)->toBe($toWh->id);
    expect($sn->status)->toBe(SerialNumber::STATUS_IN_STOCK);
});

it('renders the stock transfer create view with searchable component picker combobox', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    Warehouse::create(['name' => 'Source Hub', 'code' => 'HUB-1']);
    Warehouse::create(['name' => 'Target Hub', 'code' => 'HUB-2']);

    $category = Category::factory()->create(['name' => 'CPUs']);
    Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Intel Core i9-14900K',
        'sku' => 'CPU-INTEL-14900K',
        'requires_serial_tracking' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.stock.transfers.create'));

    $response->assertOk();
    $response->assertSee('product-search-input');
    $response->assertSee('product-dropdown-menu');
    $response->assertSee('product-picker-toggle');
    $response->assertSee('Intel Core i9-14900K');
    $response->assertSee('CPU-INTEL-14900K');
});

it('renders the redesigned stock transfer manifest show view with metrics and serials', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'East Coast Hub', 'code' => 'WH-EAST', 'city' => 'New York']);
    $toWh = Warehouse::create(['name' => 'Main Central Depot', 'code' => 'WH-MAIN', 'city' => 'Chicago']);

    $category = Category::factory()->create(['name' => 'Processors (CPU)']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'AMD Ryzen 9 7950X3D Desktop Processor',
        'sku' => 'CPU-AMD-7950X3D',
        'requires_serial_tracking' => true,
    ]);

    $transfer = StockTransfer::create([
        'reference_number' => 'TRF-20260911-0001',
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'user_id' => $admin->id,
        'status' => 'completed',
        'notes' => 'Priority overnight distribution shipment',
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'quantity' => 2,
        'serial_numbers' => ['SN-RYZEN-001', 'SN-RYZEN-002'],
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.stock.transfers.show', $transfer->id));

    $response->assertOk();
    $response->assertSee('TRF-20260911-0001');
    $response->assertSee('East Coast Hub');
    $response->assertSee('Main Central Depot');
    $response->assertSee('AMD Ryzen 9 7950X3D Desktop Processor');
    $response->assertSee('CPU-AMD-7950X3D');
    $response->assertSee('SN-RYZEN-001');
    $response->assertSee('SN-RYZEN-002');
    $response->assertSee('2 units');
    $response->assertSee('Tracked Serial Numbers');
    $response->assertSee('Priority overnight distribution shipment');
});
