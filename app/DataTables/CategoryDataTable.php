<?php

namespace App\DataTables;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Category>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Category $c) {
                $icon = $c->parent_id
                    ? '<i class="bi bi-arrow-return-right text-muted me-1"></i>'
                    : '<i class="bi bi-folder-fill text-warning me-1"></i>';

                return '
                    <div class="d-flex align-items-center">
                        '.$icon.'
                        <div>
                            <span class="fw-bold text-body-emphasis">'.e($c->name).'</span>
                            <div class="text-muted small font-monospace">'.e($c->slug).'</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('parent', fn (Category $c) => $c->parent ? '<span class="badge bg-light text-body border">'.e($c->parent->name).'</span>' : '<span class="badge bg-secondary-subtle text-muted">Root Category</span>')
            ->editColumn('products_count', fn (Category $c) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'.(int) $c->products_count.' Items</span>')
            ->editColumn('is_active', fn (Category $c) => $c->is_active ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>' : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('action', function (Category $c) {
                $editUrl = route('admin.categories.edit', $c->id);
                $deleteUrl = route('admin.categories.destroy', $c->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Category">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this category?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Category">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (Category $c) => $c->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'parent', 'products_count', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Category>
     */
    public function query(Category $model): QueryBuilder
    {
        return $model->newQuery()->with('parent')->withCount('products')->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('category-table')
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
            Column::make('name')->title('Category Name'),
            Column::make('slug')->title('Slug'),
            Column::make('parent')->title('Parent')->orderable(false),
            Column::make('products_count')->title('Total Parts'),
            Column::make('is_active')->title('Status'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Category_'.date('YmdHis');
    }
}
