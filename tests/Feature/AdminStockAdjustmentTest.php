<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\StockAdjustment;
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

it('renders the stock adjustment create page with products combobox data', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    Warehouse::create(['name' => 'Depot 1', 'code' => 'WH-D1']);
    $category = Category::factory()->create(['name' => 'Audio']);
    Product::factory()->create(['name' => 'Studio Mic', 'sku' => 'MIC-001', 'category_id' => $category->id]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.stock.adjustments.create'));

    $response->assertStatus(200);
    $response->assertSee('New Stock Adjustment');
    $response->assertSee('Studio Mic');
    $response->assertSee('MIC-001');
});

it('allows recording a stock addition adjustment and synchronizes product total stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot 1', 'code' => 'WH-D1']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 10]);

    $warehouse->products()->attach($product->id, ['quantity' => 10]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'addition',
        'reason' => 'received',
        'notes' => 'New shipment batch received',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 15],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    $response->assertSessionHas('success');

    expect($warehouse->getProductStock($product))->toBe(25);
    $product->refresh();
    expect($product->stock_quantity)->toBe(25);
});

it('allows recording a stock subtraction adjustment and updates inventory', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot 2', 'code' => 'WH-D2']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 20]);

    $warehouse->products()->attach($product->id, ['quantity' => 20]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'subtraction',
        'reason' => 'damaged',
        'notes' => 'Water damage write-off',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 4],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    expect($warehouse->getProductStock($product))->toBe(16);
    $product->refresh();
    expect($product->stock_quantity)->toBe(16);
});

it('allows viewing stock adjustment details audit receipt', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Main Center', 'code' => 'WH-CTR']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $adjustment = StockAdjustment::create([
        'reference_number' => 'ADJ-TEST-0001',
        'warehouse_id' => $warehouse->id,
        'user_id' => $admin->id,
        'type' => 'correction',
        'reason' => 'physical_count',
        'notes' => 'Annual audit reconciliation',
    ]);

    $adjustment->items()->create([
        'product_id' => $product->id,
        'old_quantity' => 5,
        'adjusted_quantity' => 3,
        'new_quantity' => 8,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.stock.adjustments.show', $adjustment->id));

    $response->assertStatus(200);
    $response->assertSee('ADJ-TEST-0001');
    $response->assertSee('Main Center');
    $response->assertSee('Annual audit reconciliation');
});

it('allows recording stock adjustment with serial numbers and registers serials into inventory', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Main Warehouse', 'code' => 'WH-SN1']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 0,
        'requires_serial_tracking' => false,
    ]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'addition',
        'reason' => 'received',
        'notes' => 'Serial stock ingestion test',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'serials' => "SN-TEST-001\nSN-TEST-002",
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    $response->assertSessionHas('success');

    $product->refresh();
    expect($product->stock_quantity)->toBe(2);
    expect($product->requires_serial_tracking)->toBeTrue();

    expect(SerialNumber::where('serial_number', 'SN-TEST-001')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'SN-TEST-002')->exists())->toBeTrue();

    $serial1 = SerialNumber::where('serial_number', 'SN-TEST-001')->first();
    expect($serial1->status)->toBe(SerialNumber::STATUS_IN_STOCK);
    expect($serial1->warehouse_id)->toBe($warehouse->id);
    expect($serial1->product_id)->toBe($product->id);
});

it('prevents recording duplicate serial numbers in stock adjustment and returns friendly error', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Main Warehouse 2', 'code' => 'WH-SN2']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 0,
    ]);

    // Pre-create an existing serial number
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'EXISTING-SN-999',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')->from(route('admin.stock.adjustments.create'))->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'addition',
        'reason' => 'received',
        'notes' => 'Duplicate test',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'serials' => 'EXISTING-SN-999',
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.create'));
    $response->assertSessionHas('error');
});
