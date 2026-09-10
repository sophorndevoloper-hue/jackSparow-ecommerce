<?php

use App\DataTables\BrandDataTable;
use App\DataTables\CategoryDataTable;
use App\DataTables\CustomerDataTable;
use App\DataTables\CustomerGroupDataTable;
use App\DataTables\MakeDataTable;
use App\DataTables\OrderDataTable;
use App\DataTables\ProductDataTable;
use App\DataTables\SerialNumberDataTable;
use App\DataTables\StockAdjustmentDataTable;
use App\DataTables\StockDataTable;
use App\DataTables\StockTransferDataTable;
use App\DataTables\SupplierDataTable;
use App\DataTables\UserDataTable;
use App\DataTables\WarehouseDataTable;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Make;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'backend']);
    $permissions = ['view dashboard', 'view products', 'edit products'];

    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'backend']);
    }

    $adminRole->syncPermissions(Permission::where('guard_name', 'backend')->get());
});

it('instantiates yajra product data table service class with query and columns', function () {
    $dataTable = app(ProductDataTable::class);

    $html = $dataTable->html();
    expect($html)->toBeInstanceOf(HtmlBuilder::class);
    expect($html->getTableAttribute('id'))->toBe('product-datatable');

    $columns = $dataTable->getColumns();
    expect(count($columns))->toBe(7);

    $columnTitles = array_map(fn ($col) => $col->title, $columns);
    expect($columnTitles)->toContain('Hardware Part Details');
    expect($columnTitles)->toContain('Category & Specs');
    expect($columnTitles)->toContain('B2B Pricing & Floor');
    expect($columnTitles)->toContain('MOQ / Case');
    expect($columnTitles)->toContain('Stock & Serials');
    expect($columnTitles)->toContain('Status');
    expect($columnTitles)->toContain('Actions');
});

it('returns processed eloquent datatable json with yajra service class', function () {
    $category = Category::factory()->create(['name' => 'CPUs']);
    $brand = Brand::factory()->create(['name' => 'AMD']);
    $make = Make::factory()->create(['name' => 'Ryzen Pro']);

    $product = Product::factory()->create([
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'make_id' => $make->id,
        'name' => 'AMD Ryzen 9 7950X',
        'sku' => 'SKU-7950X',
        'price' => 599.00,
        'stock_quantity' => 12,
    ]);

    $dataTable = app(ProductDataTable::class);

    $request = Request::create('/admin/products', 'GET', [
        'draw' => 1,
    ]);
    app()->instance('request', $request);

    $eloDataTable = $dataTable->dataTable($dataTable->query(new Product));
    expect($eloDataTable)->toBeInstanceOf(EloquentDataTable::class);

    $json = $eloDataTable->toJson()->getData(true);

    expect($json)->toHaveKey('data');
    expect($json['recordsTotal'])->toBe(1);
    expect($json['data'][0]['name'])->toContain('AMD Ryzen 9 7950X');
    expect($json['data'][0]['category'])->toBe('CPUs');
    expect($json['data'][0]['make'])->toBe('Ryzen Pro');
    expect($json['data'][0]['brand'])->toBe('AMD');
    expect($json['data'][0]['price'])->toBe('$599.00');
    expect($json['data'][0]['stock_quantity'])->toContain('In Stock (12)');
});

it('instantiates and provides columns and query for all yajra datatable service classes', function () {
    $tables = [
        BrandDataTable::class,
        CategoryDataTable::class,
        MakeDataTable::class,
        WarehouseDataTable::class,
        CustomerDataTable::class,
        CustomerGroupDataTable::class,
        SupplierDataTable::class,
        OrderDataTable::class,
        SerialNumberDataTable::class,
        UserDataTable::class,
        StockAdjustmentDataTable::class,
        StockTransferDataTable::class,
        StockDataTable::class,
    ];

    foreach ($tables as $tableClass) {
        $dt = app($tableClass);
        expect($dt->html())->toBeInstanceOf(HtmlBuilder::class);
        expect(count($dt->getColumns()))->toBeGreaterThanOrEqual(4);
    }
});

it('processes brand datatable correctly', function () {
    Brand::factory()->create(['name' => 'Corsair Gaming', 'slug' => 'corsair']);

    $dataTable = app(BrandDataTable::class);
    $request = Request::create('/admin/brands', 'GET', ['draw' => 1]);
    app()->instance('request', $request);

    $json = $dataTable->dataTable($dataTable->query(new Brand))->toJson()->getData(true);
    expect($json['recordsTotal'])->toBeGreaterThanOrEqual(1);
    expect($json['data'][0]['name'])->toContain('Corsair Gaming');
});
