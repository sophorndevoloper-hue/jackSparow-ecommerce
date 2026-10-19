<?php

use App\Models\Category;
use App\Models\FrontendUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SerialTrackingService;
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

it('forces stock_quantity to 0 when creating a serialized product', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();

    $response = $this->actingAs($admin, 'backend')->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'RTX 4090 GPU OC',
        'sku' => 'GPU-4090-OC-1',
        'price' => 1599.99,
        'stock_quantity' => 25,
        'low_stock_threshold' => 3,
        'requires_serial_tracking' => '1',
        'action' => 'save',
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'GPU-4090-OC-1')->firstOrFail();
    expect($product->requires_serial_tracking)->toBeTrue()
        ->and($product->stock_quantity)->toBe(0);
});

it('attaches initial stock of non-serialized product to default warehouse and syncs', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create([
        'name' => 'Central Hub',
        'code' => 'WH-HUB',
        'is_default' => true,
        'is_active' => true,
    ]);

    $category = Category::factory()->create();

    $response = $this->actingAs($admin, 'backend')->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'HDMI 2.1 Ultra Cable',
        'sku' => 'CAB-HDMI-21',
        'price' => 19.99,
        'stock_quantity' => 40,
        'low_stock_threshold' => 10,
        'requires_serial_tracking' => '0',
        'action' => 'save',
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'CAB-HDMI-21')->firstOrFail();
    expect($product->requires_serial_tracking)->toBeFalse()
        ->and($product->stock_quantity)->toBe(40)
        ->and($warehouse->getProductStock($product))->toBe(40);

    // Calling syncTotalStock retains 40
    expect($product->syncTotalStock())->toBe(40);
});

it('allocates serial numbers and syncs warehouse stock on storefront checkout', function () {
    $warehouse = Warehouse::create([
        'name' => 'Main Warehouse',
        'code' => 'WH-MAIN',
        'is_default' => true,
        'is_active' => true,
    ]);

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Ryzen 7 7800X3D CPU',
        'price' => 449.00,
        'requires_serial_tracking' => true,
        'stock_quantity' => 0,
    ]);

    $serialService = app(SerialTrackingService::class);
    $serialService->ingestSerials($product, $warehouse, ['SN-CPU-001', 'SN-CPU-002', 'SN-CPU-003']);

    $product->refresh();
    expect($product->stock_quantity)->toBe(3)
        ->and($warehouse->getProductStock($product))->toBe(3);

    // Add 2 items to cart
    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => 449.00,
                'quantity' => 2,
                'stock_quantity' => 3,
                'category_name' => $category->name,
                'brand_name' => 'AMD',
            ],
        ],
    ]);

    $checkoutData = [
        'customer_name' => 'Alice Walker',
        'customer_email' => 'alice@pcbuilds.test',
        'customer_phone' => '+1 555-0144',
        'street' => '200 Tech Way',
        'city' => 'San Jose',
        'state' => 'CA',
        'postal_code' => '95110',
        'country' => 'United States',
        'payment_method' => 'cash_on_delivery',
    ];

    $customer = FrontendUser::factory()->create(['email' => 'alice@pcbuilds.test']);
    $response = $this->actingAs($customer, 'frontend')
        ->post(route('checkout.process'), $checkoutData);

    $order = Order::where('customer_email', 'alice@pcbuilds.test')->firstOrFail();
    $response->assertRedirect(route('order.success', $order->order_number));

    // Check allocated serials
    $allocatedSerials = SerialNumber::where('order_id', $order->id)->get();
    expect($allocatedSerials)->toHaveCount(2);
    foreach ($allocatedSerials as $sn) {
        expect($sn->status)->toBe(SerialNumber::STATUS_ALLOCATED);
    }

    // Remaining in-stock serials and stock quantity should be 1
    $product->refresh();
    expect($product->stock_quantity)->toBe(1)
        ->and($warehouse->getProductStock($product))->toBe(1);
});

