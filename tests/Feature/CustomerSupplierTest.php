<?php

use App\Models\FrontendUser;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'frontend']);
});

it('stores registered customers in the frontend_users table as simple customers', function () {
    $response = $this->post(route('register'), [
        'name' => 'Jane Techie',
        'email' => 'jane@frontend.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '+1 (555) 345-6789',
        'city' => 'Seattle',
        'address' => '742 Evergreen Terrace',
    ]);

    $response->assertRedirect(route('home'));

    $frontendUser = FrontendUser::where('email', 'jane@frontend.test')->first();
    expect($frontendUser)->not->toBeNull();
    expect($frontendUser->customer_type)->toBe('simple');
    expect($frontendUser->orders_count)->toBe(0);
    expect($frontendUser->city)->toBe('Seattle');

    // Confirm that the user is NOT stored in the users table
    expect(User::where('email', 'jane@frontend.test')->exists())->toBeFalse();
});

it('allows website customers to sign in using dedicated storefront login', function () {
    $frontendUser = FrontendUser::factory()->create([
        'email' => 'gamer@frontend.test',
        'password' => 'secret123',
    ]);

    $response = $this->post(route('login'), [
        'email' => 'gamer@frontend.test',
        'password' => 'secret123',
    ]);

    $this->assertAuthenticated('frontend');
    $response->assertRedirect(route('home'));
});

it('allows administrators to sign in using dedicated admin login', function () {
    $admin = User::factory()->create([
        'email' => 'sysadmin@admin.test',
        'password' => 'admin12345',
    ]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->post(route('admin.login'), [
        'email' => 'sysadmin@admin.test',
        'password' => 'admin12345',
    ]);

    $this->assertAuthenticated('backend');
    $response->assertRedirect(route('admin.dashboard'));
});

it('automatically promotes a simple customer to special customer when order threshold is reached', function () {
    $customer = FrontendUser::factory()->create([
        'customer_type' => 'simple',
        'orders_count' => 2,
        'total_spent' => 450.00,
    ]);

    expect($customer->isSpecial())->toBeFalse();
    expect($customer->ordersUntilSpecial())->toBe(1);

    // Simulate placing a 3rd order
    $customer->increment('orders_count');
    $customer->increment('total_spent', 350.00);
    $promoted = $customer->checkAndPromote();

    expect($promoted)->toBeTrue();
    expect($customer->fresh()->customer_type)->toBe('special');
    expect($customer->fresh()->isSpecial())->toBeTrue();
});

it('allows admin to view customers and filter by simple and special tiers', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $simpleCustomer = FrontendUser::factory()->create([
        'name' => 'Regular Buyer',
        'customer_type' => 'simple',
    ]);

    $specialCustomer = FrontendUser::factory()->create([
        'name' => 'VIP Pro Overclocker',
        'customer_type' => 'special',
    ]);

    // View all
    $responseAll = $this->actingAs($admin, 'backend')->get(route('admin.customers.index'));
    $responseAll->assertStatus(200);
    $responseAll->assertSee('Regular Buyer');
    $responseAll->assertSee('VIP Pro Overclocker');

    // Filter simple
    $responseSimple = $this->actingAs($admin, 'backend')->get(route('admin.customers.index', ['type' => 'simple']));
    $responseSimple->assertStatus(200);
    $responseSimple->assertSee('Regular Buyer');

    // Filter special
    $responseSpecial = $this->actingAs($admin, 'backend')->get(route('admin.customers.index', ['type' => 'special']));
    $responseSpecial->assertStatus(200);
    $responseSpecial->assertSee('VIP Pro Overclocker');
});

it('allows admin to toggle customer between simple and special tiers', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $customer = FrontendUser::factory()->create(['customer_type' => 'simple']);

    $toggleResponse = $this->actingAs($admin, 'backend')
        ->patch(route('admin.customers.toggle-special', $customer->id));

    $toggleResponse->assertRedirect();
    expect($customer->fresh()->customer_type)->toBe('special');

    // Toggle back to simple
    $toggleBackResponse = $this->actingAs($admin, 'backend')
        ->patch(route('admin.customers.toggle-special', $customer->id));

    $toggleBackResponse->assertRedirect();
    expect($customer->fresh()->customer_type)->toBe('simple');
});

it('allows admin to manage hardware suppliers in people block', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // 1. Create Supplier
    $createResponse = $this->actingAs($admin, 'backend')->post(route('admin.suppliers.store'), [
        'company_name' => 'EVGA Power Supplies Direct',
        'contact_name' => 'Robert Johnson',
        'email' => 'sales@evga-direct.test',
        'phone' => '+1 (800) 555-0199',
        'address' => '408 Saturn St, Brea, CA 92821',
        'supply_categories' => 'Power Supplies, Thermal Paste',
        'status' => 'active',
        'notes' => 'Direct manufacturer distribution channel.',
    ]);

    $createResponse->assertRedirect(route('admin.suppliers.index'));

    $supplier = Supplier::where('company_name', 'EVGA Power Supplies Direct')->first();
    expect($supplier)->not->toBeNull();
    expect($supplier->contact_name)->toBe('Robert Johnson');

    // 2. View Suppliers list
    $indexResponse = $this->actingAs($admin, 'backend')->get(route('admin.suppliers.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertSee('EVGA Power Supplies Direct');

    // 3. Edit Supplier
    $updateResponse = $this->actingAs($admin, 'backend')->put(route('admin.suppliers.update', $supplier->id), [
        'company_name' => 'EVGA Corporation',
        'status' => 'inactive',
    ]);
    $updateResponse->assertRedirect(route('admin.suppliers.index'));
    expect($supplier->fresh()->company_name)->toBe('EVGA Corporation');
    expect($supplier->fresh()->status)->toBe('inactive');

    // 4. Delete Supplier
    $deleteResponse = $this->actingAs($admin, 'backend')->delete(route('admin.suppliers.destroy', $supplier->id));
    $deleteResponse->assertRedirect(route('admin.suppliers.index'));
    expect(Supplier::find($supplier->id))->toBeNull();
});
