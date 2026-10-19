<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\FrontendUser;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\StorefrontProduct;
use App\Models\User;

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

it('displays all backend featured products ordered with newest first at the top', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    // Create 10 featured products from backend
    $firstProduct = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Alpha Featured Component',
        'is_featured' => true,
        'is_active' => true,
    ]);

    for ($i = 2; $i <= 9; $i++) {
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => "Middle Featured Component {$i}",
            'is_featured' => true,
            'is_active' => true,
        ]);
    }

    $lastProduct = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Omega Featured Component',
        'is_featured' => true,
        'is_active' => true,
    ]);

    // Create flash deal product
    Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'name' => 'Flash Deal Special',
        'price' => 100,
        'sale_price' => 70,
        'is_active' => true,
    ]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Featured Components');
    $response->assertSee('Alpha Featured Component');
    $response->assertSee('Omega Featured Component');

    // Assert all products are rendered
    for ($i = 2; $i <= 9; $i++) {
        $response->assertSee("Middle Featured Component {$i}");
    }

    // Verify latest product (Omega) appears before older product (Alpha)
    $content = $response->getContent();
    $posOmega = strpos($content, 'Omega Featured Component');
    $posAlpha = strpos($content, 'Alpha Featured Component');
    expect($posOmega)->toBeLessThan($posAlpha);

    // Verify Featured Components section is located at the top before Flash Deals
    $posFeaturedSection = strpos($content, 'Featured Components');
    $posDealsSection = strpos($content, 'Flash Deals');
    expect($posFeaturedSection)->toBeLessThan($posDealsSection);
});

it('paginates featured products when they exceed 3 rows (12 items)', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    // Create 16 featured products (exceeding 3 rows of 4 columns = 12 items)
    for ($i = 1; $i <= 16; $i++) {
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => sprintf('Row Test Part %02d', $i),
            'is_featured' => true,
            'is_active' => true,
            'sale_price' => null,
        ]);
    }

    // Page 1
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('16 Featured Products');
    // Latest parts should be on page 1 (Part 16 down to Part 05)
    $response->assertSee('Row Test Part 16');
    $response->assertSee('Row Test Part 05');
    $response->assertDontSee('Row Test Part 04');
    $response->assertSee('Next');

    // Page 2
    $page2Response = $this->get('/?page=2');
    $page2Response->assertStatus(200);
    $page2Response->assertSee('Row Test Part 04');
    $page2Response->assertSee('Row Test Part 01');
    $page2Response->assertDontSee('Row Test Part 16');
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

it('redirects unauthenticated guest visiting checkout to login page with intended url', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 199.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 199.00,
                'quantity' => 1,
            ],
        ],
    ]);

    $response = $this->get(route('checkout.index'));

    $response->assertRedirect(route('login'));
});

it('redirects unauthenticated guest submitting checkout to login page', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 150.00,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 150.00,
                'quantity' => 1,
            ],
        ],
    ]);

    $checkoutData = [
        'customer_name' => 'Guest User',
        'customer_email' => 'guest@example.test',
        'customer_phone' => '+1 555-0100',
        'street' => '123 Test St',
        'city' => 'Austin',
        'state' => 'TX',
        'postal_code' => '78701',
        'country' => 'United States',
        'payment_method' => 'cash_on_delivery',
    ];

    $response = $this->post(route('checkout.process'), $checkoutData);

    $response->assertRedirect(route('login'));
    expect(Order::where('customer_email', 'guest@example.test')->exists())->toBeFalse();
});

