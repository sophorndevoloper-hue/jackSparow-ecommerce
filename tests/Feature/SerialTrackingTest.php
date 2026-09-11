<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SerialTrackingService;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->category = Category::firstOrCreate(['slug' => 'processors-cpu'], ['name' => 'Processors (CPU)']);
    $this->warehouse = Warehouse::firstOrCreate(['code' => 'MAIN_TEST'], ['name' => 'Test Main Warehouse', 'location' => 'Bay Area']);
    $this->serialService = app(SerialTrackingService::class);
});

it('bulk ingests serial numbers and syncs warehouse inventory levels', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'AMD EPYC 9654 Server CPU',
        'slug' => 'amd-epyc-9654',
        'sku' => 'CPU-EPYC-9654',
        'cost_price' => 8000.00,
        'price' => 11000.00,
        'stock_quantity' => 0,
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    $rawSerials = "EPYC9654-001\nEPYC9654-002\nEPYC9654-003";

    $ingested = $this->serialService->ingestSerials($product, $this->warehouse, $rawSerials);

    expect($ingested->count())->toBe(3);
    expect(SerialNumber::where('product_id', $product->id)->count())->toBe(3);
    expect($product->fresh()->stock_quantity)->toBe(3);

    $firstUnit = SerialNumber::where('serial_number', 'EPYC9654-001')->first();
    expect($firstUnit->status)->toBe(SerialNumber::STATUS_IN_STOCK);
    expect($firstUnit->warehouse_id)->toBe($this->warehouse->id);
});

it('activates warranty periods upon outbound shipment', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Intel Xeon w9-3495X',
        'slug' => 'intel-xeon-w9-3495x',
        'sku' => 'CPU-XEON-3495X',
        'price' => 5889.00,
        'cost_price' => 4500.00,
        'manufacturer_warranty_months' => 36,
        'stock_quantity' => 1,
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    $unit = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'XEON-3495X-ALPHA',
        'status' => SerialNumber::STATUS_ALLOCATED,
    ]);

    $this->serialService->shipUnits([$unit]);

    $freshUnit = $unit->fresh();
    expect($freshUnit->status)->toBe(SerialNumber::STATUS_SHIPPED);
    expect($freshUnit->warranty_start_date)->not->toBeNull();
    expect($freshUnit->warranty_end_date)->not->toBeNull();

    // Check warranty helper
    $warranty = $this->serialService->checkWarranty('XEON-3495X-ALPHA');
    expect($warranty['is_under_warranty'])->toBeTrue();
    expect($warranty['days_remaining'])->toBeGreaterThan(1000);
});

it('processes RMA return and updates lifecycle history notes', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Test Hardware Part',
        'slug' => 'test-hardware-part',
        'sku' => 'PART-RMA-TEST',
        'price' => 100.00,
        'stock_quantity' => 1,
        'low_stock_threshold' => 1,
    ]);

    $unit = SerialNumber::create([
        'product_id' => $product->id,
        'serial_number' => 'SN-RMA-TEST-001',
        'status' => SerialNumber::STATUS_SHIPPED,
    ]);

    $this->serialService->processRmaReturn('SN-RMA-TEST-001', SerialNumber::STATUS_RETURNED_RMA, 'Dead on arrival - PCIe slot failure');

    $freshUnit = $unit->fresh();
    expect($freshUnit->status)->toBe(SerialNumber::STATUS_RETURNED_RMA);
    expect($freshUnit->notes)->toContain('Dead on arrival - PCIe slot failure');
});

it('updates serial number status to allocated and shipped via updateStatus', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'GeForce RTX 4090 OC',
        'slug' => 'geforce-rtx-4090-oc',
        'sku' => 'GPU-RTX4090-OC',
        'price' => 1999.99,
        'cost_price' => 1500.00,
        'manufacturer_warranty_months' => 24,
        'stock_quantity' => 2,
        'low_stock_threshold' => 1,
    ]);

    $unit = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'RTX4090-ALLOC-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Update to ALLOCATED
    $this->serialService->updateStatus('RTX4090-ALLOC-001', SerialNumber::STATUS_ALLOCATED, 'Reserved for enterprise order #1001');

    $freshUnit = $unit->fresh();
    expect($freshUnit->status)->toBe(SerialNumber::STATUS_ALLOCATED);
    expect($freshUnit->notes)->toBe('Reserved for enterprise order #1001');

    // Update to SHIPPED (should set outbound date and warranty)
    $this->serialService->updateStatus('RTX4090-ALLOC-001', SerialNumber::STATUS_SHIPPED);

    $freshUnit = $unit->fresh();
    expect($freshUnit->status)->toBe(SerialNumber::STATUS_SHIPPED);
    expect($freshUnit->outbound_date)->not->toBeNull();
    expect($freshUnit->warranty_start_date)->not->toBeNull();
    expect($freshUnit->warranty_end_date)->not->toBeNull();
});

