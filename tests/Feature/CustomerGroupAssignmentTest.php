<?php

use App\Models\CustomerGroup;
use App\Models\FrontendUser;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
});

it('allows admin to assign multiple customers to a customer group', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'WHOLESALE_VIP'],
        [
            'name' => 'Wholesale VIP Tier',
            'slug' => 'wholesale-vip-tier',
            'default_discount_percentage' => 15.0,
            'is_active' => true,
        ]
    );

    $customer1 = FrontendUser::factory()->create(['name' => 'User One', 'customer_group_id' => null]);
    $customer2 = FrontendUser::factory()->create(['name' => 'User Two', 'customer_group_id' => null]);
    $customer3 = FrontendUser::factory()->create(['name' => 'User Three', 'customer_group_id' => null]);

    $response = $this->actingAs($admin, 'backend')->post(
        route('admin.customer-groups.assign-users', $group->id),
        [
            'customer_ids' => [$customer1->id, $customer2->id],
        ]
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($customer1->fresh()->customer_group_id)->toBe($group->id);
    expect($customer2->fresh()->customer_group_id)->toBe($group->id);
    expect($customer3->fresh()->customer_group_id)->toBeNull();
    expect($group->fresh()->frontendUsers()->count())->toBe(2);
});

it('allows admin to remove a customer from a customer group', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'TIER_ONE'],
        [
            'name' => 'Tier One B2B',
            'slug' => 'tier-one-b2b',
            'default_discount_percentage' => 10.0,
            'is_active' => true,
        ]
    );

    $customer = FrontendUser::factory()->create([
        'name' => 'Group Member',
        'customer_group_id' => $group->id,
    ]);

    expect($customer->customer_group_id)->toBe($group->id);

    $response = $this->actingAs($admin, 'backend')->delete(
        route('admin.customer-groups.remove-user', [$group->id, $customer->id])
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($customer->fresh()->customer_group_id)->toBeNull();
    expect($group->fresh()->frontendUsers()->count())->toBe(0);
});

it('allows admin to bulk assign customers to a group from the customer list', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'BULK_TIER'],
        [
            'name' => 'Bulk Tier',
            'slug' => 'bulk-tier',
            'default_discount_percentage' => 20.0,
            'is_active' => true,
        ]
    );

    $customers = FrontendUser::factory()->count(3)->create(['customer_group_id' => null]);
    $customerIds = $customers->pluck('id')->all();

    $response = $this->actingAs($admin, 'backend')->post(
        route('admin.customers.bulk-assign-group'),
        [
            'customer_ids' => $customerIds,
            'customer_group_id' => $group->id,
        ]
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');

    foreach ($customers as $c) {
        expect($c->fresh()->customer_group_id)->toBe($group->id);
    }
});

it('allows admin to bulk remove customers from groups', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'TEMP_TIER'],
        [
            'name' => 'Temp Tier',
            'slug' => 'temp-tier',
            'default_discount_percentage' => 5.0,
            'is_active' => true,
        ]
    );

    $customers = FrontendUser::factory()->count(2)->create(['customer_group_id' => $group->id]);
    $customerIds = $customers->pluck('id')->all();

    $response = $this->actingAs($admin, 'backend')->post(
        route('admin.customers.bulk-assign-group'),
        [
            'customer_ids' => $customerIds,
            'customer_group_id' => null,
        ]
    );

    $response->assertRedirect();
    $response->assertSessionHas('success');

    foreach ($customers as $c) {
        expect($c->fresh()->customer_group_id)->toBeNull();
    }
});

it('renders assigned customers and available customers in the customer group modal view', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'DISPLAY_TIER'],
        [
            'name' => 'Display Tier Group',
            'slug' => 'display-tier-group',
            'default_discount_percentage' => 12.0,
            'is_active' => true,
        ]
    );

    $inGroupCustomer = FrontendUser::factory()->create([
        'name' => 'Already In Group Customer',
        'customer_group_id' => $group->id,
    ]);

    $outGroupCustomer = FrontendUser::factory()->create([
        'name' => 'Available Out of Group Customer',
        'customer_group_id' => null,
    ]);

    $response = $this->actingAs($admin, 'backend')->get(route('admin.customer-groups.index'));

    $response->assertStatus(200);
    $response->assertSee('Display Tier Group');
    $response->assertSee('Already In Group Customer');
    $response->assertSee('Available Out of Group Customer');
});
