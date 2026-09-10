<?php

use App\Models\AdminMenu;
use App\Models\Category;
use App\Models\Make;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\BackendMenuSeeder;
use Database\Seeders\MakeSeeder;
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

it('allows admin to view makes listing', function () {
    Make::factory()->create(['name' => 'Dell Enterprise', 'slug' => 'dell-enterprise']);

    $response = $this->actingAs($this->admin, 'backend')->get(route('admin.makes.index'));

    $response->assertOk();
    $response->assertSee('Hardware Makes');
    $response->assertSee('Dell Enterprise');
});

it('renders the create and edit forms with interactive logo cropper', function () {
    $createResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.makes.create'));
    $createResponse->assertOk();
    $createResponse->assertSee('Make Logo (Image)');
    $createResponse->assertSee('makeCropperModal');
    $createResponse->assertSee('cropperTargetImage');

    $make = Make::factory()->create(['name' => 'Intel OEM', 'logo' => 'makes/intel.png']);
    $editResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.makes.edit', $make));
    $editResponse->assertOk();
    $editResponse->assertSee('makeCropperModal');
    $editResponse->assertSee('Crop &amp; Edit', false);
});

it('allows admin to create a new hardware make', function () {
    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.makes.store'), [
        'name' => 'Supermicro',
        'slug' => 'supermicro',
        'website' => 'https://www.supermicro.com',
        'description' => 'Enterprise computing solutions and motherboards',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.makes.index'));
    $this->assertDatabaseHas('makes', [
        'name' => 'Supermicro',
        'slug' => 'supermicro',
        'website' => 'https://www.supermicro.com',
        'is_active' => true,
    ]);
});

it('allows admin to update an existing make', function () {
    $make = Make::factory()->create(['name' => 'Hewlett Packard', 'slug' => 'hp']);

    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.makes.update', $make), [
        'name' => 'HP Enterprise',
        'slug' => 'hp-enterprise',
        'website' => 'https://www.hpe.com',
        'description' => 'HPE Servers and Networking',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.makes.index'));
    $this->assertDatabaseHas('makes', [
        'id' => $make->id,
        'name' => 'HP Enterprise',
        'slug' => 'hp-enterprise',
    ]);
});

it('prevents deleting a make with associated products', function () {
    $make = Make::factory()->create(['name' => 'Lenovo', 'slug' => 'lenovo']);

    Product::create([
        'category_id' => $this->category->id,
        'make_id' => $make->id,
        'name' => 'ThinkSystem SR650 Server',
        'slug' => 'thinksystem-sr650-server',
        'sku' => 'SR650-LNV',
        'price' => 3500.00,
        'stock_quantity' => 5,
        'low_stock_threshold' => 1,
    ]);

    $response = $this->actingAs($this->admin, 'backend')->delete(route('admin.makes.destroy', $make));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('makes', ['id' => $make->id]);
});

it('allows deleting a make when no products are associated', function () {
    $make = Make::factory()->create(['name' => 'Unused Make', 'slug' => 'unused-make']);

    $response = $this->actingAs($this->admin, 'backend')->delete(route('admin.makes.destroy', $make));

    $response->assertRedirect(route('admin.makes.index'));
    $this->assertDatabaseMissing('makes', ['id' => $make->id]);
});

it('allows creating a product with an associated make and filters by make', function () {
    $make = Make::factory()->create(['name' => 'Cisco Systems', 'slug' => 'cisco']);

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.products.store'), [
        'category_id' => $this->category->id,
        'make_id' => $make->id,
        'name' => 'Cisco Catalyst 9300 Switch',
        'sku' => 'C9300-48P',
        'price' => 4500.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
        'action' => 'save',
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'C9300-48P')->first();
    expect($product)->not->toBeNull()
        ->and($product->make_id)->toBe($make->id)
        ->and($product->make->name)->toBe('Cisco Systems');

    // Filter index by make_id
    $indexResponse = $this->actingAs($this->admin, 'backend')->get(route('admin.products.index', ['make_id' => $make->id]));
    $indexResponse->assertOk();
    $indexResponse->assertSee('Cisco Catalyst 9300 Switch');
    $indexResponse->assertSee('Make: Cisco Systems');
});

it('allows admin to create a make with an uploaded logo image', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('lenovo-logo.png', 200, 200);

    $response = $this->actingAs($this->admin, 'backend')->post(route('admin.makes.store'), [
        'name' => 'Lenovo OEM',
        'slug' => 'lenovo-oem',
        'logo' => $file,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.makes.index'));
    $make = Make::where('slug', 'lenovo-oem')->first();
    expect($make)->not->toBeNull()
        ->and($make->logo)->not->toBeNull();

    Storage::disk('public')->assertExists($make->logo);
    expect($make->logo_url)->not->toBeNull();
});

it('allows admin to update a make logo and remove existing logo', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('dell-logo.png');
    $path = $file->store('makes', 'public');
    $make = Make::factory()->create(['name' => 'Dell Inc', 'logo' => $path]);

    // Replace logo
    $newFile = UploadedFile::fake()->image('dell-new.png');
    $response = $this->actingAs($this->admin, 'backend')->put(route('admin.makes.update', $make), [
        'name' => 'Dell Technologies',
        'logo' => $newFile,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.makes.index'));
    Storage::disk('public')->assertMissing($path);

    $make->refresh();
    Storage::disk('public')->assertExists($make->logo);

    // Now remove logo
    $removeResponse = $this->actingAs($this->admin, 'backend')->put(route('admin.makes.update', $make), [
        'name' => 'Dell Technologies',
        'remove_logo' => '1',
        'is_active' => '1',
    ]);

    $removeResponse->assertRedirect(route('admin.makes.index'));
    $make->refresh();
    expect($make->logo)->toBeNull()
        ->and($make->logo_url)->toBeNull();
});

it('displays the makes menu as a standalone menu item in the backend sidebar like brand and category', function () {
    $menu = AdminMenu::where('slug', 'makes')->first();
    expect($menu)->not->toBeNull()
        ->and($menu->parent_slug)->toBeNull()
        ->and($menu->section)->toBe('Inventory');

    $response = $this->actingAs($this->admin, 'backend')->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Makes');
    $response->assertSee(route('admin.makes.index'));
});

it('seeds sample hardware makes successfully and idempotently', function () {
    $this->seed(MakeSeeder::class);

    expect(Make::where('slug', 'dell')->exists())->toBeTrue()
        ->and(Make::where('slug', 'hp')->exists())->toBeTrue()
        ->and(Make::where('slug', 'lenovo')->exists())->toBeTrue()
        ->and(Make::where('slug', 'supermicro')->exists())->toBeTrue()
        ->and(Make::where('slug', 'cisco')->exists())->toBeTrue()
        ->and(Make::where('slug', 'apple')->exists())->toBeTrue()
        ->and(Make::where('slug', 'asus')->exists())->toBeTrue()
        ->and(Make::where('slug', 'intel')->exists())->toBeTrue()
        ->and(Make::where('slug', 'acer')->exists())->toBeTrue()
        ->and(Make::where('slug', 'alienware')->exists())->toBeTrue();

    // Re-running seeder should be idempotent
    $this->seed(MakeSeeder::class);
    expect(Make::where('slug', 'dell')->count())->toBe(1);
});
