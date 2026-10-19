<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
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

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole($adminRole);
    $this->warehouse = Warehouse::create([
        'name' => 'Main Logistics Center',
        'code' => 'WH-MLC1',
        'is_default' => true,
        'is_active' => true,
    ]);
});

it('downloads starter Excel template successfully', function () {
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.products.import.template', ['format' => 'xlsx']));

    $response->assertSuccessful();
    expect($response->headers->get('content-type'))->toContain('spreadsheetml.sheet');
    expect($response->headers->get('content-disposition'))->toContain('products_import_template.xlsx');
});

it('rejects non-Excel files during product import', function () {
    $file = UploadedFile::fake()->create('products.csv', 100, 'text/csv');

    $response = $this->actingAs($this->admin, 'backend')
        ->from(route('admin.products.index'))
        ->post(route('admin.products.import'), [
            'file' => $file,
        ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHasErrors('file');
});

it('successfully imports the popular_laptops_2026.xlsx Excel file', function () {
    $filePath = base_path('popular_laptops_2026.xlsx');
    expect(file_exists($filePath))->toBeTrue();

    $file = new UploadedFile($filePath, 'popular_laptops_2026.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.products.import'), [
            'file' => $file,
            'default_warehouse_id' => $this->warehouse->id,
            'update_existing' => 1,
        ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');

    // Verify all 10 laptops exist in database
    $skus = [
        'LAP-APP-MBP16-M4P',
        'LAP-ASU-G16-4080',
        'LAP-LEN-LEG7I-4090',
        'LAP-DEL-XPS16-9640',
        'LAP-RAZ-BLADE16-4090',
        'LAP-LEN-X1CARB-G13',
        'LAP-HP-SPEC16-2IN1',
        'LAP-MSI-TITAN18-HX',
        'LAP-ASU-ZENDUO-OLED',
        'LAP-APP-MBA15-M3',
    ];

    expect(Product::whereIn('sku', $skus)->count())->toBe(10);

    // Verify categories and brands were registered
    expect(Category::whereIn('name', ['Laptops', 'Gaming Laptops', 'Ultrabooks', 'Business Laptops', 'Convertible 2-in-1'])->count())->toBeGreaterThanOrEqual(2);
    expect(Brand::whereIn('name', ['Apple', 'ASUS', 'Lenovo', 'Dell', 'Razer', 'HP', 'MSI'])->count())->toBe(7);

    // Verify serialized tracking, prices, extended technical specifications, and logistics
    foreach ($skus as $sku) {
        $laptop = Product::with('priceTiers')->where('sku', $sku)->first();
        expect($laptop->requires_serial_tracking)->toBeTrue();
        expect($laptop->stock_quantity)->toBe(0);
        expect((float) $laptop->price)->toBe(600.0);
        expect((float) $laptop->cost_price)->toBe(500.0);
        expect((float) $laptop->map_price)->toBe(579.0);
        expect((float) $laptop->min_margin_percentage)->toBe(10.0);
        expect($laptop->hs_code)->toBe('8471.30.01');
        expect($laptop->unspsc_code)->toBe('43211503');
        expect($laptop->is_hazmat)->toBeTrue();
        expect($laptop->weight_kg)->not->toBeNull();
        expect($laptop->socket)->not->toBeNull();
        expect($laptop->power_requirement_watts)->not->toBeNull();
        expect($laptop->manufacturer_warranty_months)->not->toBeNull();
        expect($laptop->distributor_warranty_months)->toBe(12);

        // Verify extended technical specifications are populated
        $specs = $laptop->specs ?: $laptop->specifications;
        expect($specs)->toBeArray()->not->toBeEmpty();
        expect($specs)->toHaveKey('Processor (CPU)');
        expect($specs)->toHaveKey('Graphics (GPU)');
        expect($specs)->toHaveKey('Memory (RAM)');
        expect($specs)->toHaveKey('Storage (SSD/HDD)');
        expect($specs)->toHaveKey('Display Screen');

        // Verify volume pricing tiers
        expect($laptop->priceTiers)->not->toBeEmpty();
    }

    // Verify editing product loads successfully with all relations (including priceTiers.customerGroup)
    $sampleProduct = Product::where('sku', 'LAP-RAZ-BLADE16-4090')->first();
    $editResponse = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.products.edit', $sampleProduct->id));
    $editResponse->assertSuccessful();
    $editResponse->assertSee($sampleProduct->name);
    $editResponse->assertSee('8471.30.01');
    $editResponse->assertSee('BGA-FP8 (Soldered)');
    $editResponse->assertSee('Processor (CPU)');
});
