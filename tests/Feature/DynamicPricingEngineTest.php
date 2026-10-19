<?php

use App\Models\Product;
use App\Models\User;
use App\Services\Pricing\DynamicPricingEngine;
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

it('calculates unit prices correctly for Standard, Gold, and OEM tiers', function () {
    $engine = app(DynamicPricingEngine::class);

    $product = Product::factory()->create([
        'price' => 100.00,
        'cost_price' => 60.00,
    ]);

    // Standard Tier (0% discount, 1 unit)
    $std = $engine->calculate($product, 'Standard', 1);
    expect($std['unit_price'])->toBe(100.00)
        ->and($std['tier_discount_percent'])->toBe(0.0)
        ->and($std['total_price'])->toBe(100.00);

    // Gold Tier (10% discount, 1 unit)
    $gold = $engine->calculate($product, 'Gold', 1);
    expect($gold['unit_price'])->toBe(90.00)
        ->and($gold['tier_discount_percent'])->toBe(10.0)
        ->and($gold['total_price'])->toBe(90.00);

    // OEM Tier (20% discount, 1 unit)
    $oem = $engine->calculate($product, 'OEM', 1);
    expect($oem['unit_price'])->toBe(80.00)
        ->and($oem['tier_discount_percent'])->toBe(20.0)
        ->and($oem['total_price'])->toBe(80.00);
});

it('applies volume breaks for 50+ units (-5%) and 200+ units (-12%)', function () {
    $engine = app(DynamicPricingEngine::class);

    $product = Product::factory()->create([
        'price' => 100.00,
        'cost_price' => 50.00,
    ]);

    // Standard Tier @ 50 units (0% tier, 5% volume break)
    $std50 = $engine->calculate($product, 'Standard', 50);
    // 100 * 0.95 = 95.00
    expect($std50['unit_price'])->toBe(95.00)
        ->and($std50['volume_discount_percent'])->toBe(5.00)
        ->and($std50['total_price'])->toBe(4750.00);

    // Gold Tier @ 50 units (10% tier -> 90.00, then 5% volume break -> 85.50)
    $gold50 = $engine->calculate($product, 'Gold', 50);
    // 90 * 0.95 = 85.50
    expect($gold50['unit_price'])->toBe(85.50)
        ->and($gold50['volume_discount_percent'])->toBe(5.00);

    // OEM Tier @ 200 units (20% tier -> 80.00, then 12% volume break -> 70.40)
    $oem200 = $engine->calculate($product, 'OEM', 200);
    // 80 * 0.88 = 70.40
    expect($oem200['unit_price'])->toBe(70.40)
        ->and($oem200['volume_discount_percent'])->toBe(12.00)
        ->and($oem200['total_price'])->toBe(14080.00);
});

it('calculates custom margin pricing when specified', function () {
    $engine = app(DynamicPricingEngine::class);

    $product = Product::factory()->create([
        'price' => 100.00,
        'cost_price' => 70.00,
    ]);

    // Cost 70 with 30% margin -> 70 / (1 - 0.30) = 100.00
    $calc = $engine->calculate($product, 'Gold', 10, 30.0);
    expect($calc['unit_price'])->toBe(100.00)
        ->and($calc['margin_percent'])->toBe(30.0);
});

it('renders the dynamic pricing matrix page and responds to JSON calculation endpoint', function () {
    $product = Product::factory()->create(['price' => 250.00, 'cost_price' => 180.00]);

    // View page
    $response = $this->actingAs($this->admin, 'backend')
        ->get(route('admin.pricing.index', ['product_id' => $product->id, 'tier' => 'OEM', 'quantity' => 200]));

    $response->assertStatus(200);
    $response->assertSee('Dynamic Pricing Engine');
    $response->assertSee('OEM Tier');
    $response->assertSee('Pallet / Bulk Break');

    // JSON API calculation
    $jsonResponse = $this->actingAs($this->admin, 'backend')
        ->postJson(route('admin.pricing.calculate'), [
            'product_id' => $product->id,
            'tier' => 'Gold',
            'quantity' => 50,
        ]);

    $jsonResponse->assertStatus(200);
    $jsonResponse->assertJsonPath('success', true);
    $jsonResponse->assertJsonStructure([
        'success',
        'data' => [
            'unit_price',
            'total_price',
            'volume_discount_percent',
            'margin_percent',
        ],
    ]);
});
