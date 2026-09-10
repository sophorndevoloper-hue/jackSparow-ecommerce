<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');

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

it('allows admin to view product images management gallery', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.products.images.index'));

    $response->assertStatus(200);
    $response->assertSee('Product Images &amp; Gallery', false);
    $response->assertSee('Target Product:');
});

it('allows admin to upload multiple images for a product', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $brand = Brand::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
    ]);

    $file1 = UploadedFile::fake()->image('gpu1.jpg', 600, 600);
    $file2 = UploadedFile::fake()->image('gpu2.png', 600, 600);

    $response = $this->actingAs($admin, 'backend')->post(route('admin.products.images.store'), [
        'product_id' => $product->id,
        'images' => [$file1, $file2],
        'is_primary' => true,
    ]);

    $response->assertRedirect(route('admin.products.images.index', ['product_id' => $product->id]));
    $response->assertSessionHas('success');

    expect($product->images()->count())->toBe(2);

    $primaryImage = $product->primaryImage;
    expect($primaryImage)->not()->toBeNull();
    Storage::disk('public')->assertExists($primaryImage->image_path);
});

it('allows setting an image as primary cover photo', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $img1 = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => 'products/test1.jpg',
        'is_primary' => true,
        'sort_order' => 0,
    ]);

    $img2 = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => 'products/test2.jpg',
        'is_primary' => false,
        'sort_order' => 1,
    ]);

    $response = $this->actingAs($admin, 'backend')->patch(route('admin.products.images.primary', $img2->id));

    $response->assertRedirect();
    $img1->refresh();
    $img2->refresh();

    expect($img1->is_primary)->toBeFalse();
    expect($img2->is_primary)->toBeTrue();
});

it('allows deleting a product image and cleans up storage', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $fakeFile = UploadedFile::fake()->image('test_del.jpg');
    $path = $fakeFile->store('products', 'public');

    $image = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => $path,
        'is_primary' => true,
    ]);

    Storage::disk('public')->assertExists($path);

    $response = $this->actingAs($admin, 'backend')->delete(route('admin.products.images.destroy', $image->id));

    $response->assertRedirect();
    expect(ProductImage::find($image->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

it('renders product list with interactive image gallery preview modal and thumbnails', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'AMD Radeon RX 7900 XTX',
        'sku' => 'GPU-RX7900XTX',
    ]);

    $img1 = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => 'products/gpu_front.jpg',
        'is_primary' => true,
    ]);

    $img2 = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => 'products/gpu_back.jpg',
        'is_primary' => false,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.products.index'));

    $response->assertStatus(200);
    $response->assertSee('product-thumb-preview');
    $response->assertSee('productGalleryModal');
    $response->assertSee('openProductGallery('.$product->id.')', false);
    $response->assertSee('GPU-RX7900XTX');
});

it('allows admin to save gallery with batch new uploads, removals, and primary selection', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $fileToKeep = UploadedFile::fake()->image('keep.jpg')->store('products', 'public');
    $fileToDelete = UploadedFile::fake()->image('delete_me.jpg')->store('products', 'public');

    $imgKeep = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => $fileToKeep,
        'is_primary' => true,
    ]);

    $imgDelete = ProductImage::create([
        'product_id' => $product->id,
        'image_path' => $fileToDelete,
        'is_primary' => false,
    ]);

    $newFile = UploadedFile::fake()->image('new_hero.png');

    $response = $this->actingAs($admin, 'backend')->post(route('admin.products.images.save'), [
        'product_id' => $product->id,
        'delete_image_ids' => [$imgDelete->id],
        'images' => [$newFile],
        'primary_image_id' => 'new_0', // Set the newly uploaded image as primary cover
    ]);

    $response->assertRedirect(route('admin.products.images.index', ['product_id' => $product->id]));
    $response->assertSessionHas('success');

    // Confirm deleted image is gone from DB & storage
    expect(ProductImage::find($imgDelete->id))->toBeNull();
    Storage::disk('public')->assertMissing($fileToDelete);

    // Confirm kept image still exists
    expect(ProductImage::find($imgKeep->id))->not->toBeNull();
    $imgKeep->refresh();
    expect($imgKeep->is_primary)->toBeFalse();

    // Confirm new image exists and is set as primary
    $newImgRecord = ProductImage::where('product_id', $product->id)->where('id', '!=', $imgKeep->id)->first();
    expect($newImgRecord)->not->toBeNull();
    expect($newImgRecord->is_primary)->toBeTrue();
    Storage::disk('public')->assertExists($newImgRecord->image_path);
});
