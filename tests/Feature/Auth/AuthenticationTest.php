<?php

use App\Models\FrontendUser;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
});

test('storefront login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertSee('Storefront Sign In');
});

test('admin login screen can be rendered', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('Admin Portal Sign In');
});

test('admin users can authenticate using the admin login screen', function () {
    $admin = User::factory()->create(['password' => 'password123']);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticated('backend');
    $response->assertRedirect(route('admin.dashboard'));
});

test('storefront customers can authenticate using the storefront login screen', function () {
    $customer = FrontendUser::factory()->create(['password' => 'password123']);

    $response = $this->post('/login', [
        'email' => $customer->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticated('frontend');
    $response->assertRedirect(route('home'));
});

test('customers can not authenticate with invalid password', function () {
    $customer = FrontendUser::factory()->create();

    $this->post('/login', [
        'email' => $customer->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('frontend');
});

test('customers can logout', function () {
    $customer = FrontendUser::factory()->create();

    $response = $this->actingAs($customer, 'frontend')->post('/logout');

    $this->assertGuest('frontend');
    $response->assertRedirect('/');
});

test('admins can logout', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->post('/admin/logout');

    $this->assertGuest('backend');
    $response->assertRedirect(route('admin.login'));
});
