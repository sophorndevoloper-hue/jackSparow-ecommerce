<?php

use App\Models\CustomerGroup;
use App\Models\FrontendUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
});

it('allows admin to view customer management list with all actions', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $customer = FrontendUser::factory()->create([
        'name' => 'Test Gaming Customer',
        'email' => 'gamer@customer.test',
        'city' => 'Austin',
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.customers.index'));

    $response->assertStatus(200);
    $response->assertSee('Test Gaming Customer');
    $response->assertSee('gamer@customer.test');
    $response->assertSee('Add Customer');
});

it('allows admin to view customer create page', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.customers.create'));

    $response->assertStatus(200);
    $response->assertSee('Register Storefront Customer');
});

it('allows admin to create a new customer', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'TEST_RETAIL'],
        ['name' => 'Test Retail Tier', 'slug' => 'test-retail-tier', 'default_discount_percentage' => 5.0, 'is_active' => true]
    );

    $customerData = [
        'name' => 'Jane Matrix',
        'email' => 'jane.matrix@hardware.test',
        'password' => 'secretPassword123!',
        'phone' => '+1 (555) 789-0123',
        'customer_group_id' => $group->id,
        'company_name' => 'Matrix Rig Builds',
        'tax_vat_number' => 'US-987654321',
        'address' => '100 Cyber Boulevard',
        'city' => 'San Jose',
        'state' => 'CA',
        'postal_code' => '95112',
        'country' => 'United States',
        'credit_limit' => 1500.00,
    ];

    $response = $this->actingAs($admin, 'backend')->post(route('admin.customers.store'), $customerData);

    $response->assertRedirect(route('admin.customers.index'));
    $response->assertSessionHas('success');

    $created = FrontendUser::where('email', 'jane.matrix@hardware.test')->first();
    expect($created)->not->toBeNull();
    expect($created->name)->toBe('Jane Matrix');
    expect($created->customer_type)->toBe('simple');
    expect($created->company_name)->toBe('Matrix Rig Builds');
    expect($created->customer_group_id)->toBe($group->id);
    expect($created->is_blocked)->toBeFalse();
    expect(Hash::check('secretPassword123!', $created->password))->toBeTrue();
});

it('allows admin to view customer edit page', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $customer = FrontendUser::factory()->create([
        'name' => 'Editable Customer',
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.customers.edit', $customer->id));

    $response->assertStatus(200);
    $response->assertSee('Edit Customer: Editable Customer');
});

it('allows admin to update an existing customer', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $customer = FrontendUser::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@customer.test',
        'phone' => '+1 111 1111',
        'customer_type' => 'simple',
    ]);

    $updateData = [
        'name' => 'Updated Customer Name',
        'email' => 'updated@customer.test',
        'phone' => '+1 222 2222',
        'company_name' => 'Pro Gamer Corp',
        'city' => 'Dallas',
    ];

    $response = $this->actingAs($admin, 'backend')->put(route('admin.customers.update', $customer->id), $updateData);

    $response->assertRedirect(route('admin.customers.index'));
    $response->assertSessionHas('success');

    $fresh = $customer->fresh();
    expect($fresh->name)->toBe('Updated Customer Name');
    expect($fresh->email)->toBe('updated@customer.test');
    expect($fresh->phone)->toBe('+1 222 2222');
    expect($fresh->customer_type)->toBe('simple');
    expect($fresh->company_name)->toBe('Pro Gamer Corp');
    expect($fresh->city)->toBe('Dallas');
});

it('allows admin to block and unblock a customer', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $customer = FrontendUser::factory()->create([
        'is_blocked' => false,
        'blocked_at' => null,
    ]);

    // Block the customer
    $blockResponse = $this->actingAs($admin, 'backend')->patch(route('admin.customers.toggle-block', $customer->id));
    $blockResponse->assertRedirect();
    $blockResponse->assertSessionHas('success');

    $freshBlocked = $customer->fresh();
    expect($freshBlocked->is_blocked)->toBeTrue();
    expect($freshBlocked->blocked_at)->not->toBeNull();
    expect($freshBlocked->isBlocked())->toBeTrue();

    // Unblock the customer
    $unblockResponse = $this->actingAs($admin, 'backend')->patch(route('admin.customers.toggle-block', $customer->id));
    $unblockResponse->assertRedirect();
    $unblockResponse->assertSessionHas('success');

    $freshUnblocked = $customer->fresh();
    expect($freshUnblocked->is_blocked)->toBeFalse();
    expect($freshUnblocked->blocked_at)->toBeNull();
    expect($freshUnblocked->isBlocked())->toBeFalse();
});

it('prevents a blocked customer from logging in to the storefront', function () {
    $customer = FrontendUser::factory()->create([
        'email' => 'blocked.customer@example.com',
        'password' => Hash::make('password123'),
        'is_blocked' => true,
        'blocked_at' => now(),
    ]);

    $response = $this->post(route('login'), [
        'email' => 'blocked.customer@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('frontend');
});

it('allows an unblocked customer to log in to the storefront', function () {
    $customer = FrontendUser::factory()->create([
        'email' => 'active.customer@example.com',
        'password' => Hash::make('password123'),
        'is_blocked' => false,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'active.customer@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated('frontend');
    $response->assertRedirect(route('home'));
});

it('allows admin to delete a customer account', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $customer = FrontendUser::factory()->create([
        'name' => 'To Be Deleted',
        'email' => 'delete.me@customer.test',
    ]);

    $response = $this->actingAs($admin, 'backend')->delete(route('admin.customers.destroy', $customer->id));

    $response->assertRedirect(route('admin.customers.index'));
    $response->assertSessionHas('success');

    expect(FrontendUser::find($customer->id))->toBeNull();
});