it('fails checkout when serialized stock is insufficient', function () {
    $warehouse = Warehouse::create([
        'name' => 'Main Warehouse',
        'code' => 'WH-MAIN-2',
        'is_default' => true,
        'is_active' => true,
    ]);

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Radeon RX 7900 XTX',
        'price' => 999.00,
        'requires_serial_tracking' => true,
        'stock_quantity' => 0,
    ]);

    $serialService = app(SerialTrackingService::class);
    $serialService->ingestSerials($product, $warehouse, ['SN-GPU-999']);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => 999.00,
                'quantity' => 2, // Requesting 2 when only 1 serial exists
                'stock_quantity' => 1,
                'category_name' => $category->name,
                'brand_name' => 'AMD',
            ],
        ],
    ]);

    $checkoutData = [
        'customer_name' => 'Bob Test',
        'customer_email' => 'bob@test.com',
        'customer_phone' => '+1 555-0155',
        'street' => '123 Test St',
        'city' => 'Austin',
        'state' => 'TX',
        'postal_code' => '78701',
        'country' => 'United States',
        'payment_method' => 'cash_on_delivery',
    ];

    $customer = FrontendUser::factory()->create(['email' => 'bob@test.com']);
    $response = $this->actingAs($customer, 'frontend')
        ->post(route('checkout.process'), $checkoutData);
    $response->assertSessionHasErrors('cart');

    // No order created
    expect(Order::where('customer_email', 'bob@test.com')->exists())->toBeFalse();
});

it('blocks arbitrary numeric stock update on serialized products in StockController', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot Alpha', 'code' => 'WH-DA']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Serialized Motherboard Z790',
        'requires_serial_tracking' => true,
        'stock_quantity' => 0,
        'low_stock_threshold' => 2,
    ]);

    $serialService = app(SerialTrackingService::class);
    $serialService->ingestSerials($product, $warehouse, ['SN-MB-001', 'SN-MB-002']);

    $product->refresh();
    expect($product->stock_quantity)->toBe(2);

    // Try to arbitrarily override stock_quantity to 20
    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.update', $product->id), [
        'stock_quantity' => 20,
        'low_stock_threshold' => 2,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $product->refresh();
    expect($product->stock_quantity)->toBe(2);

    // Updating only low_stock_threshold succeeds
    $responseThreshold = $this->actingAs($admin, 'backend')->post(route('admin.stock.update', $product->id), [
        'stock_quantity' => 2,
        'low_stock_threshold' => 6,
    ]);

    $responseThreshold->assertRedirect();
    $responseThreshold->assertSessionHas('success');

    $product->refresh();
    expect($product->low_stock_threshold)->toBe(6)
        ->and($product->stock_quantity)->toBe(2);
});

it('blocks stock adjustment addition and subtraction without serials on serialized product', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $warehouse = Warehouse::create(['name' => 'Depot Beta', 'code' => 'WH-DB']);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Corsair Platinum Power Supply',
        'requires_serial_tracking' => true,
        'stock_quantity' => 0,
    ]);

    // Attempt addition without serials
    $responseAddition = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'addition',
        'reason' => 'received',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 5],
        ],
    ]);

    $responseAddition->assertRedirect();
    $responseAddition->assertSessionHas('error');

    $product->refresh();
    expect($product->stock_quantity)->toBe(0);

    // Now ingest 2 serials
    $serialService = app(SerialTrackingService::class);
    $serialService->ingestSerials($product, $warehouse, ['SN-PSU-001', 'SN-PSU-002']);
    $product->refresh();
    expect($product->stock_quantity)->toBe(2);

    // Attempt subtraction without serials
    $responseSubtraction = $this->actingAs($admin, 'backend')->post(route('admin.stock.adjustments.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'subtraction',
        'reason' => 'damaged',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ]);

    $responseSubtraction->assertRedirect();
    $responseSubtraction->assertSessionHas('error');

    $product->refresh();
    expect($product->stock_quantity)->toBe(2);
});
