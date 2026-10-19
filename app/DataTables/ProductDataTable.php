<?php

namespace App\DataTables;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Product>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Product $p) {
                $img = $p->primary_image_url
                    ? '<img src="'.e($p->primary_image_url).'" alt="'.e($p->name).'" class="w-100 h-100 object-fit-cover">'
                    : '<i class="bi bi-cpu fs-5 text-primary"></i>';

                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded bg-light border overflow-hidden d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                            '.$img.'
                        </div>
                        <div>
                            <span class="fw-bold text-body-emphasis">'.e($p->name).'</span>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="text-muted small font-monospace">SKU: '.e($p->sku).'</span>
                                '.($p->model ? '<span class="badge bg-light text-primary border" style="font-size: 10px;"><i class="bi bi-cpu me-1"></i>'.e($p->model->name).'</span>' : '').'
                            </div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('category', fn (Product $p) => $p->category->name ?? '-')
            ->addColumn('brand', fn (Product $p) => $p->brand->name ?? '-')
            ->editColumn('price', fn (Product $p) => '$'.number_format($p->price, 2))
            ->editColumn('stock_quantity', function (Product $p) {
                if ($p->stock_quantity <= 0) {
                    return '<span class="badge bg-danger">Out of Stock (0)</span>';
                }
                if ($p->stock_quantity <= $p->low_stock_threshold) {
                    return '<span class="badge bg-warning text-dark">Low Stock ('.$p->stock_quantity.')</span>';
                }

                return '<span class="badge bg-success-subtle text-success border border-success-subtle">In Stock ('.$p->stock_quantity.')</span>';
            })
            ->editColumn('status', function (Product $p) {
                if ($p->is_active) {
                    return '<span class="badge bg-success-subtle text-success border-0 px-2 py-1"><i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>Active</span>';
                }

                return '<span class="badge bg-secondary-subtle text-secondary border-0 px-2 py-1">Draft</span>';
            })
            ->addColumn('action', function (Product $p) {
                $editUrl = route('admin.products.edit', $p->id);
                $destroyUrl = route('admin.products.destroy', $p->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $deleteBtn = '';
                if (auth()->guard('backend')->user()?->can('delete products')) {
                    if ($p->canBeDeleted()) {
                        $deleteBtn = '
                            <form action="'.$destroyUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete draft component \\\''.addslashes($p->name).'\\\'?\');">
                                '.$csrf.'
                                '.$method.'
                                <button type="submit" class="btn btn-outline-danger" title="Delete Draft Component">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        ';
                    } else {
                        $deleteBtn = '
                            <span class="d-inline-block" tabindex="0" title="'.e($p->getDeletionBlockReason()).'">
                                <button type="button" class="btn btn-outline-secondary opacity-50" style="cursor: not-allowed; pointer-events: none;" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </span>
                        ';
                    }
                }

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Product">
                            <i class="bi bi-pencil"></i>
                        </a>
                        '.$deleteBtn.'
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (Product $p) => $p->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'stock_quantity', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Product>
     */
    public function query(Product $model): QueryBuilder
    {
        return $model->newQuery()->with(['category', 'brand', 'model', 'primaryImage'])->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-datatable')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(7, 'desc')
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
            Column::make('name')->title('Hardware Part Details')->attributes(['style' => 'min-width: 280px;']),
            Column::make('category')->title('Category & Specs')->orderable(false)->attributes(['style' => 'min-width: 170px;']),
            Column::make('price')->title('B2B Pricing & Floor')->attributes(['style' => 'min-width: 160px;']),
            Column::make('moq')->title('MOQ / Case')->orderable(false)->attributes(['style' => 'min-width: 120px;']),
            Column::make('stock_quantity')->title('Stock & Serials')->attributes(['style' => 'min-width: 140px;']),
            Column::make('status')->title('Status')->attributes(['style' => 'min-width: 110px;']),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort')->attributes(['style' => 'min-width: 150px;']),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Products_'.date('YmdHis');
    }
}