it('redirects customer back to intended checkout after logging in', function () {
    $customer = FrontendUser::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    // Simulate guest hit checkout, setting intended URL to /checkout
    $response = $this->withSession(['url.intended' => route('checkout.index')])
        ->post(route('login'), [
            'email' => $customer->email,
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('checkout.index'));
    $this->assertAuthenticatedAs($customer, 'frontend');
});

it('can successfully checkout, create order, and decrement stock when logged in', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 250.00,
        'stock_quantity' => 5,
        'is_active' => true,
    ]);

    $customer = FrontendUser::factory()->create([
        'name' => 'John Wick',
        'email' => 'john@continental.test',
        'phone' => '+1 555-0199',
        'address' => '100 Wall Street',
        'city' => 'New York',
        'state' => 'NY',
        'postal_code' => '10005',
        'country' => 'United States',
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

    $response = $this->actingAs($customer, 'frontend')
        ->withSession([
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
        ])
        ->post(route('checkout.process'), $checkoutData);

    $order = Order::where('customer_email', 'john@continental.test')->first();
    expect($order)->not->toBeNull();
    expect($order->frontend_user_id)->toBe($customer->id);
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

it('renders the hardware catalog pagination with modern storefront layout', function () {
    $category = Category::factory()->create();
    // Create 16 products to exceed the 12-item page limit
    Product::factory()->count(16)->create([
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->get(route('shop'));

    $response->assertStatus(200);
    $response->assertSee('Showing');
    $response->assertSee('1');
    $response->assertSee('to');
    $response->assertSee('12');
    $response->assertSee('results');
    $response->assertSee('Next');
    // Ensure it does NOT render raw unstyled bootstrap lists
    $response->assertDontSee('class="pagination"', false);
    $response->assertDontSee('class="page-item"', false);
});

it('can sort hardware catalog by bestseller and price options', function () {
    $category = Category::factory()->create();

    $productCheap = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Alpha Budget Fan',
        'price' => 20.00,
        'sale_price' => 15.00,
        'is_active' => true,
    ]);

    $productExpensive = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Omega Flagship GPU',
        'price' => 1500.00,
        'sale_price' => null,
        'is_active' => true,
    ]);

    $productBestSeller = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Beta Top Selling CPU Cooler',
        'price' => 90.00,
        'sale_price' => null,
        'is_active' => true,
    ]);

    // Curate product into bestseller showcase or add order items
    StorefrontProduct::create([
        'product_id' => $productBestSeller->id,
        'section' => 'bestseller',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    // 1. Test Best Seller sort
    $resBestseller = $this->get(route('shop', ['sort' => 'bestseller']));
    $resBestseller->assertStatus(200);
    $productsBestseller = $resBestseller->viewData('products');
    expect($productsBestseller->first()->id)->toBe($productBestSeller->id);

    // 2. Test Price: Low to High sort (effective price: $15.00 < $90.00 < $1500.00)
    $resPriceAsc = $this->get(route('shop', ['sort' => 'price_asc']));
    $resPriceAsc->assertStatus(200);
    $productsPriceAsc = $resPriceAsc->viewData('products');
    expect($productsPriceAsc->first()->id)->toBe($productCheap->id);

    // 3. Test Price: High to Low sort ($1500.00 > $90.00 > $15.00)
    $resPriceDesc = $this->get(route('shop', ['sort' => 'price_desc']));
    $resPriceDesc->assertStatus(200);
    $productsPriceDesc = $resPriceDesc->viewData('products');
    expect($productsPriceDesc->first()->id)->toBe($productExpensive->id);

    // 4. Test Name: A to Z sort ('Alpha...' then 'Beta...' then 'Omega...')
    $resNameAsc = $this->get(route('shop', ['sort' => 'name_asc']));
    $resNameAsc->assertStatus(200);
    $productsNameAsc = $resNameAsc->viewData('products');
    expect($productsNameAsc->first()->name)->toBe('Alpha Budget Fan');

    // 5. Test Name: Z to A sort ('Omega...' first)
    $resNameDesc = $this->get(route('shop', ['sort' => 'name_desc']));
    $resNameDesc->assertStatus(200);
    $productsNameDesc = $resNameDesc->viewData('products');
    expect($productsNameDesc->first()->name)->toBe('Omega Flagship GPU');
});

it('redirects guests attempting to checkout to storefront customer login', function () {
    $response = $this->get(route('checkout.index'));

    $response->assertRedirect(route('login'));
});

it('prevents backend users from accessing storefront checkout and redirects to login', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin, 'backend')->get(route('checkout.index'));

    $response->assertRedirect(route('login'));
});

it('displays customer profile link in storefront navigation for authenticated customers', function () {
    $customer = FrontendUser::factory()->create(['name' => 'Profile Customer']);

    $response = $this->actingAs($customer, 'frontend')->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee(route('customer.profile'));
    $response->assertSee('My Profile');
});

it('does not display empty cart error message when checkout is accessed after already placing order', function () {
    $customer = FrontendUser::factory()->create();

    $response = $this->actingAs($customer, 'frontend')
        ->withSession(['last_order_number' => 'ORD-TEST-12345'])
        ->get(route('checkout.index'));

    $response->assertRedirect(route('order.success', 'ORD-TEST-12345'));
    $response->assertSessionMissing('error');
});

