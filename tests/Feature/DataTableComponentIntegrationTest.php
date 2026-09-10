<?php

use App\DataTables\BrandDataTable;
use App\DataTables\ProductDataTable;
use Illuminate\Support\Facades\Blade;

it('renders datatable blade component with yajra service class', function () {
    $dataTable = app(ProductDataTable::class);

    $view = Blade::render('<x-datatable :dataTable="$dataTable" />', ['dataTable' => $dataTable]);

    expect($view)->toContain('id="product-datatable"');
    expect($view)->toContain('Hardware Part Details');
    expect($view)->toContain('table-responsive');
});

it('renders datatable blade component in slot mode with custom id and search placeholder', function () {
    $blade = <<<'BLADE'
        <x-datatable id="testTable" searchPlaceholder="Search custom..." :perPage="25">
            <table id="testTable" class="table">
                <thead><tr><th>Name</th></tr></thead>
                <tbody><tr><td>Item A</td></tr></tbody>
            </table>
        </x-datatable>
        @stack('scripts')
    BLADE;

    $view = Blade::render($blade);

    expect($view)->toContain('id="testTable"');
    expect($view)->toContain('Item A');
    expect($view)->toContain('Search custom...');
    expect($view)->toContain('pageLength: 25');
    expect($view)->toContain('.DataTable(');
});

it('renders brand datatable with x-datatable component', function () {
    $dataTable = app(BrandDataTable::class);

    $view = Blade::render('<x-datatable :dataTable="$dataTable" id="customBrandTable" />', ['dataTable' => $dataTable]);

    expect($view)->toContain('id="customBrandTable"');
    expect($view)->toContain('Brand');
    expect($view)->toContain('Total Components');
});

it('renders x-datatable.thead component dynamically from datatable service class', function () {
    $dataTable = app(BrandDataTable::class);

    $view = Blade::render('<x-datatable.thead :dataTable="$dataTable" />', ['dataTable' => $dataTable]);

    expect($view)->toContain('<thead class="table-light">');
    expect($view)->toContain('<th>Brand</th>');
    expect($view)->toContain('<th>Slug</th>');
    expect($view)->toContain('<th class="no-sort">Website</th>');
    expect($view)->toContain('<th>Total Components</th>');
    expect($view)->toContain('<th>Status</th>');
    expect($view)->toContain('<th class="text-end no-sort">Actions</th>');
});

it('renders x-datatable.refresh component with target and icon', function () {
    $view = Blade::render('<x-datatable.refresh target="productsTable" text="Reload Data" />');

    expect($view)->toContain('data-dt-refresh="productsTable"');
    expect($view)->toContain('Reload Data');
    expect($view)->toContain('refresh-icon');
});
