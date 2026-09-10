<?php

use App\Models\BackendProfile;
use App\Models\User;
use Database\Seeders\AdminMenuSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
    $this->seed(AdminMenuSeeder::class);
});

it('allows authenticated admin to view the dashboard profile page', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get(route('admin.profile.edit'));

    $response->assertStatus(200);
    $response->assertSee('My Account Profile');
    $response->assertSee('Personal Details &amp; Avatar', false);
    $response->assertSee('Change Password');
});

it('updates user credentials and backend profile details', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->put(route('admin.profile.update'), [
        'name' => 'captain_jack',
        'email' => 'jack@jacksparow.com',
        'full_name' => 'Captain Jack Sparrow',
        'phone' => '+1 555 777 8888',
        'designation' => 'Fleet Commander & Head of Tech',
        'bio' => 'Commander of the Black Pearl server cluster.',
        'address' => 'Pier 42, Tortuga Tech Hub',
    ]);

    $response->assertRedirect(route('admin.profile.edit'));
    $response->assertSessionHas('success');

    $admin->refresh();
    expect($admin->name)->toBe('captain_jack');
    expect($admin->email)->toBe('jack@jacksparow.com');

    $this->assertDatabaseHas('backend_profiles', [
        'user_id' => $admin->id,
        'full_name' => 'Captain Jack Sparrow',
        'phone' => '+1 555 777 8888',
        'designation' => 'Fleet Commander & Head of Tech',
        'bio' => 'Commander of the Black Pearl server cluster.',
        'address' => 'Pier 42, Tortuga Tech Hub',
    ]);

    expect($admin->profile)->toBeInstanceOf(BackendProfile::class);
    expect($admin->profile->user_id)->toBe($admin->id);
    expect($admin->profile->full_name)->toBe('Captain Jack Sparrow');
});

it('uploads and stores user avatar in backend_profile and handles file cleanup', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $firstAvatar = UploadedFile::fake()->image('first_avatar.png', 150, 150);

    // First upload
    $this->actingAs($admin, 'backend')->put(route('admin.profile.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => $firstAvatar,
    ]);

    $admin->refresh();
    $firstPath = $admin->profile->avatar;
    expect($firstPath)->not()->toBeNull();
    Storage::disk('public')->assertExists($firstPath);
    expect($admin->avatar_url)->toContain('storage/'.$firstPath);

    // Second upload (should delete first file)
    $secondAvatar = UploadedFile::fake()->image('second_avatar.jpg', 200, 200);

    $this->actingAs($admin, 'backend')->put(route('admin.profile.update'), [
        'name' => $admin->name,
        'email' => $admin->email,
        'avatar' => $secondAvatar,
    ]);

    $admin->refresh();
    $secondPath = $admin->profile->avatar;
    expect($secondPath)->not()->toBeNull();
    expect($secondPath)->not()->toBe($firstPath);

    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($secondPath);

    // Verify avatar URL is rendered in dashboard navbar
    $dashboardResponse = $this->actingAs($admin, 'backend')->get(route('admin.dashboard'));
    $dashboardResponse->assertSee($admin->avatar_url);
});

it('allows admin to change password with valid current password', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'password' => Hash::make('OldPassword123!'),
    ]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->put(route('admin.profile.password'), [
        'current_password' => 'OldPassword123!',
        'password' => 'NewSecurePassword2026!',
        'password_confirmation' => 'NewSecurePassword2026!',
    ]);

    $response->assertRedirect(route('admin.profile.edit'));
    $response->assertSessionHas('success');

    $admin->refresh();
    expect(Hash::check('NewSecurePassword2026!', $admin->password))->toBeTrue();
});

it('rejects password change when current password is wrong', function () {
    $admin = User::factory()->create([
        'is_approved' => true,
        'password' => Hash::make('CorrectPassword123!'),
    ]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->put(route('admin.profile.password'), [
        'current_password' => 'WrongPassword!',
        'password' => 'NewSecurePassword2026!',
        'password_confirmation' => 'NewSecurePassword2026!',
    ]);

    $response->assertSessionHasErrors(['current_password']);
    $admin->refresh();
    expect(Hash::check('CorrectPassword123!', $admin->password))->toBeTrue();
});

it('redirects backend authenticated user from /profile to admin.profile.edit', function () {
    $admin = User::factory()->create(['is_approved' => true]);
    $admin->assignRole(Role::findByName('admin', 'backend'));

    $response = $this->actingAs($admin, 'backend')->get('/profile');

    $response->assertRedirect(route('admin.profile.edit'));
});