it('allows admin to update serial status to ALLOCATED via http request', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
    ]);
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole('superadmin');

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Server Motherboard Pro',
        'slug' => 'server-mb-pro',
        'sku' => 'MB-SRV-PRO',
        'price' => 750.00,
        'stock_quantity' => 1,
        'low_stock_threshold' => 1,
    ]);

    $unit = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'MB-SRV-999',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->put(route('admin.serial-numbers.update', $unit->id), [
            'status' => 'ALLOCATED',
            'notes' => 'Allocated for Rack 4 Build',
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    expect($unit->fresh()->status)->toBe('ALLOCATED');
    expect($unit->fresh()->notes)->toBe('Allocated for Rack 4 Build');
});

it('includes all catalog products in the serial number registry and auto-enables serial tracking on ingest', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $untrackedProduct = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Standard DDR5 Memory Module',
        'slug' => 'standard-ddr5-mem',
        'sku' => 'RAM-DDR5-STD',
        'price' => 89.99,
        'cost_price' => 60.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => false,
    ]);

    // Check index view includes this untracked product
    $indexResponse = $this->actingAs($admin, 'backend')->get(route('admin.serial-numbers.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertSee('Standard DDR5 Memory Module');

    // Ingest serial numbers for this product
    $storeResponse = $this->actingAs($admin, 'backend')->post(route('admin.serial-numbers.store'), [
        'product_id' => $untrackedProduct->id,
        'warehouse_id' => $this->warehouse->id,
        'serials_text' => "RAM-SN-001\nRAM-SN-002",
        'cost_price' => 60.00,
    ]);

    $storeResponse->assertRedirect(route('admin.serial-numbers.index'));
    expect($untrackedProduct->fresh()->requires_serial_tracking)->toBeTrue();
    expect(SerialNumber::where('product_id', $untrackedProduct->id)->count())->toBe(2);
});

it('prevents ingesting duplicate serial numbers in a single batch', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Corsair Dominator Titanium DDR5',
        'slug' => 'corsair-dominator-titanium-ddr5',
        'sku' => 'RAM-COR-DDR5',
        'price' => 220.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.serial-numbers.store'), [
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serials_text' => "SN-DUP-001\nSN-DUP-001",
        'cost_price' => 50.00,
    ]);

    $response->assertSessionHasErrors('serials_text');
    expect(SerialNumber::where('serial_number', 'SN-DUP-001')->count())->toBe(0);
});

it('prevents ingesting serial numbers that already exist in database', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'G.Skill Trident Z5 RGB',
        'slug' => 'g-skill-trident-z5-rgb',
        'sku' => 'RAM-GSK-Z5',
        'price' => 190.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
    ]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-ALREADY-EXISTS',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.serial-numbers.store'), [
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serials_text' => 'SN-ALREADY-EXISTS',
        'cost_price' => 50.00,
    ]);

    $response->assertSessionHasErrors('serials_text');
    expect(SerialNumber::where('serial_number', 'SN-ALREADY-EXISTS')->count())->toBe(1);
});

it('allows admin to delete an IN_STOCK serial number and syncs warehouse stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Seasonic Vertex GX-1000',
        'slug' => 'seasonic-vertex-gx-1000',
        'sku' => 'PSU-SEA-1000',
        'price' => 240.00,
        'stock_quantity' => 2,
        'requires_serial_tracking' => true,
    ]);

    $unit1 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SEA-GX1000-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $unit2 = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SEA-GX1000-002',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $this->serialService->ingestSerials($product, $this->warehouse, 'SEA-GX1000-003');
    expect($product->fresh()->stock_quantity)->toBe(3);

    // Delete IN_STOCK serial unit
    $response = $this->actingAs($admin, 'backend')
        ->delete(route('admin.serial-numbers.destroy', $unit1->id));

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('serial_numbers', ['id' => $unit1->id]);
    expect($product->fresh()->stock_quantity)->toBe(2);
});

