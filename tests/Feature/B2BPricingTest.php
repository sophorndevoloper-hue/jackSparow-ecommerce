<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Services\B2BPricingService;

beforeEach(function () {
    $this->category = Category::firstOrCreate(['slug' => 'processors-cpu'], ['name' => 'Processors (CPU)']);
    $this->brand = Brand::firstOrCreate(['slug' => 'amd'], ['name' => 'AMD']);
    $this->pricingService = app(B2BPricingService::class);
});

it('calculates tiered volume pricing based on quantity breaks', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'AMD Ryzen 9 7900X',
        'slug' => 'amd-ryzen-9-7900x',
        'sku' => 'CPU-7900X-TEST',
        'price' => 500.00,
        'cost_price' => 380.00,
        'stock_quantity' => 50,
        'low_stock_threshold' => 5,
        'moq' => 1,
    ]);

    // Volume breaks: 1-9: $500, 10-49: $450, 50+: $420
    ProductPriceTier::create([
        'product_id' => $product->id,
        'min_quantity' => 10,
        'max_quantity' => 49,
        'unit_price' => 450.00,
    ]);

    ProductPriceTier::create([
        'product_id' => $product->id,
        'min_quantity' => 50,
        'max_quantity' => null,
        'unit_price' => 420.00,
    ]);

    // Quantity 1 -> $500 base price
    $calc1 = $this->pricingService->calculateLineItemPricing($product, 1);
    expect($calc1['effective_unit_price'])->toBe(500.00);
    expect($calc1['subtotal'])->toBe(500.00);

    // Quantity 15 -> $450 tier
    $calc15 = $this->pricingService->calculateLineItemPricing($product, 15);
    expect($calc15['effective_unit_price'])->toBe(450.00);
    expect($calc15['subtotal'])->toBe(6750.00);
    expect($calc15['total_savings'])->toBe(750.00);

    // Quantity 60 -> $420 tier
    $calc60 = $this->pricingService->calculateLineItemPricing($product, 60);
    expect($calc60['effective_unit_price'])->toBe(420.00);
    expect($calc60['subtotal'])->toBe(25200.00);
});

it('applies custom pricing tier for specific customer group over general tiers', function () {
    $wholesaleGroup = CustomerGroup::create([
        'name' => 'Test Wholesale',
        'slug' => 'test-wholesale',
        'code' => 'TEST_WHOLESALE',
        'default_discount_percentage' => 10.00,
    ]);

    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'AMD Threadripper 7980X',
        'slug' => 'amd-threadripper-7980x',
        'sku' => 'CPU-TR-7980X',
        'price' => 4999.00,
        'cost_price' => 4000.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    // General tier
    ProductPriceTier::create([
        'product_id' => $product->id,
        'min_quantity' => 5,
        'unit_price' => 4600.00,
    ]);

    // Dedicated Wholesale Tier
    ProductPriceTier::create([
        'product_id' => $product->id,
        'customer_group_id' => $wholesaleGroup->id,
        'min_quantity' => 5,
        'unit_price' => 4400.00,
    ]);

    $calcWholesale = $this->pricingService->calculateLineItemPricing($product, 5, $wholesaleGroup);
    expect($calcWholesale['effective_unit_price'])->toBe(4400.00);

    $calcRetail = $this->pricingService->calculateLineItemPricing($product, 5);
    expect($calcRetail['effective_unit_price'])->toBe(4600.00);
});

it('strictly enforces margin floor protection and prevents sub-cost sales', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'NVIDIA RTX 4090 Test',
        'slug' => 'rtx-4090-test',
        'sku' => 'GPU-4090-GUARD',
        'price' => 1800.00,
        'cost_price' => 1500.00,
        'min_margin_percentage' => 15.00, // Floor = 1500 * 1.15 = $1725.00
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    expect($product->getFloorPrice())->toBe(1725.00);

    // Tier priced at $1600 (below the $1725 margin floor!)
    ProductPriceTier::create([
        'product_id' => $product->id,
        'min_quantity' => 10,
        'unit_price' => 1600.00,
    ]);

    $calc = $this->pricingService->calculateLineItemPricing($product, 10);

    // Must be guarded to floor price of $1725.00
    expect($calc['effective_unit_price'])->toBe(1725.00);
    expect($calc['is_floor_guarded'])->toBeTrue();
    expect($calc['subtotal'])->toBe(17250.00);
});

it('validates minimum order quantity and case pack multiples', function () {
    $product = Product::create([
        'category_id' => $this->category->id,
        'brand_id' => $this->brand->id,
        'name' => 'OEM Bulk Thermal Paste Pack',
        'slug' => 'oem-thermal-paste-pack',
        'sku' => 'PASTE-OEM-50',
        'price' => 50.00,
        'cost_price' => 30.00,
        'moq' => 10,
        'case_pack_multiple' => 5,
        'stock_quantity' => 500,
        'low_stock_threshold' => 20,
    ]);

    // Quantity 8 -> Fails MOQ (needs 10)
    $calc1 = $this->pricingService->calculateLineItemPricing($product, 8);
    expect($calc1['is_moq_satisfied'])->toBeFalse();
    expect($calc1['is_case_pack_satisfied'])->toBeFalse();

    // Quantity 12 -> MOQ satisfied (12 >= 10), but fails case pack multiple of 5
    $calc2 = $this->pricingService->calculateLineItemPricing($product, 12);
    expect($calc2['is_moq_satisfied'])->toBeTrue();
    expect($calc2['is_case_pack_satisfied'])->toBeFalse();

    // Quantity 15 -> Both satisfied!
    $calc3 = $this->pricingService->calculateLineItemPricing($product, 15);
    expect($calc3['is_moq_satisfied'])->toBeTrue();
    expect($calc3['is_case_pack_satisfied'])->toBeTrue();
});
