<?php

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerGroupPaymentTerm;
use App\Models\FrontendUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'backend']);
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
});

it('allows B2B customer to checkout using group payment term with deposit and discount', function () {
    $group = CustomerGroup::create([
        'name' => 'Wholesale Elite',
        'code' => 'WHOLESALE_ELITE',
        'slug' => 'wholesale-elite',
        'credit_limit' => 50000.00,
        'is_active' => true,
    ]);

    $term = CustomerGroupPaymentTerm::create([
        'customer_group_id' => $group->id,
        'name' => 'Elite Net 30 with 20% Deposit',
        'code' => 'ELITE_NET_30',
        'days' => 30,
        'early_payment_discount_percentage' => 2.00,
        'early_payment_days' => 10,
        'late_fee_percentage' => 1.50,
        'require_deposit' => true,
        'deposit_percentage' => 20.00,
        'is_default' => true,
        'is_active' => true,
    ]);

    $b2bUser = FrontendUser::factory()->create([
        'customer_group_id' => $group->id,
        'email' => 'b2b@enterprise.com',
        'credit_limit' => 50000.00,
    ]);

    $product = Product::factory()->create([
        'name' => 'Workstation Server RAM 64GB',
        'price' => 200.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $cart = [
        $product->id => [
            'name' => $product->name,
            'price' => 200.00,
            'quantity' => 2, // Subtotal = $400.00
            'image' => null,
            'sku' => $product->sku,
        ],
    ];

    $payload = [
        'customer_name' => 'John Enterprise',
        'customer_email' => 'b2b@enterprise.com',
        'customer_phone' => '+15551234567',
        'street' => '100 Industrial Parkway',
        'city' => 'Austin',
        'state' => 'TX',
        'postal_code' => '78701',
        'country' => 'United States',
        'payment_method' => 'payment_term',
        'payment_term_id' => $term->id,
    ];

    $response = $this->actingAs($b2bUser, 'frontend')
        ->withSession(['cart' => $cart])
        ->post(route('checkout.process'), $payload);

    $response->assertSessionHasNoErrors();
    $order = Order::where('customer_email', 'b2b@enterprise.com')->latest()->first();

    expect($order)->not->toBeNull();
    expect($order->payment_term_id)->toBe($term->id);
    expect($order->payment_due_date->format('Y-m-d'))->toBe(now()->addDays(30)->format('Y-m-d'));
    expect($order->deposit_amount)->toBeGreaterThan(0.00);
    expect($order->early_payment_discount_amount)->toBeGreaterThan(0.00);
    expect($order->early_payment_discount_deadline->format('Y-m-d'))->toBe(now()->addDays(10)->format('Y-m-d'));
});

it('blocks B2B checkout when order exceeds available credit limit', function () {
    $group = CustomerGroup::create([
        'name' => 'Small Business Tier',
        'code' => 'SMB_TIER',
        'slug' => 'smb-tier',
        'credit_limit' => 500.00, // Small limit
        'is_active' => true,
    ]);

    $term = CustomerGroupPaymentTerm::create([
        'customer_group_id' => $group->id,
        'name' => 'SMB Net 15',
        'code' => 'SMB_NET_15',
        'days' => 15,
        'is_active' => true,
    ]);

    $b2bUser = FrontendUser::factory()->create([
        'customer_group_id' => $group->id,
        'credit_limit' => 500.00,
    ]);

    // Existing outstanding unpaid order of $400
    Order::factory()->create([
        'frontend_user_id' => $b2bUser->id,
        'payment_status' => 'pending',
        'total_amount' => 400.00,
    ]);

    $product = Product::factory()->create([
        'price' => 200.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    // New order is $200 + tax + shipping => > $200. Available credit is only $100 ($500 - $400)
    $cart = [
        $product->id => [
            'name' => $product->name,
            'price' => 200.00,
            'quantity' => 1,
            'image' => null,
            'sku' => $product->sku,
        ],
    ];

    $payload = [
        'customer_name' => 'SMB Owner',
        'customer_email' => $b2bUser->email,
        'customer_phone' => '+15559876543',
        'street' => '12 Commerce Way',
        'city' => 'Dallas',
        'state' => 'TX',
        'postal_code' => '75001',
        'country' => 'United States',
        'payment_method' => 'payment_term',
        'payment_term_id' => $term->id,
    ];

    $response = $this->actingAs($b2bUser, 'frontend')
        ->withSession(['cart' => $cart])
        ->from(route('checkout.index'))
        ->post(route('checkout.process'), $payload);

    $response->assertSessionHasErrors(['payment_method']);
});

it('attaches group payment term, due date, and deposit when quote is converted to order', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $group = CustomerGroup::create([
        'name' => 'Corporate Tier',
        'code' => 'CORP_TIER',
        'slug' => 'corp-tier',
        'is_active' => true,
    ]);

    $term = CustomerGroupPaymentTerm::create([
        'customer_group_id' => $group->id,
        'name' => 'Corporate Net 60',
        'code' => 'CORP_NET_60',
        'days' => 60,
        'require_deposit' => true,
        'deposit_percentage' => 25.00,
        'early_payment_discount_percentage' => 1.50,
        'early_payment_days' => 15,
        'is_default' => true,
        'is_active' => true,
    ]);

    Customer::factory()->create([
        'email' => 'procurement@megacorp.com',
        'company_name' => 'MegaCorp Industries',
        'customer_group_id' => $group->id,
    ]);

    $quote = Quote::create([
        'quote_number' => 'RFQ-2026-9999',
        'client_name' => 'Alice Chief',
        'company_name' => 'MegaCorp Industries',
        'client_email' => 'procurement@megacorp.com',
        'status' => 'approved',
        'subtotal' => 1000.00,
        'tax_amount' => 80.00,
        'total_quoted_price' => 1080.00,
        'target_margin_percent' => 20.0,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.quotes.convert-to-order', $quote->id));

    $response->assertRedirect();
    $order = Order::where('customer_email', 'procurement@megacorp.com')->latest()->first();

    expect($order)->not->toBeNull();
    expect($order->payment_term_id)->toBe($term->id);
    expect($order->payment_due_date->format('Y-m-d'))->toBe(now()->addDays(60)->format('Y-m-d'));
    expect((float) $order->deposit_amount)->toBe(270.00); // 25% of 1080
    expect((float) $order->early_payment_discount_amount)->toBe(15.00); // 1.5% of 1000
});

it('applies late penalty fee to overdue orders operating under payment terms via artisan command', function () {
    $term = CustomerGroupPaymentTerm::create([
        'name' => 'Net 30 with 2% Late Fee',
        'code' => 'NET_30_LATE',
        'days' => 30,
        'late_fee_percentage' => 2.00,
        'is_active' => true,
    ]);

    $overdueOrder = Order::factory()->create([
        'order_number' => 'ORD-OVERDUE-01',
        'payment_status' => 'pending',
        'payment_term_id' => $term->id,
        'payment_due_date' => now()->subDays(5)->toDateString(), // 5 days past due!
        'total_amount' => 1000.00,
        'late_fee_amount' => 0.00,
        'late_fee_applied_at' => null,
    ]);

    $this->artisan('orders:process-overdue-terms')
        ->assertExitCode(0);

    $overdueOrder->refresh();
    expect((float) $overdueOrder->late_fee_amount)->toBe(20.00); // 2% of 1000
    expect($overdueOrder->late_fee_applied_at)->not->toBeNull();
    expect($overdueOrder->remaining_balance)->toBe(1020.00); // 1000 + 20
});

it('applies early payment discount when order is settled on or before deadline', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));
    $admin->givePermissionTo(Permission::firstOrCreate(['name' => 'edit orders', 'guard_name' => 'backend']));

    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
        'subtotal' => 1000.00,
        'total_amount' => 1080.00,
        'discount_amount' => 0.00,
        'early_payment_discount_amount' => 20.00,
        'early_payment_discount_deadline' => now()->addDays(5)->toDateString(),
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->patch(route('admin.orders.status', $order->id), [
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);

    $response->assertRedirect();
    $order->refresh();
    expect($order->payment_status)->toBe('paid');
    expect((float) $order->discount_amount)->toBe(20.00);
    expect((float) $order->total_amount)->toBe(1060.00); // 1080 - 20
});

it('allows admin to mark upfront deposit as paid', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));
    $admin->givePermissionTo(Permission::firstOrCreate(['name' => 'edit orders', 'guard_name' => 'backend']));

    $order = Order::factory()->create([
        'payment_status' => 'pending',
        'total_amount' => 1000.00,
        'deposit_amount' => 250.00,
        'deposit_paid' => false,
        'deposit_paid_at' => null,
    ]);

    $response = $this->actingAs($admin, 'backend')
        ->patch(route('admin.orders.mark-deposit-paid', $order->id));

    $response->assertRedirect();
    $order->refresh();
    expect($order->deposit_paid)->toBeTrue();
    expect($order->deposit_paid_at)->not->toBeNull();
    expect($order->payment_status)->toBe('partially_paid');
    expect($order->remaining_balance)->toBe(750.00); // 1000 - 250
});

it('automatically creates default payment term when customer group with payment_terms_days is created', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $payload = [
        'name' => 'Wholesale Gold Tier',
        'code' => 'WHOLESALE_GOLD',
        'default_discount_percentage' => 12.0,
        'payment_terms_days' => 45,
        'credit_limit' => 25000.00,
        'is_active' => true,
    ];

    $response = $this->actingAs($admin, 'backend')
        ->post(route('admin.customer-groups.store'), $payload);

    $response->assertRedirect(route('admin.customer-groups.index'));

    $group = CustomerGroup::where('code', 'WHOLESALE_GOLD')->first();
    expect($group)->not->toBeNull();

    $term = CustomerGroupPaymentTerm::where('customer_group_id', $group->id)->first();
    expect($term)->not->toBeNull();
    expect($term->days)->toBe(45);
    expect($term->is_default)->toBeTrue();
    expect($term->is_active)->toBeTrue();
});
