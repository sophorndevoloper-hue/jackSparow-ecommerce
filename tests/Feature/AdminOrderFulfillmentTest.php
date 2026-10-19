<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\WarehouseSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        BackendMenuSeeder::class,
        WarehouseSeeder::class,
    ]);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole(Role::findByName('admin', 'backend'));
});

it('displays warehouse fulfillment queue and progress metrics', function () {
    $product = Product::factory()->create(['requires_serial_tracking' => true]);
    $order = Order::factory()->create(['status' => 'confirmed']);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Intel Core i9-14900K',
        'product_sku' => 'CPU-14900K',
        'unit_price' => 580.00,
        'quantity' => 2,
        'total_price' => 1160.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.fulfillment.index'));

    $response->assertStatus(200);
    $response->assertSee('Order Fulfillment & RMA Tracking');
    $response->assertSee($order->order_number);
    $response->assertSee('0/2');
    $response->assertSee('Manage Sale');
});

it('displays fulfillment scanning workstation with required serial progress', function () {
    $product = Product::factory()->create([
        'name' => 'GeForce RTX 4080',
        'sku' => 'GPU-4080',
        'requires_serial_tracking' => true,
        'manufacturer_warranty_months' => 36,
    ]);

    $order = Order::factory()->create(['status' => 'confirmed']);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'GeForce RTX 4080',
        'product_sku' => 'GPU-4080',
        'unit_price' => 1100.00,
        'quantity' => 1,
        'total_price' => 1100.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.fulfillment.show', $order->id));

    $response->assertStatus(200);
    $response->assertSee('Fulfillment Workstation: Order #'.$order->order_number);
    $response->assertSee('Hardware Serial Scanner');
    $response->assertSee('0 / 1 Serials Verified');
});

it('scans and attaches a hardware serial number: verifies match, updates status to sold, and sets 36 months warranty', function () {
    $product = Product::factory()->create([
        'name' => 'AMD Ryzen 9 7950X3D',
        'sku' => 'CPU-7950X3D',
        'requires_serial_tracking' => true,
    ]);

    $order = Order::factory()->create(['status' => 'confirmed']);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'AMD Ryzen 9 7950X3D',
        'product_sku' => 'CPU-7950X3D',
        'unit_price' => 650.00,
        'quantity' => 1,
        'total_price' => 650.00,
    ]);

    $serial = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'serial_number' => 'SN-7950X3D-998811',
        'status' => SerialNumber::STATUS_IN_STOCK,
        'cost_price' => 450.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.fulfillment.attach-serial', $order->id), [
            'order_item_id' => $item->id,
            'serial_number' => 'SN-7950X3D-998811',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $serial->refresh();

    // 1. Verify matched reserved item
    expect($serial->order_id)->toBe($order->id)
        ->and($serial->order_item_id)->toBe($item->id)
        // 2. Update status to 'sold'
        ->and($serial->status)->toBe('sold')
        // 3. Set warranty_start_date = CURRENT_DATE and warranty_end_date = CURRENT_DATE + interval '36 months'
        ->and($serial->warranty_start_date?->toDateString())->toBe(now()->toDateString())
        ->and($serial->warranty_end_date?->toDateString())->toBe(now()->addMonths(36)->toDateString());
});

it('fails verification when scanned serial does not match any reserved items in order', function () {
    $orderedProduct = Product::factory()->create(['requires_serial_tracking' => true]);
    $differentProduct = Product::factory()->create(['name' => 'Unrelated Product', 'requires_serial_tracking' => true]);

    $order = Order::factory()->create(['status' => 'confirmed']);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $orderedProduct->id,
        'product_name' => 'Ordered Item',
        'product_sku' => 'ORD-001',
        'unit_price' => 100.00,
        'quantity' => 1,
        'total_price' => 100.00,
    ]);

    $unrelatedSerial = SerialNumber::create([
        'product_id' => $differentProduct->id,
        'warehouse_id' => 1,
        'serial_number' => 'SN-DIFFERENT-PROD',
        'status' => SerialNumber::STATUS_IN_STOCK,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.fulfillment.attach-serial', $order->id), [
            'serial_number' => 'SN-DIFFERENT-PROD',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $unrelatedSerial->refresh();
    expect($unrelatedSerial->order_id)->toBeNull()
        ->and($unrelatedSerial->status)->toBe(SerialNumber::STATUS_IN_STOCK);
});

