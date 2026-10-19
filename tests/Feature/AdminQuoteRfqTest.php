<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
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

it('displays RFQ pipeline and list of wholesale quotes', function () {
    $quote = Quote::create([
        'quote_number' => 'RFQ-2026-TEST01',
        'client_name' => 'John Wick',
        'company_name' => 'Continental Hardware',
        'client_email' => 'john@continental.local',
        'company_tier' => 'OEM',
        'status' => 'pending_review',
        'target_margin_percent' => 20.0,
        'total_quoted_price' => 15000.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.quotes.index'));

    $response->assertStatus(200);
    $response->assertSee('RFQ-2026-TEST01');
    $response->assertSee('Continental Hardware');
    $response->assertSee('Pending Review');
    $response->assertSee('Manage Sale');
});

it('creates a custom Bill of Materials (BOM) RFQ', function () {
    $product = Product::factory()->create([
        'name' => 'NVIDIA RTX 4090 OC',
        'sku' => 'GPU-4090-OC',
        'price' => 1600.00,
        'cost_price' => 1200.00,
    ]);

    $data = [
        'company_name' => 'CyberPower Systems',
        'client_name' => 'Mark Vance',
        'client_email' => 'mark@cyberpower.local',
        'client_phone' => '+855 12 888 999',
        'company_tier' => 'OEM',
        'target_delivery_date' => now()->addDays(20)->toDateString(),
        'target_margin_percent' => 18.0,
        'client_notes' => 'Custom gaming rig contract build',
        'items' => [
            [
                'product_id' => $product->id,
                'item_name' => 'NVIDIA RTX 4090 OC',
                'item_sku' => 'GPU-4090-OC',
                'quantity' => 10,
                'target_unit_price' => 1400.00,
            ],
            [
                'product_id' => null,
                'item_name' => 'Custom Watercooling Braided Tubing Kit',
                'item_sku' => 'CUSTOM-TUBING-KIT',
                'quantity' => 10,
                'target_unit_price' => 75.00,
            ],
        ],
    ];

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.quotes.store'), $data);

    $quote = Quote::where('company_name', 'CyberPower Systems')->first();
    expect($quote)->not->toBeNull()
        ->and($quote->items)->toHaveCount(2)
        ->and($quote->company_tier)->toBe('OEM')
        ->and($quote->status)->toBe('pending_review');

    $response->assertRedirect(route('admin.quotes.show', $quote->id));
});

it('allows admin to review and update custom margin pricing on BOM line items', function () {
    $quote = Quote::create([
        'quote_number' => 'RFQ-2026-TEST02',
        'client_name' => 'Sarah Connor',
        'company_name' => 'Skynet Cybernetics',
        'company_tier' => 'Gold',
        'status' => 'pending_review',
        'target_margin_percent' => 15.0,
        'total_cost' => 1000.00,
        'subtotal' => 1200.00,
        'total_quoted_price' => 1200.00,
    ]);

    $item = $quote->items()->create([
        'item_name' => 'Server Motherboard Dual Socket',
        'item_sku' => 'MB-SRV-DUAL',
        'quantity' => 5,
        'unit_cost' => 200.00,
        'margin_percent' => 15.0,
        'quoted_unit_price' => 240.00,
        'total_quoted_price' => 1200.00,
    ]);

    // Update with 25% custom margin -> 200 / (1 - 0.25) = 266.67
    $response = $this->actingAs($this->admin, 'backend')
        ->patch(route('admin.quotes.pricing', $quote->id), [
            'target_margin_percent' => 25.0,
            'items' => [
                $item->id => [
                    'quoted_unit_price' => 266.67,
                    'margin_percent' => 25.0,
                ],
            ],
        ]);

    $response->assertRedirect();
    $quote->refresh();
    $item->refresh();

    expect($item->quoted_unit_price)->toBe('266.67')
        ->and($item->total_quoted_price)->toBe('1333.35')
        ->and($quote->total_quoted_price)->toBe('1333.35');
});

it('finalizes and sends quote with validity date to client', function () {
    $quote = Quote::create([
        'quote_number' => 'RFQ-2026-TEST03',
        'client_name' => 'Bruce Wayne',
        'company_name' => 'Wayne Enterprises',
        'company_tier' => 'OEM',
        'status' => 'pending_review',
        'total_quoted_price' => 50000.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.quotes.send', $quote->id), [
            'valid_days' => 30,
            'admin_notes' => 'Includes 3-year enterprise warranty and express freight',
        ]);

    $response->assertRedirect();
    $quote->refresh();

    expect($quote->status)->toBe('quoted')
        ->and($quote->valid_until)->not->toBeNull()
        ->and($quote->admin_notes)->toContain('Includes 3-year enterprise warranty');
});

it('converts an accepted quote into a confirmed customer Order', function () {
    $product = Product::factory()->create(['name' => 'DDR5 64GB Kit', 'sku' => 'RAM-DDR5-64G']);

    $quote = Quote::create([
        'quote_number' => 'RFQ-2026-TEST04',
        'client_name' => 'Tony Stark',
        'company_name' => 'Stark Industries',
        'client_email' => 'tony@stark.local',
        'client_phone' => '+855 12 777 888',
        'company_tier' => 'OEM',
        'status' => 'quoted',
        'subtotal' => 2000.00,
        'total_quoted_price' => 2000.00,
    ]);

    $quote->items()->create([
        'product_id' => $product->id,
        'item_name' => 'DDR5 64GB Kit',
        'item_sku' => 'RAM-DDR5-64G',
        'quantity' => 10,
        'unit_cost' => 150.00,
        'quoted_unit_price' => 200.00,
        'total_quoted_price' => 2000.00,
    ]);

    $response = $this->actingAs($this->admin, 'backend')
        ->post(route('admin.quotes.convert-to-order', $quote->id));

    $quote->refresh();
    expect($quote->status)->toBe('converted_to_order')
        ->and($quote->converted_order_id)->not->toBeNull();

    $order = Order::find($quote->converted_order_id);
    expect($order)->not->toBeNull()
        ->and($order->customer_name)->toBe('Tony Stark')
        ->and($order->total_amount)->toBe('2000.00')
        ->and($order->status)->toBe('confirmed')
        ->and($order->items)->toHaveCount(1);

    $response->assertRedirect(route('admin.orders.show', $order->id));
});
