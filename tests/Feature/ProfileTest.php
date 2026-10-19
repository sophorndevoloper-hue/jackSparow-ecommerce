<?php

use App\Models\FrontendUser;

test('customer profile page is displayed', function () {
    $customer = FrontendUser::factory()->create();

    $response = $this
        ->actingAs($customer, 'frontend')
        ->get(route('customer.profile'));

    $response->assertOk()
        ->assertSee($customer->name)
        ->assertSee($customer->email);
});

test('customer profile information can be updated using frontend profile request', function () {
    $customer = FrontendUser::factory()->create();

    $response = $this
        ->actingAs($customer, 'frontend')
        ->put(route('customer.profile.update'), [
            'name' => 'Updated Customer',
            'email' => 'updated_customer@example.com',
            'phone' => '+1 555-0199',
            'company_name' => 'Tech Corp',
            'address' => '123 Tech Blvd',
            'city' => 'Phnom Penh',
            'state' => 'Central',
            'postal_code' => '12000',
            'country' => 'Cambodia',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $customer->refresh();

    expect($customer->name)->toBe('Updated Customer')
        ->and($customer->email)->toBe('updated_customer@example.com')
        ->and($customer->company_name)->toBe('Tech Corp');
});

test('unified profile url redirects customer to customer profile', function () {
    $customer = FrontendUser::factory()->create();

    $response = $this
        ->actingAs($customer, 'frontend')
        ->get('/profile');

    $response->assertRedirect(route('customer.profile'));
});
