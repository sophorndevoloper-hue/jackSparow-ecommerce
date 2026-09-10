<?php

namespace App\DataTables;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BrandDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Brand>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Brand $b) {
                $img = $b->logo_url
                    ? '<img src="'.e($b->logo_url).'" alt="'.e($b->name).'" class="w-100 h-100 object-fit-contain p-1">'
                    : '<i class="bi bi-tag fs-5 text-primary"></i>';

                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded bg-light border overflow-hidden d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                            '.$img.'
                        </div>
                        <div>
                            <span class="fw-bold text-body-emphasis">'.e($b->name).'</span>
                            <div class="text-muted small font-monospace">'.e($b->slug).'</div>
                        </div>
                    </div>
                ';
            })
            ->editColumn('website', function (Brand $b) {
                if (! $b->website) {
                    return '<span class="text-muted">-</span>';
                }

                return '<a href="'.e($b->website).'" target="_blank" rel="noopener noreferrer" class="text-decoration-none small text-truncate d-inline-block" style="max-width: 180px;"><i class="bi bi-box-arrow-up-right me-1"></i>'.e($b->website).'</a>';
            })
            ->editColumn('products_count', fn (Brand $b) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'.(int) $b->products_count.' Products</span>')
            ->editColumn('is_active', fn (Brand $b) => $b->is_active ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>' : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('action', function (Brand $b) {
                $editUrl = route('admin.brands.edit', $b->id);
                $deleteUrl = route('admin.brands.destroy', $b->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Brand">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this brand?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Brand">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (Brand $b) => $b->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'website', 'products_count', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Brand>
     */
    public function query(Brand $model): QueryBuilder
    {
        return $model->newQuery()->withCount('products')->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('brand-table')
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
            Column::make('name')->title('Brand'),
            Column::make('slug')->title('Slug'),
            Column::make('website')->title('Website')->orderable(false),
            Column::make('products_count')->title('Total Components'),
            Column::make('is_active')->title('Status'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Brand_'.date('YmdHis');
    }
}
