<?php

use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
    $this->artisan('db:seed', ['--class' => 'ShippingMethodSeeder']);
    $this->artisan('db:seed', ['--class' => 'PaymentMethodSeeder']);
    $this->artisan('db:seed', ['--class' => 'BackendMenuSeeder']);
});

it('renders Payment Methods in sidebar for admin', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Payment Methods');
});

it('allows admin to view, create, toggle, update, and delete payment methods', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    // 1. View Index
    $indexResponse = $this->actingAs($admin, 'backend')->get(route('admin.payment-methods.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertSee('Cash on Delivery (COD)');
    $indexResponse->assertSee('Credit / Debit Card');
    $indexResponse->assertSee('Direct Bank Wire Transfer');
    $indexResponse->assertSee('ABA PAY / KHQR');

    // 2. Store new payment method
    $storeResponse = $this->actingAs($admin, 'backend')->post(route('admin.payment-methods.store'), [
        'name' => 'Cryptocurrency (USDT/BTC)',
        'code' => 'crypto_pay',
        'description' => 'Direct payment via Bitcoin or USDT TRC20.',
        'instructions' => 'Send exact amount to wallet address: 0x9B1... and submit transaction hash.',
        'is_active' => true,
        'sort_order' => 5,
    ]);
    $storeResponse->assertRedirect(route('admin.payment-methods.index'));

    $newMethod = PaymentMethod::where('code', 'crypto_pay')->first();
    expect($newMethod)->not->toBeNull();
    expect($newMethod->name)->toBe('Cryptocurrency (USDT/BTC)');

    // 3. Toggle Active status
    $toggleResponse = $this->actingAs($admin, 'backend')
        ->patch(route('admin.payment-methods.toggle-active', $newMethod->id));
    $toggleResponse->assertRedirect();
    expect($newMethod->fresh()->is_active)->toBeFalse();

    // 4. Update method
    $updateResponse = $this->actingAs($admin, 'backend')
        ->put(route('admin.payment-methods.update', $newMethod->id), [
            'name' => 'Crypto Web3 Pay',
            'code' => 'crypto_pay',
            'description' => 'Pay with Web3 wallet connection.',
            'is_active' => true,
        ]);
    $updateResponse->assertRedirect(route('admin.payment-methods.index'));
    expect($newMethod->fresh()->name)->toBe('Crypto Web3 Pay');
    expect($newMethod->fresh()->is_active)->toBeTrue();

    // 5. Delete method
    $deleteResponse = $this->actingAs($admin, 'backend')
        ->delete(route('admin.payment-methods.destroy', $newMethod->id));
    $deleteResponse->assertRedirect(route('admin.payment-methods.index'));
    expect(PaymentMethod::where('code', 'crypto_pay')->first())->toBeNull();
});

it('protects payment method management with permissions', function () {
    $staff = User::factory()->create(['is_approved' => true]);
    // Staff only has view dashboard permission
    $staff->givePermissionTo(Permission::findByName('view dashboard', 'backend'));

    // Forbidden: Payment Methods
    $this->actingAs($staff, 'backend')
        ->get(route('admin.payment-methods.index'))
        ->assertStatus(403);
});