it('prevents deleting a serial number when status is not IN_STOCK', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'NVIDIA RTX A6000 Ada',
        'slug' => 'nvidia-rtx-a6000-ada',
        'sku' => 'GPU-NV-A6000',
        'price' => 6800.00,
        'stock_quantity' => 1,
        'requires_serial_tracking' => true,
    ]);

    // Test with ALLOCATED
    $allocatedUnit = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'A6000-ALLOC-99',
        'status' => SerialNumber::STATUS_ALLOCATED,
    ]);

    $responseAlloc = $this->actingAs($admin, 'backend')
        ->delete(route('admin.serial-numbers.destroy', $allocatedUnit->id));

    $responseAlloc->assertRedirect(route('admin.serial-numbers.index'));
    $responseAlloc->assertSessionHas('error');
    $this->assertDatabaseHas('serial_numbers', ['id' => $allocatedUnit->id]);

    // Test with OTHER
    $otherUnit = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'A6000-OTHER-99',
        'status' => SerialNumber::STATUS_OTHER,
    ]);

    $responseOther = $this->actingAs($admin, 'backend')
        ->delete(route('admin.serial-numbers.destroy', $otherUnit->id));

    $responseOther->assertRedirect(route('admin.serial-numbers.index'));
    $responseOther->assertSessionHas('error');
    $this->assertDatabaseHas('serial_numbers', ['id' => $otherUnit->id]);
});

it('allows updating a serial number status to OTHER and filtering by OTHER status', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Samsung 990 PRO 2TB',
        'slug' => 'samsung-990-pro-2tb',
        'sku' => 'SSD-SAM-990P-2TB',
        'price' => 180.00,
        'stock_quantity' => 1,
    ]);

    $unit = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SAM-990P-MISC',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Update status to OTHER
    $updateResponse = $this->actingAs($admin, 'backend')
        ->put(route('admin.serial-numbers.update', $unit->id), [
            'status' => 'OTHER',
            'notes' => 'Internal lab testing sample',
        ]);

    $updateResponse->assertRedirect(route('admin.serial-numbers.index'));
    $updateResponse->assertSessionHas('success');

    expect($unit->fresh()->status)->toBe(SerialNumber::STATUS_OTHER);

    // Verify index page displays OTHER badge and disabled delete button for OTHER
    $indexResponse = $this->actingAs($admin, 'backend')
        ->get(route('admin.serial-numbers.index', ['status' => 'OTHER']));

    $indexResponse->assertOk();
    $indexResponse->assertSee('SAM-990P-MISC');
    $indexResponse->assertSee('<span class="badge bg-secondary">OTHER</span>', false);
    $indexResponse->assertSee('disabled title="Only IN_STOCK serial numbers can be deleted', false);
});

it('gracefully shows an error message when ingesting serial numbers that already exist on product edit page', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Corsair Dominator Titanium DDR5',
        'slug' => 'corsair-dominator-ddr5',
        'sku' => 'RAM-COR-DDR5-64G',
        'price' => 320.00,
        'stock_quantity' => 1,
        'requires_serial_tracking' => true,
    ]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'COR-DDR5-EXIST-01',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->from(route('admin.products.edit', $product->id))
        ->post(route('admin.products.serials.store', $product->id), [
            'warehouse_id' => $this->warehouse->id,
            'serials_text' => "COR-DDR5-EXIST-01\nCOR-DDR5-NEW-02",
        ]);

    $response->assertRedirect(route('admin.products.edit', $product->id));
    $response->assertSessionHasErrors(['serials_text']);

    // Ensure duplicate was not added again
    expect(SerialNumber::where('serial_number', 'COR-DDR5-EXIST-01')->count())->toBe(1);
    expect(SerialNumber::where('serial_number', 'COR-DDR5-NEW-02')->count())->toBe(0);
});

it('gracefully shows an error message when ingesting serial numbers that already exist on serial registry page', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'G.Skill Trident Z5 Neo',
        'slug' => 'gskill-trident-z5-neo',
        'sku' => 'RAM-GSK-Z5-32G',
        'price' => 180.00,
        'stock_quantity' => 1,
        'requires_serial_tracking' => true,
    ]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'GSK-Z5-EXIST-01',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->from(route('admin.serial-numbers.index'))
        ->post(route('admin.serial-numbers.store'), [
            'product_id' => $product->id,
            'warehouse_id' => $this->warehouse->id,
            'serials_text' => "GSK-Z5-EXIST-01\nGSK-Z5-NEW-02",
        ]);

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHasErrors(['serials_text']);

    expect(SerialNumber::where('serial_number', 'GSK-Z5-EXIST-01')->count())->toBe(1);
});

