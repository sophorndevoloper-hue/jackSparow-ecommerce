<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;

it('renders the storefront homepage with featured hardware and categories', function () {
    $category = Category::factory()->create(['name' => 'Processors (CPU)', 'slug' => 'processors-cpu']);
    $brand = Brand::factory()->create(['name' => 'AMD', 'slug' => 'amd']);
    Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'AMD Ryzen 7 7800X3D',
        'is_featured' => true,
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Build Your Ultimate');
    $response->assertSee('AMD Ryzen 7 7800X3D');
});

it('renders the hardware shop catalog with filtering', function () {
    $category = Category::factory()->create(['name' => 'Graphics Cards (GPU)', 'slug' => 'graphics-cards-gpu']);
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'NVIDIA GeForce RTX 4080 Super',
        'is_active' => true,
    ]);

    $response = $this->get(route('shop', ['category' => 'graphics-cards-gpu']));

    $response->assertStatus(200);
    $response->assertSee('NVIDIA GeForce RTX 4080 Super');
});

it('renders the computer part detail page with specifications', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Corsair Vengeance RGB DDR5 32GB',
        'slug' => 'corsair-vengeance-ddr5',
        'specifications' => ['Speed' => '6000 MHz', 'Latency' => 'CL30'],
        'is_active' => true,
    ]);

    $response = $this->get(route('product.show', $product->slug));

    $response->assertStatus(200);
    $response->assertSee('Corsair Vengeance RGB DDR5 32GB');
    $response->assertSee('6000 MHz');
});

it('can add a computer component to cart and view cart', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 399.99,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $response = $this->post(route('cart.add', $product->id), ['quantity' => 2]);

    $response->assertSessionHas('cart');
    $response->assertSessionHas('success');

    $cartResponse = $this->get(route('cart.index'));
    $cartResponse->assertStatus(200);
    $cartResponse->assertSee($product->name);
});

it('can successfully checkout, create order, and decrement stock', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 250.00,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);

    // Simulate item in cart
    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'price' => 250.00,
                'quantity' => 2,
                'stock_quantity' => 5,
                'category_name' => $category->name,
                'brand_name' => 'Hardware',
            ],
        ],
    ]);

    $checkoutData = [
        'customer_name' => 'John Wick',
        'customer_email' => 'john@continental.test',
        'customer_phone' => '+1 555-0199',
        'street' => '100 Wall Street',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10005',
        'country' => 'United States',
        'payment_method' => 'cash_on_delivery',
        'customer_notes' => 'Leave package by the front door.',
    ];

    $response = $this->post(route('checkout.process'), $checkoutData);

    $order = Order::where('customer_email', 'john@continental.test')->first();
    expect($order)->not->toBeNull();
    expect($order->items)->toHaveCount(1);
    expect($order->status)->toBe('pending');
    expect($order->total_amount)->toBeGreaterThan(500.00);

    // Verify stock was decremented from 5 to 3
    $product->refresh();
    expect($product->stock_quantity)->toBe(3);

    $response->assertRedirect(route('order.success', $order->order_number));

    // View order success receipt
    $receiptResponse = $this->get(route('order.success', $order->order_number));
    $receiptResponse->assertStatus(200);
    $receiptResponse->assertSee($order->order_number);
});
