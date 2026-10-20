<?php

use App\Models\AdminMenu;
use App\Models\CustomerGroup;
use App\Models\CustomerGroupPaymentTerm;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
});

it('allows admin to view customer group payment terms index', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $term = CustomerGroupPaymentTerm::create([
        'name' => 'Test Net 45 Days',
        'code' => 'TEST_NET_45',
        'days' => 45,
        'early_payment_discount_percentage' => 2.0,
        'early_payment_days' => 10,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->get(route('admin.customer-group-payment-terms.index'));

    $response->assertStatus(200);
    $response->assertSee('Test Net 45 Days');
    $response->assertSee('TEST_NET_45');
    $response->assertSee('Net 45');
});

it('allows admin to create a global payment term', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $payload = [
        'name' => 'Custom Due in 21 Days',
        'code' => 'NET_21',
        'customer_group_id' => null,
        'days' => 21,
        'description' => 'Special payment agreement for standard business orders.',
        'early_payment_discount_percentage' => 1.5,
        'early_payment_days' => 7,
        'late_fee_percentage' => 1.0,
        'require_deposit' => true,
        'deposit_percentage' => 20.0,
        'is_default' => false,
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.customer-group-payment-terms.store'), $payload);

    $response->assertRedirect(route('admin.customer-group-payment-terms.index'));
    $response->assertSessionHas('success');

    $term = CustomerGroupPaymentTerm::where('code', 'NET_21')->first();
    expect($term)->not->toBeNull();
    expect($term->name)->toBe('Custom Due in 21 Days');
    expect($term->days)->toBe(21);
    expect($term->customer_group_id)->toBeNull();
    expect((float) $term->early_payment_discount_percentage)->toBe(1.5);
    expect($term->early_payment_days)->toBe(7);
    expect($term->require_deposit)->toBeTrue();
    expect((float) $term->deposit_percentage)->toBe(20.0);
    expect($term->is_active)->toBeTrue();
});

it('allows admin to create a group-specific payment term', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::firstOrCreate(
        ['code' => 'TIER_ENTERPRISE'],
        [
            'name' => 'Tier Enterprise Accounts',
            'slug' => 'tier-enterprise-accounts',
            'default_discount_percentage' => 20.0,
            'is_active' => true,
        ]
    );

    $payload = [
        'name' => 'Enterprise Net 90',
        'code' => 'ENT_NET_90',
        'customer_group_id' => $group->id,
        'days' => 90,
        'description' => 'Extended terms exclusively for Tier Enterprise Accounts.',
        'early_payment_discount_percentage' => 3.0,
        'early_payment_days' => 15,
        'late_fee_percentage' => 2.0,
        'is_default' => true,
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.customer-group-payment-terms.store'), $payload);

    $response->assertRedirect(route('admin.customer-group-payment-terms.index'));
    $response->assertSessionHas('success');

    $term = CustomerGroupPaymentTerm::where('code', 'ENT_NET_90')->first();
    expect($term)->not->toBeNull();
    expect($term->customer_group_id)->toBe($group->id);
    expect($term->customerGroup->name)->toBe('Tier Enterprise Accounts');
    expect($term->is_default)->toBeTrue();
});

it('allows admin to update an existing payment term', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $term = CustomerGroupPaymentTerm::create([
        'name' => 'Original Term Name',
        'code' => 'ORIG_CODE',
        'days' => 30,
        'is_active' => true,
    ]);

    $updatePayload = [
        'name' => 'Updated Term Name',
        'code' => 'UPDATED_CODE',
        'days' => 40,
        'early_payment_discount_percentage' => 2.5,
        'early_payment_days' => 10,
        'late_fee_percentage' => 1.5,
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'backend')
        ->put(route('admin.customer-group-payment-terms.update', $term->id), $updatePayload);

    $response->assertRedirect(route('admin.customer-group-payment-terms.index'));
    $response->assertSessionHas('success');

    $fresh = $term->fresh();
    expect($fresh->name)->toBe('Updated Term Name');
    expect($fresh->code)->toBe('UPDATED_CODE');
    expect($fresh->days)->toBe(40);
    expect((float) $fresh->early_payment_discount_percentage)->toBe(2.5);
});

it('allows admin to toggle active status of a payment term', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $term = CustomerGroupPaymentTerm::create([
        'name' => 'Toggleable Term',
        'code' => 'TOGGLE_TERM',
        'days' => 15,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->patch(route('admin.customer-group-payment-terms.toggle-status', $term->id));

    $response->assertRedirect(route('admin.customer-group-payment-terms.index'));
    expect($term->fresh()->is_active)->toBeFalse();

    // Toggle back
    $this->actingAs($admin, 'backend')
        ->patch(route('admin.customer-group-payment-terms.toggle-status', $term->id));
    expect($term->fresh()->is_active)->toBeTrue();
});

it('allows admin to delete a payment term', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $term = CustomerGroupPaymentTerm::create([
        'name' => 'Term To Delete',
        'code' => 'DEL_TERM',
        'days' => 30,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->delete(route('admin.customer-group-payment-terms.destroy', $term->id));

    $response->assertRedirect(route('admin.customer-group-payment-terms.index'));
    $response->assertSessionHas('success');

    expect(CustomerGroupPaymentTerm::find($term->id))->toBeNull();
});

it('renders the group payment terms menu in the backend sidebar', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $tree = AdminMenu::getSidebarTree($admin);

    expect(isset($tree['People']))->toBeTrue();
    $peopleTitles = [];
    foreach ($tree['People'] as $item) {
        $peopleTitles[] = $item['title'];
        if (! empty($item['sub_items'])) {
            $peopleTitles = array_merge($peopleTitles, array_column($item['sub_items'], 'title'));
        }
    }
    expect($peopleTitles)->toContain('Group Payment Terms');
});

it('rejects creation if upfront deposit is required but percentage is omitted', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $payload = [
        'name' => 'Deposit Term Missing Percentage',
        'code' => 'DEP_INVALID',
        'days' => 30,
        'require_deposit' => true,
        'deposit_percentage' => null,
    ];

    $response = $this->actingAs($admin, 'backend')
        ->from(route('admin.customer-group-payment-terms.index'))
        ->post(route('admin.customer-group-payment-terms.store'), $payload);

    $response->assertSessionHasErrors(['deposit_percentage']);
    expect(CustomerGroupPaymentTerm::where('code', 'DEP_INVALID')->exists())->toBeFalse();
});

it('rejects creation if early payment days exceeds total settlement days', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $payload = [
        'name' => 'Invalid Days Sequence',
        'code' => 'INVALID_DAYS',
        'days' => 15,
        'early_payment_discount_percentage' => 3.0,
        'early_payment_days' => 20, // Greater than total days!
    ];

    $response = $this->actingAs($admin, 'backend')
        ->from(route('admin.customer-group-payment-terms.index'))
        ->post(route('admin.customer-group-payment-terms.store'), $payload);

    $response->assertSessionHasErrors(['early_payment_days']);
    expect(CustomerGroupPaymentTerm::where('code', 'INVALID_DAYS')->exists())->toBeFalse();
});