it('returns JSON error response for AJAX requests when serial numbers already exist on product edit', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Kingston Fury Renegade DDR5',
        'slug' => 'kingston-fury-renegade-ddr5',
        'sku' => 'RAM-KNG-FURY-32G',
        'price' => 210.00,
        'stock_quantity' => 1,
        'requires_serial_tracking' => true,
    ]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'KNG-FURY-EXIST-99',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Send AJAX request (expects JSON)
    $response = $this->actingAs($admin, 'backend')
        ->postJson(route('admin.products.serials.store', $product->id), [
            'warehouse_id' => $this->warehouse->id,
            'serials_text' => "KNG-FURY-EXIST-99\nKNG-FURY-NEW-100",
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['serials_text']);
    $response->assertJsonFragment([
        'message' => 'The following serial number(s) already exist in inventory: KNG-FURY-EXIST-99',
    ]);
});

it('returns JSON success response with new records for AJAX requests on product edit', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'TeamGroup T-Force Delta DDR5',
        'slug' => 'teamgroup-tforce-delta-ddr5',
        'sku' => 'RAM-TMG-DELTA-32G',
        'price' => 195.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->postJson(route('admin.products.serials.store', $product->id), [
            'warehouse_id' => $this->warehouse->id,
            'serials_text' => "TMG-DELTA-001\nTMG-DELTA-002",
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'count' => 2,
    ]);

    expect(SerialNumber::where('product_id', $product->id)->count())->toBe(2);
});

it('allows ingesting serial numbers via file upload directly on product edit', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'Kingston Fury Beast DDR5 64GB',
        'slug' => 'kingston-fury-beast-ddr5-64gb',
        'sku' => 'RAM-KNG-FURY-64G',
        'price' => 320.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
    ]);

    $csvContent = "serial_number\nKF-UPLOAD-001\nKF-UPLOAD-002\nKF-UPLOAD-003";
    $file = UploadedFile::fake()->createWithContent('serials.csv', $csvContent);

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.products.serials.store', $product->id), [
            'warehouse_id' => $this->warehouse->id,
            'file' => $file,
        ]);

    $response->assertSessionHas('success');
    expect(SerialNumber::where('product_id', $product->id)->count())->toBe(3);
});

it('only adds serial numbers with status IN_STOCK to product stock quantity', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $superadminRole = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    $admin->assignRole($superadminRole);

    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'RTX 4090 OC Gaming',
        'slug' => 'rtx-4090-oc-gaming',
        'sku' => 'GPU-4090-OC',
        'price' => 1799.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
    ]);

    // 2 IN_STOCK, 1 SHIPPED (sold), 1 DEFECTIVE_SCRAP, 1 RETURNED_RMA
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'GPU-4090-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'GPU-4090-002',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'GPU-4090-003',
        'status' => SerialNumber::STATUS_SHIPPED,
    ]);
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'GPU-4090-004',
        'status' => SerialNumber::STATUS_DEFECTIVE_SCRAP,
    ]);
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'GPU-4090-005',
        'status' => SerialNumber::STATUS_RETURNED_RMA,
    ]);

    // Sync via model
    $product->syncTotalStock();
    expect($product->fresh()->stock_quantity)->toBe(2);

    // Update via ProductController
    $response = $this->actingAs($admin, 'backend')
        ->put(route('admin.products.update', $product->id), [
            'category_id' => $this->category->id,
            'name' => 'RTX 4090 OC Gaming (Updated)',
            'sku' => 'GPU-4090-OC',
            'price' => 1799.00,
            'stock_quantity' => 99, // Should be ignored and strictly match IN_STOCK count (2)
            'low_stock_threshold' => 1,
            'requires_serial_tracking' => true,
        ]);

    $response->assertRedirect(route('admin.products.index'));
    expect($product->fresh()->stock_quantity)->toBe(2);

    // Also check edit view displays 2 in stock out of 5 total
    $editView = $this->actingAs($admin, 'backend')->get(route('admin.products.edit', $product->id));
    $editView->assertOk();
    $editView->assertSee('2</span> in stock', false);
    $editView->assertSee('5</span> total', false);
});