it('fails verification when scanned serial is already sold or allocated to another order', function () {
    $product = Product::factory()->create(['requires_serial_tracking' => true]);
    $order1 = Order::factory()->create(['order_number' => 'ORD-1001', 'status' => 'confirmed']);
    $order2 = Order::factory()->create(['order_number' => 'ORD-1002', 'status' => 'confirmed']);

    OrderItem::create([
        'order_id' => $order2->id,
        'product_id' => $product->id,
        'product_name' => 'CPU Unit',
        'product_sku' => 'CPU-001',
        'unit_price' => 200.00,
        'quantity' => 1,
        'total_price' => 200.00,
    ]);

    $serial = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'order_id' => $order1->id,
        'serial_number' => 'SN-ALREADY-SOLD',
        'status' => SerialNumber::STATUS_SOLD,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.fulfillment.attach-serial', $order2->id), [
            'serial_number' => 'SN-ALREADY-SOLD',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $serial->refresh();
    expect($serial->order_id)->toBe($order1->id);
});

it('detaches a serial number and returns it to in-stock inventory', function () {
    $product = Product::factory()->create(['requires_serial_tracking' => true]);
    $order = Order::factory()->create(['status' => 'confirmed']);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Power Supply 1000W',
        'product_sku' => 'PSU-1000W',
        'unit_price' => 180.00,
        'quantity' => 1,
        'total_price' => 180.00,
    ]);

    $serial = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'serial_number' => 'SN-PSU-112233',
        'status' => SerialNumber::STATUS_SOLD,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.fulfillment.detach-serial', [$order->id, $serial->id]));

    $response->assertRedirect();
    $serial->refresh();

    expect($serial->order_id)->toBeNull()
        ->and($serial->order_item_id)->toBeNull()
        ->and($serial->status)->toBe(SerialNumber::STATUS_IN_STOCK);
});

it('blocks completing fulfillment if required hardware serials are not yet scanned', function () {
    $product = Product::factory()->create(['requires_serial_tracking' => true]);
    $order = Order::factory()->create(['status' => 'confirmed']);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'Motherboard Z790',
        'product_sku' => 'MB-Z790',
        'unit_price' => 300.00,
        'quantity' => 2,
        'total_price' => 600.00,
    ]);

    // Only 0 of 2 serials scanned
    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.fulfillment.complete', $order->id));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $order->refresh();
    expect($order->status)->toBe('confirmed');
});

it('completes fulfillment and marks order as shipped when all serials are verified', function () {
    $product = Product::factory()->create(['requires_serial_tracking' => true]);
    $order = Order::factory()->create(['status' => 'confirmed']);

    $item = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => 'NVMe SSD 4TB',
        'product_sku' => 'SSD-4TB-GEN4',
        'unit_price' => 320.00,
        'quantity' => 1,
        'total_price' => 320.00,
    ]);

    $serial = SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => 1,
        'order_id' => $order->id,
        'order_item_id' => $item->id,
        'serial_number' => 'SN-SSD-4TB-5544',
        'status' => SerialNumber::STATUS_SOLD,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.fulfillment.complete', $order->id));

    $response->assertRedirect(route('admin.fulfillment.show', $order->id));
    $response->assertSessionHas('success');

    $order->refresh();
    $serial->refresh();

    expect($order->status)->toBe('shipped')
        ->and($serial->status)->toBe(SerialNumber::STATUS_SOLD)
        ->and($serial->outbound_date)->not->toBeNull()
        ->and($order->deliveries)->toHaveCount(1);
});
