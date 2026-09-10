<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
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

it('allows transferring stock between two warehouses and moves inventory immediately on completed', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'Warehouse Alpha', 'code' => 'WH-A']);
    $toWh = Warehouse::create(['name' => 'Warehouse Bravo', 'code' => 'WH-B']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'stock_quantity' => 50]);

    $fromWh->products()->attach($product->id, ['quantity' => 50]);
    $toWh->products()->attach($product->id, ['quantity' => 0]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.transfers.store'), [
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'status' => 'completed',
        'transfer_date' => date('Y-m-d'),
        'notes' => 'Urgent replenishment',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 20],
        ],
    ]);

    $response->assertRedirect(route('admin.stock.transfers.index'));
    $response->assertSessionHas('success');

    expect($fromWh->getProductStock($product))->toBe(30);
    expect($toWh->getProductStock($product))->toBe(20);

    $product->refresh();
    expect($product->stock_quantity)->toBe(50);
});

it('blocks stock transfer if source warehouse has insufficient stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'Warehouse Alpha', 'code' => 'WH-A1']);
    $toWh = Warehouse::create(['name' => 'Warehouse Bravo', 'code' => 'WH-B1']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $fromWh->products()->attach($product->id, ['quantity' => 5]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.transfers.store'), [
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'status' => 'completed',
        'products' => [
            ['product_id' => $product->id, 'quantity' => 10], // Requesting 10 when only 5 available
        ],
    ]);

    $response->assertSessionHas('error');
    expect($fromWh->getProductStock($product))->toBe(5);
    expect($toWh->getProductStock($product))->toBe(0);
});

it('allows transitioning in_transit transfer to completed status and delivers stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $fromWh = Warehouse::create(['name' => 'Warehouse Origin', 'code' => 'WH-OG']);
    $toWh = Warehouse::create(['name' => 'Warehouse Destination', 'code' => 'WH-DEST']);

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $fromWh->products()->attach($product->id, ['quantity' => 10]); // deducted already when moved in transit
    $toWh->products()->attach($product->id, ['quantity' => 0]);

    $transfer = StockTransfer::create([
        'reference_number' => 'TRF-TEST-0001',
        'from_warehouse_id' => $fromWh->id,
        'to_warehouse_id' => $toWh->id,
        'user_id' => $admin->id,
        'status' => 'in_transit',
        'transfer_date' => now(),
    ]);

    $transfer->items()->create([
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $response = $this->actingAs($admin, 'backend')->patch(route('admin.stock.transfers.status', $transfer->id), [
        'status' => 'completed',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $transfer->refresh();
    expect($transfer->status)->toBe('completed');
    expect($toWh->getProductStock($product))->toBe(10);
});
