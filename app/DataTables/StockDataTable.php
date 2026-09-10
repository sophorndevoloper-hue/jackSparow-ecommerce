<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Product>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', fn (Product $p) => e($p->name))
            ->addColumn('category', fn (Product $p) => $p->category->name ?? '-')
            ->editColumn('stock_quantity', fn (Product $p) => (int) $p->stock_quantity)
            ->editColumn('price', fn (Product $p) => '$'.number_format($p->price, 2))
            ->addColumn('quick_adjust', fn (Product $p) => number_format($p->stock_quantity).' units (Alert: ≤ '.number_format($p->low_stock_threshold).')')
            ->addColumn('action', function (Product $p) {
                $serialsUrl = route('admin.serial-numbers.index', ['product_id' => $p->id]);
                $editUrl = route('admin.products.edit', $p->id);

                return '<div class="d-flex align-items-center justify-content-end gap-1">
                    <a href="'.$serialsUrl.'" class="btn btn-sm btn-ghost" title="View Serial Product List (Registry & Warranties)"><i class="bi bi-upc-scan"></i></a>
                    <a href="'.$editUrl.'" class="btn btn-sm btn-ghost" title="Edit Product"><i class="bi bi-pencil"></i></a>
                </div>';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Product>
     */
    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery()->with(['category', 'brand', 'make', 'primaryImage', 'warehouses'])->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('Hardware Component')->attributes(['style' => 'min-width: 260px;']),
            Column::make('category')->title('Category, Make & Brand'),
            Column::make('stock_quantity')->title('Stock Status'),
            Column::make('price')->title('Unit Price'),
            Column::computed('quick_adjust')->title('Stock Qty & Alert')->orderable(false)->attributes(['style' => 'min-width: 170px;']),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Stock_'.date('YmdHis');
    }
}
