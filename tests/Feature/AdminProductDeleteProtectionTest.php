<?php

use App\Models\Category;
use App\Models\Product\Product;
use App\Models\SerialNumber;
use App\Models\User;
use App\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $permissions = [
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

    $this->category = Category::firstOrCreate(
        ['name' => 'CPUs'],
        ['slug' => 'cpus-'.rand(100, 999)]
    );

    $this->warehouse = Warehouse::firstOrCreate(
        ['code' => 'WH-TEST1'],
        ['name' => 'Test Hub', 'is_default' => true, 'is_active' => true]
    );
});

it('blocks deleting an active product even if stock is 0', function () {
    $product = Product::create([
        'name' => 'Active Test CPU',
        'slug' => 'active-test-cpu-'.rand(100, 999),
        'sku' => 'CPU-ACT-001',
        'category_id' => $this->category->id,
        'price' => 299.99,
        'cost_price' => 200.00,
        'stock_quantity' => 0,
        'is_active' => true, // Active (Not draft)
    ]);

    expect($product->canBeDeleted())->toBeFalse();
    expect($product->getDeletionBlockReason())->toContain('status is Active');

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.products.destroy', $product->id));

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('error');
    expect(Product::where('id', $product->id)->exists())->toBeTrue();
});

it('blocks deleting a draft product if stock is greater than 0', function () {
    $product = Product::create([
        'name' => 'Draft In-Stock CPU',
        'slug' => 'draft-stock-cpu-'.rand(100, 999),
        'sku' => 'CPU-DRF-002',
        'category_id' => $this->category->id,
        'price' => 199.99,
        'cost_price' => 150.00,
        'stock_quantity' => 5, // Stock > 0
        'is_active' => false, // Draft
    ]);

    expect($product->canBeDeleted())->toBeFalse();
    expect($product->getDeletionBlockReason())->toContain('stock is 5');

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.products.destroy', $product->id));

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('error');
    expect(Product::where('id', $product->id)->exists())->toBeTrue();
});

it('blocks deleting a draft product if it has in-stock serial numbers', function () {
    $product = Product::create([
        'name' => 'Serialized Draft CPU',
        'slug' => 'ser-draft-cpu-'.rand(100, 999),
        'sku' => 'CPU-SER-003',
        'category_id' => $this->category->id,
        'price' => 499.99,
        'cost_price' => 380.00,
        'stock_quantity' => 0,
        'requires_serial_tracking' => true,
        'is_active' => false, // Draft
    ]);

    SerialNumber::create([
        'product_id' => $product->id,
        'warehouse_id' => $this->warehouse->id,
        'serial_number' => 'SN-TEST-DEL-001',
        'status' => SerialNumber::STATUS_IN_STOCK,
        'cost_price' => 380.00,
    ]);

    expect($product->canBeDeleted())->toBeFalse();

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.products.destroy', $product->id));

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('error');
    expect(Product::where('id', $product->id)->exists())->toBeTrue();
});

it('allows deleting a product when stock is 0 and status is draft', function () {
    $product = Product::create([
        'name' => 'Safe Deletable Draft GPU',
        'slug' => 'safe-del-draft-'.rand(100, 999),
        'sku' => 'GPU-DEL-004',
        'category_id' => $this->category->id,
        'price' => 599.99,
        'cost_price' => 450.00,
        'stock_quantity' => 0, // Stock is 0
        'is_active' => false, // Draft
    ]);

    expect($product->canBeDeleted())->toBeTrue();
    expect($product->getDeletionBlockReason())->toBeNull();

    $response = $this->actingAs($this->admin, 'backend')
        ->delete(route('admin.products.destroy', $product->id));

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionHas('success');
    expect(Product::where('id', $product->id)->exists())->toBeFalse();
});
