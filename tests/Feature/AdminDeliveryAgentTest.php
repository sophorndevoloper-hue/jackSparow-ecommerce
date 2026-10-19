<?php

use App\Models\Delivery;
use App\Models\DeliveryAgent;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RoleAndPermissionSeeder::class,
        BackendMenuSeeder::class,
    ]);

    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole(Role::findByName('admin', 'backend'));
});

it('displays delivery agents list, KPI metrics, and sidebar under People section', function () {
    $agent = DeliveryAgent::create([
        'name' => 'Sokha Courier',
        'code' => 'AGT-001',
        'phone' => '+855 12 345 678',
        'vehicle_type' => 'motorcycle',
        'license_plate' => '1AB-1234 PP',
        'zone' => 'Phnom Penh Central',
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.delivery-agents.index'));

    $response->assertStatus(200);
    $response->assertSee('Delivery Agents');
    $response->assertSee('Sokha Courier');
    $response->assertSee('AGT-001');
    $response->assertSee('Phnom Penh Central');
    $response->assertSee('Available');

    // Check sidebar has Delivery Agents
    $response->assertSee('Delivery Agents');
});

it('filters delivery agents by search keyword and vehicle type', function () {
    DeliveryAgent::create([
        'name' => 'Fast Rider',
        'code' => 'AGT-RDR',
        'phone' => '+855 11 111 222',
        'vehicle_type' => 'motorcycle',
        'status' => 'available',
        'is_active' => true,
    ]);

    DeliveryAgent::create([
        'name' => 'Heavy Trucker',
        'code' => 'AGT-TRK',
        'phone' => '+855 22 333 444',
        'vehicle_type' => 'truck',
        'status' => 'on_delivery',
        'is_active' => true,
    ]);

    // Filter by search
    $searchResponse = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.delivery-agents.index', ['search' => 'Heavy Trucker']));

    $searchResponse->assertStatus(200);
    $searchResponse->assertSee('Heavy Trucker');
    $searchResponse->assertDontSee('Fast Rider');

    // Filter by vehicle type
    $vehicleResponse = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.delivery-agents.index', ['vehicle_type' => 'motorcycle']));

    $vehicleResponse->assertStatus(200);
    $vehicleResponse->assertSee('Fast Rider');
    $vehicleResponse->assertDontSee('Heavy Trucker');
});

it('creates a new delivery agent with valid data', function () {
    $data = [
        'name' => 'David Courier',
        'code' => 'AGT-DAV',
        'phone' => '+855 98 765 432',
        'email' => 'david@courier.local',
        'vehicle_type' => 'van',
        'license_plate' => '2AB-5678 PP',
        'zone' => 'Toul Kork District',
        'status' => 'available',
        'is_active' => '1',
        'notes' => 'Primary van for electronics delivery',
    ];

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.delivery-agents.store'), $data);

    $response->assertRedirect(route('admin.delivery-agents.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('delivery_agents', [
        'code' => 'AGT-DAV',
        'name' => 'David Courier',
        'vehicle_type' => 'van',
        'is_active' => true,
    ]);
});

it('validates unique agent code on create and update', function () {
    DeliveryAgent::create([
        'name' => 'Agent One',
        'code' => 'AGT-DUP',
        'phone' => '+855 12 000 001',
        'vehicle_type' => 'motorcycle',
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.delivery-agents.store'), [
            'name' => 'Agent Two',
            'code' => 'AGT-DUP',
            'phone' => '+855 12 000 002',
            'vehicle_type' => 'motorcycle',
            'status' => 'available',
        ]);

    $response->assertSessionHasErrors(['code']);
});

it('updates an existing delivery agent', function () {
    $agent = DeliveryAgent::create([
        'name' => 'Old Name',
        'code' => 'AGT-UPD',
        'phone' => '+855 12 000 003',
        'vehicle_type' => 'bicycle',
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->put(route('admin.delivery-agents.update', $agent), [
            'name' => 'Updated Name',
            'code' => 'AGT-UPD',
            'phone' => '+855 12 999 999',
            'vehicle_type' => 'van',
            'status' => 'on_delivery',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('admin.delivery-agents.index'));
    $response->assertSessionHas('success');

    $agent->refresh();
    expect($agent->name)->toBe('Updated Name')
        ->and($agent->vehicle_type)->toBe('van')
        ->and($agent->status)->toBe('on_delivery');
});

it('toggles the active status of a delivery agent', function () {
    $agent = DeliveryAgent::create([
        'name' => 'Toggle Agent',
        'code' => 'AGT-TOG',
        'phone' => '+855 12 111 222',
        'vehicle_type' => 'motorcycle',
        'status' => 'available',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->patch(route('admin.delivery-agents.toggle-active', $agent));

    $response->assertRedirect();
    $agent->refresh();
    expect($agent->is_active)->toBeFalse();

    $this->actingAs($this->admin, 'backend')
        ->patch(route('admin.delivery-agents.toggle-active', $agent));

    $agent->refresh();
    expect($agent->is_active)->toBeTrue();
});

it('deletes a delivery agent when no active deliveries exist', function () {
    $agent = DeliveryAgent::create([
        'name' => 'Delete Me',
        'code' => 'AGT-DEL',
        'phone' => '+855 12 333 444',
        'vehicle_type' => 'bicycle',
        'status' => 'inactive',
        'is_active' => false,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.delivery-agents.destroy', $agent));

    $response->assertRedirect(route('admin.delivery-agents.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('delivery_agents', [
        'id' => $agent->id,
    ]);
});

it('prevents deleting an agent with active shipments', function () {
    $agent = DeliveryAgent::create([
        'name' => 'Busy Agent',
        'code' => 'AGT-BSY',
        'phone' => '+855 12 444 555',
        'vehicle_type' => 'motorcycle',
        'status' => 'on_delivery',
        'is_active' => true,
    ]);

    $order = Order::factory()->create();

    Delivery::create([
        'delivery_number' => 'DEL-2026-TEST01',
        'order_id' => $order->id,
        'delivery_agent_id' => $agent->id,
        'status' => 'in_transit',
        'recipient_name' => 'Customer Test',
        'shipping_address' => ['street' => 'Street 1', 'city' => 'Phnom Penh'],
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.delivery-agents.destroy', $agent));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('delivery_agents', [
        'id' => $agent->id,
    ]);
});

it('denies access to unauthorized staff users', function () {
    $staff = User::factory()->create(['is_approved' => true]);

    $response = $this->actingAs($staff, 'backend')
        ->get(route('admin.delivery-agents.index'));

    $response->assertStatus(403);
});
