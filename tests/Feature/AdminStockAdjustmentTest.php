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

it('fetches available in-stock serial numbers for a product in a warehouse via AJAX endpoint', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse1 = Warehouse::create(['name' => 'Depot A', 'code' => 'WH-DA']);
    $warehouse2 = Warehouse::create(['name' => 'Depot B', 'code' => 'WH-DB']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    // Warehouse 1 in-stock serial
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse1->id,
        'serial_number' => 'SN-WH1-IN-01',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Warehouse 1 shipped serial (should NOT be returned)
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse1->id,
        'serial_number' => 'SN-WH1-SHIPPED-02',
        'status' => SerialNumber::STATUS_SHIPPED,
    ]);

    // Warehouse 2 in-stock serial (different warehouse, should NOT be returned)
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse2->id,
        'serial_number' => 'SN-WH2-IN-03',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')->getJson(route('admin.stock.adjustments.product-serials', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse1->id,
    ]));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'count' => 1,
        'serials' => [
            ['serial_number' => 'SN-WH1-IN-01'],
        ],
    ]);
    $response->assertJsonMissing(['serial_number' => 'SN-WH1-SOLD-02']);
    $response->assertJsonMissing(['serial_number' => 'SN-WH2-IN-03']);
});

it('allows subtracting stock by selecting specific serial numbers and updates serial statuses', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot Sub', 'code' => 'WH-SUB']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 3,
        'requires_serial_tracking' => true,
    ]);
    $warehouse->products()->attach($product->id, ['quantity' => 3]);

    $serial1 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-SUB-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $serial2 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-SUB-002',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $serial3 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-SUB-003',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Deduct serial 1 and serial 2 for reason 'damaged'
    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'subtraction',
        'reason' => 'damaged',
        'notes' => 'Scrapped damaged units',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'serials' => "SN-SUB-001\nSN-SUB-002",
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    $response->assertSessionHas('success');

    // Quantities should be decremented from 3 to 1
    expect($warehouse->getProductStock($product))->toBe(1);
    $product->refresh();
    expect($product->stock_quantity)->toBe(1);

    // Serial statuses
    expect($serial1->fresh()->status)->toBe(SerialNumber::STATUS_DEFECTIVE_SCRAP);
    expect($serial2->fresh()->status)->toBe(SerialNumber::STATUS_DEFECTIVE_SCRAP);
    expect($serial3->fresh()->status)->toBe(SerialNumber::STATUS_IN_STOCK);
});

it('auto-determines quantity from serial count when quantity field is omitted or 0', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Main Depot', 'code' => 'WH-MD']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 0,
    ]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'addition',
        'reason' => 'received',
        'notes' => 'Omitted quantity test',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 0, // Quantity left as 0, should sync to 3
                'serials' => "SN-AUTO-01\nSN-AUTO-02\nSN-AUTO-03",
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    $response->assertSessionHas('success');

    $product->refresh();
    expect($product->stock_quantity)->toBe(3);
    expect(SerialNumber::where('serial_number', 'SN-AUTO-01')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'SN-AUTO-02')->exists())->toBeTrue();
    expect(SerialNumber::where('serial_number', 'SN-AUTO-03')->exists())->toBeTrue();
});

it('prevents adjustments when no serials or quantity are specified', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Main Depot 2', 'code' => 'WH-MD2']);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($admin, 'backend')->from(route('admin.stock.adjustments.create'))->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'addition',
        'reason' => 'received',
        'products' => [
            [
                'product_id' => $product->id,
                'quantity' => 0,
                'serials' => '',
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.create'));
    $response->assertSessionHas('error');
});

it('reconciles serial numbers on count correction by removing uncounted serials and updating warehouse stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot Correct', 'code' => 'WH-CORR']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 3,
        'requires_serial_tracking' => true,
    ]);
    $warehouse->products()->attach($product->id, ['quantity' => 3]);

    $s1 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-CORR-01',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $s2 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-CORR-02',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $s3 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-CORR-03',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Count correction: only SN-CORR-01 is present, remove SN-CORR-02 and SN-CORR-03
    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'correction',
        'reason' => 'physical_count',
        'notes' => 'Physical count audit',
        'products' => [
            [
                'product_id' => $product->id,
                'serials' => 'SN-CORR-01',
                'remove_serials' => "SN-CORR-02\nSN-CORR-03",
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    $response->assertSessionHas('success');

    // Warehouse stock should be updated to 1
    expect($warehouse->getProductStock($product))->toBe(1);

    // Product total stock should be 1
    $product->refresh();
    expect($product->stock_quantity)->toBe(1);

    // Serial statuses
    expect($s1->fresh()->status)->toBe(SerialNumber::STATUS_IN_STOCK);
    expect($s2->fresh()->status)->toBe(SerialNumber::STATUS_OTHER);
    expect($s3->fresh()->status)->toBe(SerialNumber::STATUS_OTHER);
});

it('supports adding newly discovered serials while removing uncounted serials during count correction', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot Correct 2', 'code' => 'WH-CORR2']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 2,
        'requires_serial_tracking' => true,
    ]);
    $warehouse->products()->attach($product->id, ['quantity' => 2]);

    $s1 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-EXIST-01',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $s2 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'serial_number' => 'SN-EXIST-02',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Keep s1, remove s2, and add newly found SN-NEW-99
    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'correction',
        'reason' => 'physical_count',
        'products' => [
            [
                'product_id' => $product->id,
                'serials' => 'SN-EXIST-01',
                'remove_serials' => 'SN-EXIST-02',
                'new_serials' => 'SN-NEW-99',
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.adjustments.index'));
    $response->assertSessionHas('success');

    // Warehouse stock: 1 kept + 1 new = 2
    expect($warehouse->getProductStock($product))->toBe(2);

    $product->refresh();
    expect($product->stock_quantity)->toBe(2);

    expect($s1->fresh()->status)->toBe(SerialNumber::STATUS_IN_STOCK);
    expect($s2->fresh()->status)->toBe(SerialNumber::STATUS_OTHER);
    expect(SerialNumber::where('serial_number', 'SN-NEW-99')->where('status', SerialNumber::STATUS_IN_STOCK)->exists())->toBeTrue();
});
