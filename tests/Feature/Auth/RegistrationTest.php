<?php

use App\Models\FrontendUser;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new storefront customers can register into frontend_users table', function () {
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
