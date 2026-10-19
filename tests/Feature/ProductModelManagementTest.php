<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\User;
use Database\Seeders\BackendMenuSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(BackendMenuSeeder::class);

    $this->adminRole = Role::findByName('admin', 'backend');
    $this->admin = User::factory()->create(['is_approved' => true]);
    $this->admin->assignRole($this->adminRole);

    $this->category = Category::firstOrCreate(['slug' => 'processors-cpu'], ['name' => 'Processors (CPU)']);
    $this->brand = Brand::factory()->create(['name' => 'ASUS', 'slug' => 'asus']);
});

it('allows admin to view hardware models listing', function () {
    ProductModel::factory()->create([
        'brand_id' => $this->brand->id,
        'name' => 'ROG Strix',
        'slug' => 'rog-strix',
    ]);

    $response = $this->actingAs($this->admin, 'backend')->get(route('admin.models.index'));

    $response->assertOk();
    $response->assertSee('Hardware Models');
    $response->assertSee('ROG Strix');
    $response->assertSee('ASUS');
});

it('returns yajra datatable json response on ajax request for models', function () {
    ProductModel::factory()->create([
        'brand_id' => $this->brand->id,
        'name' => 'ROG Strix',
        'slug' => 'rog-strix',
    ]);

    $response = $this->actingAs($this->admin, 'backend')->getJson(route('admin.models.index'), [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    expect($response->json('data.0.name'))->toContain('ROG Strix');
});

it('renders the create and edit forms for product models', function () {
    $createResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.models.create'));
    $createResponse->assertOk();
    $createResponse->assertSee('Add Hardware Model');
    $createResponse->assertSee('ASUS');

    $model = ProductModel::factory()->create([
        'brand_id' => $this->brand->id,
        'name' => 'TUF Gaming',
    ]);

    $editResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.models.edit', $model));
    $editResponse->assertOk();
    $editResponse->assertSee('Edit Model: TUF Gaming');
    $editResponse->assertSee('TUF Gaming');
});

it('allows admin to create a hardware model with image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('model-badge.png', 200, 200);

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.models.store'), [
        'brand_id' => $this->brand->id,
        'name' => 'ROG Strix GeForce RTX 4090',
        'slug' => 'rog-strix-rtx-4090',
        'description' => 'Flagship enthusiast graphics card series',
        'image' => $file,
        'sort_order' => 1,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.models.index'));
    $model = ProductModel::where('slug', 'rog-strix-rtx-4090')->first();
    expect($model)->not->toBeNull()
        ->and($model->brand_id)->toBe($this->brand->id)
        ->and($model->image)->not->toBeNull();

    Storage::disk('public')->assertExists($model->image);
    expect($model->image_url)->not->toBeNull();
});

it('allows admin to update a hardware model', function () {
    $model = ProductModel::factory()->create([
        'brand_id' => $this->brand->id,
        'name' => 'Old Series',
        'slug' => 'old-series',
    ]);

    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.models.update', $model), [
        'brand_id' => $this->brand->id,
        'name' => 'Updated Series',
        'slug' => 'updated-series',
        'description' => 'Updated details',
        'is_active' => '1',
        'sort_order' => 5,
    ]);

    $response->assertRedirect(route('admin.models.index'));
    $model->refresh();
    expect($model->name)->toBe('Updated Series')
        ->and($model->slug)->toBe('updated-series')
        ->and($model->sort_order)->toBe(5);
});

it('prevents deleting a model that has assigned products', function () {
    $model = ProductModel::factory()->create(['brand_id' => $this->brand->id]);

    $product = Product::factory()->create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'model_id' => $model->id,
        'name' => 'ASUS ROG Strix RTX 4090 OC',
    ]);

    $response = $this->actingAs($this->admin, 'backend')->delete(route('admin.models.destroy', $model));

    $response->assertSessionHas('error', 'Cannot delete model with associated products.');
    expect(ProductModel::where('id', $model->id)->exists())->toBeTrue();
});

it('allows deleting an unassigned hardware model', function () {
    $model = ProductModel::factory()->create(['brand_id' => $this->brand->id]);

    $response = $this->actingAs($this->admin, 'backend')->delete(route('admin.models.destroy', $model));

    $response->assertRedirect(route('admin.models.index'));
    expect(ProductModel::where('id', $model->id)->exists())->toBeFalse();
});

it('returns models filtered by brand via byBrand json endpoint', function () {
    $msi = Brand::factory()->create(['name' => 'MSI', 'slug' => 'msi']);

    $asusModel = ProductModel::factory()->create(['brand_id' => $this->brand->id, 'name' => 'ROG Maximus']);
    $msiModel = ProductModel::factory()->create(['brand_id' => $msi->id, 'name' => 'Suprim X']);

    $response = $this->actingAs($this->admin, 'backend')->getJson(route('admin.models.by-brand', ['brand' => $this->brand->id]));

    $response->assertOk();
    $data = $response->json();
    expect(collect($data)->pluck('id'))->toContain($asusModel->id)
        ->and(collect($data)->pluck('id'))->not->toContain($msiModel->id);
});

it('creates and associates a product with a model correctly', function () {
    $model = ProductModel::factory()->create([
        'brand_id' => $this->brand->id,
        'name' => 'ROG Strix 4080',
    ]);

    $productData = [
        'name' => 'ASUS ROG Strix GeForce RTX 4080 16GB OC',
        'slug' => 'asus-rog-strix-rtx-4080-oc',
        'sku' => 'GPU-ASUS-4080-STRIX',
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'model_id' => $model->id,
        'price' => 1199.99,
        'cost_price' => 950.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
        'action' => 'save',
        'is_active' => '1',
    ];

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.products.store'), $productData);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'GPU-ASUS-4080-STRIX')->first();
    expect($product)->not->toBeNull()
        ->and($product->model_id)->toBe($model->id)
        ->and($product->model)->not->toBeNull()
        ->and($product->model->name)->toBe('ROG Strix 4080')
        ->and($model->products()->count())->toBe(1);
});
