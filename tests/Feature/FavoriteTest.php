<?php

use App\Models\Category;
use App\Models\Favorite;
use App\Models\FrontendUser;
use App\Models\Product;

it('renders the favorites page for guests and shows empty state', function () {
    $response = $this->get(route('favorites.index'));

    $response->assertStatus(200);
    $response->assertSee('Saved Hardware Favorites');
    $response->assertSee('Your Wishlist is Currently Empty');
});

it('allows guests to toggle product favorites via session', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'AMD Ryzen 9 7950X',
        'is_active' => true,
    ]);

    // First toggle: Add to favorites
    $response = $this->postJson(route('favorites.toggle', $product->id));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'is_favorited' => true,
        'favorite_count' => 1,
    ]);

    expect(session('favorites'))->toContain($product->id);

    // Second toggle: Remove from favorites
    $response2 = $this->postJson(route('favorites.toggle', $product->id));

    $response2->assertStatus(200);
    $response2->assertJson([
        'success' => true,
        'is_favorited' => false,
        'favorite_count' => 0,
    ]);

    expect(session('favorites'))->not->toContain($product->id);
});

it('persists favorites in database for authenticated customers', function () {
    $customer = FrontendUser::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'NVIDIA RTX 4090 OC',
        'is_active' => true,
    ]);

    $this->actingAs($customer, 'frontend');

    // Add to favorites
    $response = $this->postJson(route('favorites.toggle', $product->id));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'is_favorited' => true,
        'favorite_count' => 1,
    ]);

    $this->assertDatabaseHas('favorites', [
        'frontend_user_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    // View favorites page
    $pageResponse = $this->get(route('favorites.index'));
    $pageResponse->assertStatus(200);
    $pageResponse->assertSee('NVIDIA RTX 4090 OC');

    // Toggle off
    $response2 = $this->postJson(route('favorites.toggle', $product->id));
    $response2->assertJson([
        'success' => true,
        'is_favorited' => false,
        'favorite_count' => 0,
    ]);

    $this->assertDatabaseMissing('favorites', [
        'frontend_user_id' => $customer->id,
        'product_id' => $product->id,
    ]);
});

it('merges guest session favorites into database when logging in', function () {
    $category = Category::factory()->create();
    $product1 = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);
    $product2 = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

    $customer = FrontendUser::factory()->create([
        'email' => 'tech_builder@example.com',
        'password' => bcrypt('password123'),
    ]);

    // Simulate guest favorites in session
    session(['favorites' => [$product1->id, $product2->id]]);

    // Customer logs in
    $response = $this->post(route('login'), [
        'email' => 'tech_builder@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('home'));

    $this->assertDatabaseHas('favorites', [
        'frontend_user_id' => $customer->id,
        'product_id' => $product1->id,
    ]);

    $this->assertDatabaseHas('favorites', [
        'frontend_user_id' => $customer->id,
        'product_id' => $product2->id,
    ]);
});

it('allows customer to clear all favorites', function () {
    $customer = FrontendUser::factory()->create();
    $category = Category::factory()->create();
    $product1 = Product::factory()->create(['category_id' => $category->id, 'is_active' => true]);

    Favorite::create([
        'frontend_user_id' => $customer->id,
        'product_id' => $product1->id,
    ]);

    $this->actingAs($customer, 'frontend');

    $response = $this->post(route('favorites.clear'));

    $response->assertRedirect(route('favorites.index'));
    $this->assertDatabaseMissing('favorites', [
        'frontend_user_id' => $customer->id,
    ]);
});

it('tracks visited products in recently viewed session and renders recently viewed page', function () {
    $category = Category::factory()->create();
    $productA = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Intel Core i9-14900K',
        'slug' => 'intel-core-i9-14900k',
        'is_active' => true,
    ]);
    $productB = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Samsung 990 Pro 2TB',
        'slug' => 'samsung-990-pro-2tb',
        'is_active' => true,
    ]);

    // Visit product A
    $this->get(route('product.show', $productA->slug))->assertStatus(200);
    expect(session('recently_viewed'))->toContain($productA->id);

    // Visit product B
    $this->get(route('product.show', $productB->slug))->assertStatus(200);
    expect(session('recently_viewed'))->toContain($productB->id);
    expect(session('recently_viewed')[0])->toBe($productB->id);

    // Visit recently viewed page
    $recentResponse = $this->get(route('recently-viewed.index'));
    $recentResponse->assertStatus(200);
    $recentResponse->assertSee('Recently Viewed Hardware');
    $recentResponse->assertSee('Intel Core i9-14900K');
    $recentResponse->assertSee('Samsung 990 Pro 2TB');

    // Clear recently viewed history
    $clearResponse = $this->post(route('recently-viewed.clear'));
    $clearResponse->assertRedirect(route('recently-viewed.index'));
    expect(session('recently_viewed'))->toBeNull();
});
