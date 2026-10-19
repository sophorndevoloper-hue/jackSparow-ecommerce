<?php

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Permission;
use App\Models\ShippingMethod;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'ShippingMethodSeeder']);
    $this->artisan('db:seed', ['--class' => 'BackendMenuSeeder']);
});

it('renders Deliveries and Shipping Methods in sidebar for admin', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Deliveries');
    $response->assertSee('Shipping Methods');
});

it('allows admin to view, create, toggle, and update shipping methods', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // 1. View Index
    $indexResponse = $this->actingAs($admin, 'backend')->get(route('admin.shipping-methods.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertSee('Standard Ground Delivery');
    $indexResponse->assertSee('Express Priority Air');

    // 2. Store new shipping method
    $storeResponse = $this->actingAs($admin, 'backend')->post(route('admin.shipping-methods.store'), [
        'name' => 'Drone Same-Day Courier',
        'code' => 'drone_courier',
        'carrier' => 'SkyDrop Logistics',
        'base_cost' => 45.00,
        'min_order_amount' => 200.00,
        'estimated_days' => 'Within 3 hours',
        'description' => 'Ultra-fast automated drone delivery for graphics cards and processors.',
        'is_active' => true,
        'sort_order' => 5,
    ]);
    $storeResponse->assertRedirect(route('admin.shipping-methods.index'));

    $newMethod = ShippingMethod::where('code', 'drone_courier')->first();
    expect($newMethod)->not->toBeNull();
    expect((float) $newMethod->base_cost)->toBe(45.00);

    // 3. Toggle Active status
    $toggleResponse = $this->actingAs($admin, 'backend')
        ->patch(route('admin.shipping-methods.toggle-active', $newMethod->id));
    $toggleResponse->assertRedirect();
    expect($newMethod->fresh()->is_active)->toBeFalse();

    // 4. Update method
    $updateResponse = $this->actingAs($admin, 'backend')
        ->put(route('admin.shipping-methods.update', $newMethod->id), [
            'name' => 'Drone Express Delivery',
            'code' => 'drone_courier',
            'carrier' => 'SkyDrop Logistics Direct',
            'base_cost' => 40.00,
            'is_active' => true,
        ]);
    $updateResponse->assertRedirect(route('admin.shipping-methods.index'));
    expect($newMethod->fresh()->name)->toBe('Drone Express Delivery');
    expect((float) $newMethod->fresh()->base_cost)->toBe(40.00);
});

it('allows admin to dispatch, track, and update order deliveries', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $order = Order::factory()->create([
        'status' => 'pending',
        'customer_name' => 'Jonathan Hardware',
        'customer_email' => 'jonathan@custompc.test',
        'customer_phone' => '+1 (555) 345-6789',
    ]);

    $shippingMethod = ShippingMethod::where('code', 'standard')->first();

    // 1. Access Dispatch Create Form
    $createResponse = $this->actingAs($admin, 'backend')
        ->get(route('admin.deliveries.create', ['order_id' => $order->id]));
    $createResponse->assertStatus(200);
    $createResponse->assertSee($order->order_number);

    // 2. Dispatch Delivery
    $storeResponse = $this->actingAs($admin, 'backend')
        ->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'shipping_method_id' => $shippingMethod->id,
            'carrier' => 'FedEx Express',
            'tracking_number' => 'TRK-FDX-998877',
            'status' => 'dispatched',
            'recipient_name' => 'Jonathan Hardware',
            'recipient_phone' => '+1 (555) 345-6789',
            'recipient_email' => 'jonathan@custompc.test',
            'street' => '742 Evergreen Terrace',
            'city' => 'Springfield',
            'state' => 'IL',
            'postal_code' => '62704',
            'country' => 'United States',
            'estimated_delivery_date' => now()->addDays(2)->toDateString(),
            'notes' => 'Signature required upon delivery.',
        ]);

    $delivery = Delivery::where('tracking_number', 'TRK-FDX-998877')->first();
    expect($delivery)->not->toBeNull();
    $storeResponse->assertRedirect(route('admin.deliveries.show', $delivery->id));

    // Order status should have synced to 'shipped'
    expect($order->fresh()->status)->toBe('shipped');
    expect($delivery->dispatched_at)->not->toBeNull();

    // 3. View Delivery Details
    $showResponse = $this->actingAs($admin, 'backend')
        ->get(route('admin.deliveries.show', $delivery->id));
    $showResponse->assertStatus(200);
    $showResponse->assertSee('TRK-FDX-998877');
    $showResponse->assertSee('FedEx Express');
    $showResponse->assertSee('Dispatched');

    // 4. Quick Status Update to Delivered
    $statusResponse = $this->actingAs($admin, 'backend')
        ->patch(route('admin.deliveries.status', $delivery->id), [
            'status' => 'delivered',
        ]);
    $statusResponse->assertRedirect();
    expect($delivery->fresh()->status)->toBe('delivered');
    expect($delivery->fresh()->delivered_at)->not->toBeNull();
    expect($order->fresh()->status)->toBe('delivered');
});

it('protects delivery and shipping method management with permissions', function () {
    $staff = User::factory()->create(['is_approved' => true]);
    // Staff only has view dashboard permission
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));

    // Forbidden: Deliveries
    $this->actingAs($staff, 'backend')
        ->get(route('admin.deliveries.index'))
        ->assertStatus(403);

    // Forbidden: Shipping Methods
    $this->actingAs($staff, 'backend')
        ->get(route('admin.shipping-methods.index'))
        ->assertStatus(403);
});
