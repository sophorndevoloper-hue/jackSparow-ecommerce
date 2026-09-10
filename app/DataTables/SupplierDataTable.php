<?php

namespace App\DataTables;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SupplierDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Supplier>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('company_name', function (Supplier $s) {
                return '
                    <div>
                        <span class="fw-bold text-body-emphasis">'.e($s->company_name).'</span>
                        <div class="text-muted small">Contact: '.e($s->contact_name ?? 'N/A').'</div>
                    </div>
                ';
            })
            ->editColumn('email', fn (Supplier $s) => $s->email ? '<a href="mailto:'.e($s->email).'" class="text-decoration-none small"><i class="bi bi-envelope me-1"></i>'.e($s->email).'</a>' : '<span class="text-muted">-</span>')
            ->editColumn('phone', fn (Supplier $s) => $s->phone ? '<span class="text-body font-monospace small">'.e($s->phone).'</span>' : '<span class="text-muted">-</span>')
            ->addColumn('location', fn (Supplier $s) => e($s->city ?? 'N/A'))
            ->editColumn('status', fn (Supplier $s) => $s->status === 'active' ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>' : '<span class="badge bg-secondary">'.e(ucfirst($s->status ?? 'inactive')).'</span>')
            ->addColumn('action', function (Supplier $s) {
                $editUrl = route('admin.suppliers.edit', $s->id);
                $deleteUrl = route('admin.suppliers.destroy', $s->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Supplier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this supplier?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Supplier">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (Supplier $s) => $s->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['company_name', 'email', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Supplier>
     */
    public function query(Supplier $model): QueryBuilder
    {
        return $model->newQuery()->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('supplier-table')
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
            Column::make('company_name')->title('Company Name'),
            Column::make('contact_person')->title('Contact Person'),
            Column::make('email')->title('Email & Phone')->orderable(false),
            Column::make('categories')->title('Supplied Categories')->orderable(false),
            Column::make('status')->title('Status'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Supplier_'.date('YmdHis');
    }
}