it('renders floating toast container and seeds session messages into toasts', function () {
    $response = $this->withSession([
        'success' => 'Component added to your rig successfully!',
    ])->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('x-data="storefrontToaster()"', false);
    $response->assertSee('Component added to your rig successfully!');
});

it('fetches and displays active payment methods from backend on checkout', function () {
    $customer = FrontendUser::factory()->create();
    $product = Product::factory()->create(['price' => 200, 'stock_quantity' => 10]);

    // Create an active custom payment method and an inactive one
    PaymentMethod::create([
        'name' => 'Custom Crypto Payment',
        'code' => 'crypto_pay',
        'description' => 'Pay with Bitcoin or USDT',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    PaymentMethod::create([
        'name' => 'Disabled Gateway',
        'code' => 'disabled_gateway',
        'description' => 'This gateway should not appear',
        'is_active' => false,
        'sort_order' => 2,
    ]);

    $cart = [
        $product->id => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => 200,
            'quantity' => 1,
            'image' => null,
        ],
    ];

    $response = $this->actingAs($customer, 'frontend')
        ->withSession(['cart' => $cart])
        ->get(route('checkout.index'));

    $response->assertStatus(200);
    $response->assertSee('Custom Crypto Payment');
    $response->assertSee('crypto_pay');
    $response->assertDontSee('Disabled Gateway');
});

it('can add a product to cart via AJAX without page reload and returns json response', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Intel Core i9-14900K',
        'price' => 589.99,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('cart.add', $product->id), [
        'quantity' => 2,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'cart_count' => 2,
    ]);
    expect($response->json('message'))->toContain('Intel Core i9-14900K');
});

it('returns 422 json error when adding out-of-stock product via AJAX', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'RTX 4090 Rare Edition',
        'price' => 1999.99,
        'stock_quantity' => 1,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('cart.add', $product->id), [
        'quantity' => 5,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
    ]);
    expect($response->json('message'))->toContain('Only 1 units available in stock');
});

it('renders official warranty certificate and receipt on order success page', function () {
    $order = Order::factory()->create([
        'user_id' => null,
        'customer_id' => null,
        'order_number' => 'ORD-WARRANTY-1234',
        'status' => 'pending',
    ]);

    $response = $this->get(route('order.success', $order->order_number));

    $response->assertStatus(200);
    $response->assertSee('Warranty Certificate');
    $response->assertSee('Official Receipt');
    $response->assertSee('ORD-WARRANTY-1234');
});

it('can update cart quantity via JSON and recalculate totals', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'AMD Ryzen 9 7950X3D',
        'price' => 649.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 649.00,
                'quantity' => 1,
                'stock_quantity' => 10,
            ],
        ],
    ]);

    $response = $this->postJson(route('cart.update', $product->id), [
        'quantity' => 3,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'quantity' => 3,
        'cart_count' => 3,
        'item_total' => 1947.00,
        'subtotal' => 1947.00,
    ]);
});

it('rejects cart quantity updates exceeding available stock via JSON', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'MSI MAG B650',
        'price' => 199.00,
        'stock_quantity' => 4,
        'is_active' => true,
    ]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 199.00,
                'quantity' => 1,
                'stock_quantity' => 4,
            ],
        ],
    ]);

    $response = $this->postJson(route('cart.update', $product->id), [
        'quantity' => 10,
    ]);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'stock_quantity' => 4,
    ]);
    expect($response->json('message'))->toContain('Maximum available stock is 4 units');
});

it('can remove an item from cart via JSON', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name' => 'Corsair RAM',
        'price' => 120.00,
        'stock_quantity' => 10,
        'is_active' => true,
    ]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 120.00,
                'quantity' => 1,
                'stock_quantity' => 10,
            ],
        ],
    ]);

    $response = $this->postJson(route('cart.remove', $product->id));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'cart_count' => 0,
        'is_empty' => true,
    ]);
});

it('can clear the entire cart via JSON', function () {
    $product = Product::factory()->create(['price' => 50, 'stock_quantity' => 10]);

    $this->withSession([
        'cart' => [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => 50.00,
                'quantity' => 2,
            ],
        ],
    ]);

    $response = $this->postJson(route('cart.clear'));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'cart_count' => 0,
        'is_empty' => true,
    ]);
});
