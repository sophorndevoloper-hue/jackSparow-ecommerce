<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $permissions = [
        'view dashboard',
        'view products',
        'view stock',
        'create products',
        'edit products',
    ];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'backend']);
    }

    $adminRole->syncPermissions(Permission::where('guard_name', 'backend')->get());

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole($adminRole);

    $this->warehouse = Warehouse::firstOrCreate(
        ['code' => 'WH-WAR-TEST'],
        ['name' => 'Warranty Test Depot', 'address' => '123 Tech Way', 'city' => 'Austin, TX']
    );

    $this->category = Category::firstOrCreate(['slug' => 'processors-test'], ['name' => 'Processors (CPU)']);

    $this->product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'AMD Ryzen 9 7950X3D Desktop Processor',
        'slug' => 'amd-ryzen-9-7950x3d-warranty-test',
        'sku' => 'CPU-AMD-7950X3D-WAR',
        'price' => 699.00,
        'cost_price' => 520.00,
        'stock_quantity' => 5,
        'low_stock_threshold' => 2,
        'requires_serial_tracking' => true,
        'manufacturer_warranty_months' => 36,
        'distributor_warranty_months' => 12,
        'warranty_period' => '3-Year Manufacturer Warranty',
    ]);

    $this->serial = SerialNumber::create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'WAR-SN-7950X3D-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
        'cost_price' => 520.00,
        'inbound_date' => now()->subDays(5)->toDateString(),
    ]);
});

it('renders individual printable hardware warranty certificate', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.serial-numbers.warranty', $this->serial->id));

    $response->assertStatus(200);
    $response->assertSee('Official Warranty Certificate');
    $response->assertSee('WAR-SN-7950X3D-001');
    $response->assertSee('AMD Ryzen 9 7950X3D Desktop Processor');
    $response->assertSee('CPU-AMD-7950X3D-WAR');
    $response->assertSee('Warranty Test Depot');
    $response->assertSee('3-Year Manufacturer Warranty');
    $response->assertSee('Print Warranty Certificate');
});

it('renders batch printable warranty certificates with id filtering', function () {
    $serial2 = SerialNumber::create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'WAR-SN-7950X3D-002',
        'status' => SerialNumber::STATUS_SHIPPED,
        'cost_price' => 520.00,
        'inbound_date' => now()->subDays(10)->toDateString(),
        'warranty_start_date' => now()->subDays(2)->toDateString(),
        'warranty_end_date' => now()->addMonths(36)->toDateString(),
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.serial-numbers.warranties.print', [
            'ids' => "{$this->serial->id},{$serial2->id}",
        ]));

    $response->assertStatus(200);
    $response->assertSee('WAR-SN-7950X3D-001');
    $response->assertSee('WAR-SN-7950X3D-002');
    $response->assertSee('Print All Certificates (2)');
    $response->assertSee('Active Warranty Coverage');
});

it('redirects to index with error when no serial numbers match batch warranty query', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.serial-numbers.warranties.print', [
            'search' => 'NON_EXISTENT_SERIAL_QUERY_12345',
        ]));

    $response->assertRedirect(route('admin.serial-numbers.index'));
    $response->assertSessionHas('error');
});
