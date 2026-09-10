<?php

use App\Models\Category;
use App\Models\Make;
use App\Models\Product;
use App\Models\User;
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

it('allows admin to view stock management dashboard with metrics', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 15,
        'low_stock_threshold' => 5,
        'price' => 100,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.stock.index'));

    $response->assertStatus(200);
    $response->assertSee('Stock Management');
    $response->assertSee('Total Warehouse Units');
    $response->assertSee('Inventory Asset Value');
    $response->assertSee('title="Edit Product"', false);
    $response->assertSee('bi-pencil');
    $response->assertSee('title="View Serial Product List (Registry &amp; Warranties)"', false);
    $response->assertSee('bi-upc-scan');
});

it('allows filtering stock by low stock and out of stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $lowProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Low Stock GPU RTX 4070',
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $outProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Out of Stock CPU Intel i9',
        'stock_quantity' => 0,
        'low_stock_threshold' => 5,
    ]);

    $inProduct = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'In Stock RAM DDR5',
        'stock_quantity' => 50,
        'low_stock_threshold' => 5,
    ]);

    // Low stock filter
    $responseLow = $this->actingAs($admin, 'backend')->get(route('admin.stock.index', ['filter' => 'low']));
    $responseLow->assertStatus(200);
    $responseLow->assertSee('Low Stock GPU RTX 4070');
    $responseLow->assertDontSee('In Stock RAM DDR5');

    // Out of stock filter
    $responseOut = $this->actingAs($admin, 'backend')->get(route('admin.stock.index', ['filter' => 'out']));
    $responseOut->assertStatus(200);
    $responseOut->assertSee('Out of Stock CPU Intel i9');
    $responseOut->assertDontSee('In Stock RAM DDR5');
});

it('allows quick stock update for a product', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock_quantity' => 5,
        'low_stock_threshold' => 2,
    ]);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.stock.update', $product->id), [
        'stock_quantity' => 25,
        'low_stock_threshold' => 10,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $product->refresh();
    expect($product->stock_quantity)->toBe(25);
    expect($product->low_stock_threshold)->toBe(10);
});

it('sorts stock overview products by updated_at descending', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();

    $olderProduct = Product::factory()->create([
        'name' => 'Older Hardware Product Alpha',
        'category_id' => $category->id,
        'stock_quantity' => 10,
        'updated_at' => now()->subDays(3),
    ]);

    $newerProduct = Product::factory()->create([
        'name' => 'Newer Hardware Product Omega',
        'category_id' => $category->id,
        'stock_quantity' => 20,
        'updated_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.stock.index'));
    $response->assertStatus(200);

    $content = $response->getContent();
    $posNewer = strpos($content, 'Newer Hardware Product Omega');
    $posOlder = strpos($content, 'Older Hardware Product Alpha');

    expect($posNewer)->not->toBeFalse();
    expect($posOlder)->not->toBeFalse();
    expect($posNewer)->toBeLessThan($posOlder);

    $response->assertSee('Stock Qty');
    $response->assertSee('Alert:');
    $response->assertSee('Updated');
});

it('allows filtering stock overview by make in general, low stock alerts, and out of stock', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $makeAsus = Make::factory()->create(['name' => 'ASUS ROG']);
    $makeMsi = Make::factory()->create(['name' => 'MSI Gaming']);

    // ASUS products: 1 normal in stock, 1 low stock, 1 out of stock
    $asusInStock = Product::factory()->create([
        'name' => 'ASUS Strix In Stock',
        'category_id' => $category->id,
        'make_id' => $makeAsus->id,
        'stock_quantity' => 20,
        'low_stock_threshold' => 5,
    ]);

    $asusLowStock = Product::factory()->create([
        'name' => 'ASUS TUF Low Stock',
        'category_id' => $category->id,
        'make_id' => $makeAsus->id,
        'stock_quantity' => 2,
        'low_stock_threshold' => 5,
    ]);

    $asusOutOfStock = Product::factory()->create([
        'name' => 'ASUS Prime Out Of Stock',
        'category_id' => $category->id,
        'make_id' => $makeAsus->id,
        'stock_quantity' => 0,
        'low_stock_threshold' => 5,
    ]);

    // MSI products: 1 low stock, 1 out of stock
    $msiLowStock = Product::factory()->create([
        'name' => 'MSI Ventus Low Stock',
        'category_id' => $category->id,
        'make_id' => $makeMsi->id,
        'stock_quantity' => 1,
        'low_stock_threshold' => 5,
    ]);

    $msiOutOfStock = Product::factory()->create([
        'name' => 'MSI Suprim Out Of Stock',
        'category_id' => $category->id,
        'make_id' => $makeMsi->id,
        'stock_quantity' => 0,
        'low_stock_threshold' => 5,
    ]);

    // 1. Filter by Make ASUS ROG in general stock overview
    $respOverview = $this->actingAs($admin, 'backend')->get(route('admin.stock.index', ['make_id' => $makeAsus->id]));
    $respOverview->assertStatus(200);
    $respOverview->assertSee('ASUS Strix In Stock');
    $respOverview->assertSee('ASUS TUF Low Stock');
    $respOverview->assertSee('ASUS Prime Out Of Stock');
    $respOverview->assertDontSee('MSI Ventus Low Stock');
    $respOverview->assertDontSee('MSI Suprim Out Of Stock');

    // 2. Filter by Make ASUS ROG with Low Stock alert
    $respLow = $this->actingAs($admin, 'backend')->get(route('admin.stock.index', ['filter' => 'low', 'make_id' => $makeAsus->id]));
    $respLow->assertStatus(200);
    $respLow->assertSee('ASUS TUF Low Stock');
    $respLow->assertDontSee('ASUS Strix In Stock');
    $respLow->assertDontSee('MSI Ventus Low Stock');

    // 3. Filter by Make ASUS ROG with Out of Stock
    $respOut = $this->actingAs($admin, 'backend')->get(route('admin.stock.index', ['filter' => 'out', 'make_id' => $makeAsus->id]));
    $respOut->assertStatus(200);
    $respOut->assertSee('ASUS Prime Out Of Stock');
    $respOut->assertDontSee('ASUS Strix In Stock');
    $respOut->assertDontSee('ASUS TUF Low Stock');
    $respOut->assertDontSee('MSI Suprim Out Of Stock');
});
