<?php

use App\Models\FrontendUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new storefront customers can register into frontend_users table with only fullname email and password', function () {
    $response = $this->post('/register', [
        'name' => 'New Gamer',
        'email' => 'newgamer@store.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated('frontend');
    $response->assertRedirect(route('home'));

    expect(FrontendUser::where('email', 'newgamer@store.test')->exists())->toBeTrue();
    expect(User::where('email', 'newgamer@store.test')->exists())->toBeFalse();
});

test('customer can edit phone and shipping address in customer profile', function () {
    $customer = FrontendUser::factory()->create([
        'name' => 'Alex Customer',
        'email' => 'alex@store.test',
        'phone' => null,
        'address' => null,
    ]);

    // View profile screen
    $res = $this->actingAs($customer, 'frontend')->get(route('customer.profile'));
    $res->assertStatus(200);
    $res->assertSee('Alex Customer');
    $res->assertSee('Personal and Delivery Details');

    // Update profile columns
    $updateRes = $this->actingAs($customer, 'frontend')->put(route('customer.profile.update'), [
        'name' => 'Alex Updated',
        'email' => 'alex@store.test',
        'phone' => '+1 (555) 777-8888',
        'company_name' => 'Tech Corp',
        'address' => '742 Evergreen Terrace',
        'city' => 'Springfield',
        'state' => 'Oregon',
        'postal_code' => '97477',
        'country' => 'United States',
    ]);

    $updateRes->assertSessionHas('success');
    $customer->refresh();
    expect($customer->name)->toBe('Alex Updated');
    expect($customer->phone)->toBe('+1 (555) 777-8888');
    expect($customer->address)->toBe('742 Evergreen Terrace');
    expect($customer->city)->toBe('Springfield');
});

test('customer can update password in customer profile', function () {
    $customer = FrontendUser::factory()->create([
        'password' => 'oldpassword123',
    ]);

    $response = $this->actingAs($customer, 'frontend')->put(route('customer.profile.password'), [
        'current_password' => 'oldpassword123',
        'password' => 'newpassword456',
        'password_confirmation' => 'newpassword456',
    ]);

    $response->assertSessionHas('success');
    $customer->refresh();
    expect(Hash::check('newpassword456', $customer->password))->toBeTrue();
});
