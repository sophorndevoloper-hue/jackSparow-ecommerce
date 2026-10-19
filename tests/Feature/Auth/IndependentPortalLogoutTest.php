<?php

use App\Models\FrontendUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('preserves admin session when logging out of the storefront customer portal', function () {
    $customer = FrontendUser::factory()->create();
    $admin = User::factory()->create([
        'is_approved' => true,
    ]);

    // Log both guards in within the same session
    $this->actingAs($customer, 'frontend');
    $this->actingAs($admin, 'backend');

    expect(Auth::guard('frontend')->check())->toBeTrue();
    expect(Auth::guard('backend')->check())->toBeTrue();

    // Customer logs out via storefront
    $response = $this->post(route('logout'));
    $response->assertRedirect(route('home'));

    // Customer is logged out, but Admin remains logged in
    expect(Auth::guard('frontend')->check())->toBeFalse();
    expect(Auth::guard('backend')->check())->toBeTrue();
});

it('preserves customer session when logging out of the admin portal', function () {
    $customer = FrontendUser::factory()->create();
    $admin = User::factory()->create([
        'is_approved' => true,
    ]);

    // Log both guards in within the same session
    $this->actingAs($customer, 'frontend');
    $this->actingAs($admin, 'backend');

    expect(Auth::guard('frontend')->check())->toBeTrue();
    expect(Auth::guard('backend')->check())->toBeTrue();

    // Admin logs out via admin portal
    $response = $this->post(route('admin.logout'));
    $response->assertRedirect(route('admin.login'));

    // Admin is logged out, but Customer remains logged in
    expect(Auth::guard('backend')->check())->toBeFalse();
    expect(Auth::guard('frontend')->check())->toBeTrue();
});
