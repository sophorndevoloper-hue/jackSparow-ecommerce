<?php

namespace App\DataTables;

use App\Models\ProductModel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductModelDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<ProductModel>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (ProductModel $m) {
                $img = $m->image_url
                    ? '<img src="'.e($m->image_url).'" alt="'.e($m->name).'" class="w-100 h-100 object-fit-contain p-1">'
                    : '<i class="bi bi-cpu fs-5 text-primary"></i>';

                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded bg-light border overflow-hidden d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                            '.$img.'
                        </div>
                        <div>
                            <span class="fw-bold text-body-emphasis">'.e($m->name).'</span>
                            <div class="text-muted small font-monospace">'.e($m->slug).'</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('brand', function (ProductModel $m) {
                return '<span class="badge bg-secondary-subtle text-secondary-emphasis border px-2 py-1"><i class="bi bi-tag-fill me-1"></i>'.e($m->brand?->name ?? '-').'</span>';
            })
            ->editColumn('products_count', fn (ProductModel $m) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'.(int) $m->products_count.' Products</span>')
            ->editColumn('is_active', fn (ProductModel $m) => $m->is_active ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>' : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('action', function (ProductModel $m) {
                $editUrl = route('admin.models.edit', $m->id);
                $deleteUrl = route('admin.models.destroy', $m->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Model">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this model?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Model">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (ProductModel $m) => $m->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'brand', 'products_count', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ProductModel>
     */
    public function query(ProductModel $model): QueryBuilder
    {
        return $model->newQuery()->with('brand')->withCount('products')->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('models-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5, 'desc')
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
            Column::make('name')->title('Model Name'),
            Column::computed('brand')->title('Brand / Manufacturer'),
            Column::make('products_count')->title('Assigned Products'),
            Column::make('is_active')->title('Status'),
            Column::make('updated_at')->title('Updated At'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ProductModels_'.date('YmdHis');
    }
}
