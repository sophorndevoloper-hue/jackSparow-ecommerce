<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
});

it('allows admin to view brands listing', function () {
    Brand::factory()->create(['name' => 'NVIDIA Corporation', 'slug' => 'nvidia']);

    $response = $this->actingAs($this->admin, 'backend')->get(route('admin.brands.index'));

    $response->assertOk();
    $response->assertSee('Hardware Brands');
    $response->assertSee('NVIDIA Corporation');
});

it('returns yajra datatable json response on ajax request', function () {
    Brand::factory()->create(['name' => 'NVIDIA Corporation', 'slug' => 'nvidia']);

    $response = $this->actingAs($this->admin, 'backend')->getJson(route('admin.brands.index'), [
        'X-Requested-With' => 'XMLHttpRequest',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['data', 'recordsTotal', 'recordsFiltered']);
    expect($response->json('data.0.name'))->toContain('NVIDIA Corporation');
});

it('renders the create and edit forms with interactive logo cropper', function () {
    $createResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.brands.create'));
    $createResponse->assertOk();
    $createResponse->assertSee('Brand Logo (Image)');
    $createResponse->assertSee('brandCropperModal');
    $createResponse->assertSee('brandCropperTargetImage');

    $brand = Brand::factory()->create(['name' => 'ASUS', 'logo' => 'brands/asus.png']);
    $editResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.brands.edit', $brand));
    $editResponse->assertOk();
    $editResponse->assertSee('brandCropperModal');
    $editResponse->assertSee('Crop &amp; Edit', false);
});

it('allows admin to create a brand with an uploaded logo image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('corsair-logo.png', 200, 200);

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.brands.store'), [
        'name' => 'Corsair',
        'slug' => 'corsair',
        'website' => 'https://www.corsair.com',
        'description' => 'High-performance gaming gear and PC components',
        'logo' => $file,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.brands.index'));
    $brand = Brand::where('slug', 'corsair')->first();
    expect($brand)->not->toBeNull()
        ->and($brand->logo)->not->toBeNull();

    Storage::disk('public')->assertExists($brand->logo);
    expect($brand->logo_url)->not->toBeNull();
});

it('allows admin to update a brand logo and remove existing logo', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('msi-old.png');
    $path = $file->store('brands', 'public');
    $brand = Brand::factory()->create(['name' => 'MSI Gaming', 'logo' => $path]);

    // Replace logo
    $newFile = UploadedFile::fake()->image('msi-new.png');
    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.brands.update', $brand), [
        'name' => 'Micro-Star International',
        'logo' => $newFile,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.brands.index'));
    Storage::disk('public')->assertMissing($path);

    $brand->refresh();
    Storage::disk('public')->assertExists($brand->logo);

    // Remove logo
    $removeResponse = $this->actingAs($this->admin, 'backend')->put(route('admin.brands.update', $brand), [
        'name' => 'Micro-Star International',
        'remove_logo' => '1',
        'is_active' => '1',
    ]);

    $removeResponse->assertRedirect(route('admin.brands.index'));
    $brand->refresh();
    expect($brand->logo)->toBeNull()
        ->and($brand->logo_url)->toBeNull();
});

it('prevents deleting a brand with associated products', function () {
    $brand = Brand::factory()->create(['name' => 'AMD', 'slug' => 'amd']);

    Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $brand->id,
        'name' => 'AMD Ryzen 7 7800X3D',
        'slug' => 'amd-ryzen-7-7800x3d',
        'sku' => 'CPU-AMD-7800X3D',
        'price' => 449.00,
        'stock_quantity' => 15,
        'low_stock_threshold' => 3,
    ]);

    $response = $this->actingAs($this->admin, 'backend')->delete(route('admin.brands.destroy', $brand));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('brands', ['id' => $brand->id]);
});

it('allows deleting a brand and deletes its logo from storage', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('gigabyte.png');
    $path = $file->store('brands', 'public');
    $brand = Brand::factory()->create(['name' => 'Gigabyte', 'logo' => $path]);

    $response = $this->actingAs($this->admin, 'backend')->delete(route('admin.brands.destroy', $brand));

    $response->assertRedirect(route('admin.brands.index'));
    $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    Storage::disk('public')->assertMissing($path);
});
