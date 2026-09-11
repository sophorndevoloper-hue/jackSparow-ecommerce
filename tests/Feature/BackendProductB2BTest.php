<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);

    $this->category = Category::firstOrCreate(['slug' => 'processors-cpu'], ['name' => 'Processors (CPU)']);
    $this->brand = Brand::firstOrCreate(['slug' => 'amd'], ['name' => 'AMD']);
    $this->warehouse = Warehouse::firstOrCreate(['code' => 'WH_SF'], ['name' => 'San Francisco Central Hub', 'location' => 'CA']);
});

it('allows admin to create hardware product with B2B identifiers and tier pricing', function () {
    $group = CustomerGroup::create([
        'name' => 'System Integrator Pro',
        'slug' => 'system-integrator-pro',
        'code' => 'SI_PRO',
        'default_discount_percentage' => 12.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.products.store'), [
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'AMD Ryzen 9 9950X Desktop Processor',
        'sku' => 'CPU-AMD-9950X',
        'mpn' => '100-100001277WOF',
        'upc_ean' => '730143315999',
        'hs_code' => '8473.30.1180',
        'price' => 649.00,
        'cost_price' => 490.00,
        'map_price' => 649.00,
        'min_margin_percentage' => 12.00,
        'moq' => 2,
        'case_pack_multiple' => 5,
        'stock_quantity' => 25,
        'low_stock_threshold' => 5,
        'socket' => 'AM5',
        'chipset' => 'X870E',
        'tdp_watts' => 170,
        'requires_serial_tracking' => true,
        'spec_keys' => ['Cores / Threads', 'Base Clock'],
        'spec_values' => ['16 Cores / 32 Threads', '4.3 GHz'],
        'tier_customer_group_id' => [$group->id],
        'tier_min_qty' => [10],
        'tier_max_qty' => [49],
        'tier_price' => [560.00],
        'action' => 'save',
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'CPU-AMD-9950X')->first();
    expect($product)->not->toBeNull();
    expect($product->mpn)->toBe('100-100001277WOF');
    expect($product->socket)->toBe('AM5');
    expect($product->tdp_watts)->toBe(170);
    expect($product->requires_serial_tracking)->toBeTrue();
    expect($product->specifications['Cores / Threads'])->toBe('16 Cores / 32 Threads');

    // Verify created pricing tier
    $tier = ProductPriceTier::where('product_id', $product->id)->first();
    expect($tier)->not->toBeNull();
    expect((float) $tier->unit_price)->toBe(560.00);
    expect($tier->min_quantity)->toBe(10);
    expect($tier->customer_group_id)->toBe($group->id);
});

it('allows admin to manage customer groups via backend CRUD', function () {
    $createResponse = $this->actingAs($this->admin, 'backend')->post(route('admin.customer-groups.store'), [
        'name' => 'Government & Defense IT',
        'code' => 'GOV_DEFENSE',
        'default_discount_percentage' => 20.00,
        'min_order_amount' => 10000.00,
        'credit_limit' => 250000.00,
        'payment_terms_days' => 60,
        'tax_exempt' => 1,
    ]);

    $createResponse->assertRedirect(route('admin.customer-groups.index'));

    $group = CustomerGroup::where('code', 'GOV_DEFENSE')->first();
    expect($group)->not->toBeNull();
    expect($group->tax_exempt)->toBeTrue();
    expect((float) $group->default_discount_percentage)->toBe(20.00);

    // Update group
    $updateResponse = $this->actingAs($this->admin, 'backend')->put(route('admin.customer-groups.update', $group->id), [
        'name' => 'Gov IT Division',
        'code' => 'GOV_DEFENSE',
        'default_discount_percentage' => 22.00,
    ]);

    $updateResponse->assertRedirect(route('admin.customer-groups.index'));
    expect($group->fresh()->name)->toBe('Gov IT Division');
    expect((float) $group->fresh()->default_discount_percentage)->toBe(22.00);
});

it('allows admin to batch ingest serial numbers from product edit screen', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'name' => 'GeForce RTX 4080 Serial Test',
        'slug' => 'geforce-rtx-4080-serial-test',
        'sku' => 'GPU-4080-STEST',
        'price' => 1199.00,
        'cost_price' => 950.00,
        'stock_quantity' => 0,
        'low_stock_threshold' => 2,
        'requires_serial_tracking' => true,
    ]);

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.products.serials.store', $product->id), [
        'warehouse_id' => $this->warehouse->id,
        'serials_text' => "SN-4080-101\nSN-4080-102\nSN-4080-103",
        'cost_price' => 950.00,
    ]);

    $response->assertRedirect();

    expect(SerialNumber::where('product_id', $product->id)->count())->toBe(3);
    expect($product->fresh()->stock_quantity)->toBe(3);
});

it('alerts with error message when entering total stock qty more than serial numbers on update', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'Ryzen 7 7800X3D Processor',
        'slug' => 'ryzen-7-7800x3d',
        'sku' => 'CPU-7800X3D',
        'price' => 449.00,
        'stock_quantity' => 2,
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    // Create 2 serial numbers
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-7800X3D-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-7800X3D-002',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Attempt to update with stock quantity 5, it should automatically match 2 serial numbers
    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.products.update', $product->id), [
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'Ryzen 7 7800X3D Processor',
        'sku' => 'CPU-7800X3D',
        'price' => 449.00,
        'stock_quantity' => 5, // 5 > 2
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');
    expect($product->fresh()->stock_quantity)->toBe(2);
});

it('automatically matches total stock qty with serial numbers count on update when serialized', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'Ryzen 9 7900X Processor',
        'slug' => 'ryzen-9-7900x',
        'sku' => 'CPU-7900X',
        'price' => 549.00,
        'stock_quantity' => 2,
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    // Create 2 serial numbers
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-7900X-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);
    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-7900X-002',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    // Attempt to update with stock quantity 1, it should automatically match 2 serial numbers
    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.products.update', $product->id), [
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'Ryzen 9 7900X Processor',
        'sku' => 'CPU-7900X',
        'price' => 549.00,
        'stock_quantity' => 1, // 1 < 2
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');
    expect($product->fresh()->stock_quantity)->toBe(2);
});

it('allows updating product when total stock qty matches serial numbers count', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'Ryzen 5 7600 Processor',
        'slug' => 'ryzen-5-7600',
        'sku' => 'CPU-7600',
        'price' => 229.00,
        'stock_quantity' => 1,
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-7600-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.products.update', $product->id), [
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'Ryzen 5 7600 Processor (Updated)',
        'sku' => 'CPU-7600',
        'price' => 219.00,
        'stock_quantity' => 1, // Matches 1 serial
        'low_stock_threshold' => 1,
        'requires_serial_tracking' => true,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');
    expect($product->fresh()->name)->toBe('Ryzen 5 7600 Processor (Updated)');
    expect($product->fresh()->stock_quantity)->toBe(1);
});
