<?php

namespace App\DataTables;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WarehouseDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Warehouse>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Warehouse $wh) {
                $defaultBadge = $wh->is_default ? '<span class="badge bg-primary ms-1" style="font-size: 10px;">Default Hub</span>' : '';
                $viewUrl = route('admin.warehouses.show', $wh->id);

                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-building fs-5"></i>
                        </div>
                        <div>
                            <a href="'.$viewUrl.'" class="fw-bold text-body-emphasis text-decoration-none">'.e($wh->name).'</a>
                            '.$defaultBadge.'
                            <div class="text-muted small font-monospace">Code: '.e($wh->code).'</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('location', function (Warehouse $wh) {
                return '<div><span class="text-body fw-medium">'.e($wh->city ?? 'N/A').'</span><small class="text-muted d-block text-truncate" style="max-width: 180px;">'.e($wh->address ?? '-').'</small></div>';
            })
            ->addColumn('contact', function (Warehouse $wh) {
                $phone = $wh->phone ? '<div class="small"><i class="bi bi-telephone text-muted me-1"></i>'.e($wh->phone).'</div>' : '';
                $email = $wh->email ? '<div class="small text-muted"><i class="bi bi-envelope text-muted me-1"></i>'.e($wh->email).'</div>' : '';

                return $phone.$email ?: '<span class="text-muted small">-</span>';
            })
            ->addColumn('total_stock', function (Warehouse $wh) {
                return '<span class="badge bg-info-subtle text-info border border-info-subtle">'.(int) $wh->total_stock.' Units</span>';
            })
            ->editColumn('is_active', fn (Warehouse $wh) => $wh->is_active ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>' : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('action', function (Warehouse $wh) {
                $viewUrl = route('admin.warehouses.show', $wh->id);
                $editUrl = route('admin.warehouses.edit', $wh->id);
                $deleteUrl = route('admin.warehouses.destroy', $wh->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $deleteBtn = ! $wh->is_default ? '
                    <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this warehouse?\');">
                        '.$csrf.'
                        '.$method.'
                        <button type="submit" class="btn btn-outline-danger" title="Delete Warehouse">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                ' : '';

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$viewUrl.'" class="btn btn-outline-secondary" title="View Inventory">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Warehouse">
                            <i class="bi bi-pencil"></i>
                        </a>
                        '.$deleteBtn.'
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (Warehouse $w) => $w->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'location', 'contact', 'total_stock', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Warehouse>
     */
    public function query(Warehouse $model): QueryBuilder
    {
        return $model->newQuery()->withCount('products')->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('warehouse-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(6, 'desc')
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
            Column::make('name')->title('Warehouse Info'),
            Column::make('code')->title('Code'),
            Column::make('location')->title('Location / City'),
            Column::make('contact')->title('Contact Details')->orderable(false),
            Column::make('total_stock')->title('Stored Items'),
            Column::make('is_active')->title('Status'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Warehouse_'.date('YmdHis');
    }
}
