<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\HardwareCompatibilityService;

beforeEach(function () {
    $this->cpuCategory = Category::firstOrCreate(['slug' => 'processors-cpu'], ['name' => 'Processors (CPU)']);
    $this->moboCategory = Category::firstOrCreate(['slug' => 'motherboards'], ['name' => 'Motherboards']);
    $this->psuCategory = Category::firstOrCreate(['slug' => 'power-supplies-psu'], ['name' => 'Power Supplies (PSU)']);
    $this->brand = Brand::firstOrCreate(['slug' => 'amd'], ['name' => 'AMD']);
    $this->compatService = app(HardwareCompatibilityService::class);
});

it('detects socket compatibility between CPU and Motherboard', function () {
    $am5Cpu = Product::create([
        'category_id' => $this->cpuCategory->id,
        'brand_id' => $this->brand->id,
        'name' => 'AMD Ryzen 7 7700X',
        'slug' => 'amd-ryzen-7-7700x',
        'sku' => 'CPU-AM5-7700X',
        'socket' => 'AM5',
        'tdp_watts' => 105,
        'price' => 349.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    $am5Mobo = Product::create([
        'category_id' => $this->moboCategory->id,
        'brand_id' => $this->brand->id,
        'name' => 'ASUS TUF Gaming B650-PLUS',
        'slug' => 'asus-tuf-b650-plus',
        'sku' => 'MB-AM5-B650',
        'socket' => 'AM5',
        'chipset' => 'B650',
        'price' => 209.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    $intelMobo = Product::create([
        'category_id' => $this->moboCategory->id,
        'brand_id' => $this->brand->id,
        'name' => 'MSI PRO Z790-P',
        'slug' => 'msi-pro-z790-p',
        'sku' => 'MB-INT-Z790',
        'socket' => 'LGA1700',
        'chipset' => 'Z790',
        'price' => 229.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    // Matching AM5 <-> AM5
    $checkMatching = $this->compatService->checkPairCompatibility($am5Cpu, $am5Mobo);
    expect($checkMatching['is_compatible'])->toBeTrue();
    expect($checkMatching['issues'])->toBeEmpty();

    // Mismatched AM5 <-> LGA1700
    $checkMismatched = $this->compatService->checkPairCompatibility($am5Cpu, $intelMobo);
    expect($checkMismatched['is_compatible'])->toBeFalse();
    expect($checkMismatched['issues'])->not->toBeEmpty();
    expect($checkMismatched['issues'][0])->toContain('Socket Incompatibility');
});

it('validates entire system bill of materials and verifies power budget', function () {
    $cpu = Product::create([
        'category_id' => $this->cpuCategory->id,
        'name' => 'Intel Core i9-14900K Test',
        'slug' => 'intel-i9-14900k-test',
        'sku' => 'CPU-14900K-TEST',
        'socket' => 'LGA1700',
        'tdp_watts' => 253,
        'price' => 580.00,
        'stock_quantity' => 5,
        'low_stock_threshold' => 1,
    ]);

    $gpu = Product::create([
        'category_id' => $this->cpuCategory->id,
        'name' => 'RTX 4090 Test GPU',
        'slug' => 'rtx-4090-test-gpu',
        'sku' => 'GPU-4090-TEST',
        'tdp_watts' => 450,
        'price' => 1999.00,
        'stock_quantity' => 5,
        'low_stock_threshold' => 1,
    ]);

    $insufficientPsu = Product::create([
        'category_id' => $this->psuCategory->id,
        'name' => '500W Budget Power Supply',
        'slug' => '500w-budget-psu',
        'sku' => 'PSU-500W-TEST',
        'power_requirement_watts' => 500,
        'price' => 59.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    $adequatePsu = Product::create([
        'category_id' => $this->psuCategory->id,
        'name' => '1000W Platinum Power Supply',
        'slug' => '1000w-platinum-psu',
        'sku' => 'PSU-1000W-TEST',
        'power_requirement_watts' => 1000,
        'price' => 220.00,
        'stock_quantity' => 10,
        'low_stock_threshold' => 2,
    ]);

    // Total draw: CPU (253W) + GPU (450W) = 703W
    // With 500W PSU -> Fails!
    $badBuild = $this->compatService->validateSystemBuild([$cpu, $gpu, $insufficientPsu]);
    expect($badBuild['is_valid'])->toBeFalse();
    expect($badBuild['total_tdp_watts'])->toBe(703);
    expect($badBuild['is_power_sufficient'])->toBeFalse();

    // With 1000W PSU -> Valid!
    $goodBuild = $this->compatService->validateSystemBuild([$cpu, $gpu, $adequatePsu]);
    expect($goodBuild['is_valid'])->toBeTrue();
    expect($goodBuild['is_power_sufficient'])->toBeTrue();
});
