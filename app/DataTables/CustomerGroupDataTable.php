<?php

namespace App\DataTables;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerGroupDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<CustomerGroup>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (CustomerGroup $g) {
                return '
                    <div>
                        <span class="fw-bold text-body-emphasis">'.e($g->name).'</span>
                        <div class="text-muted small font-monospace">Code: '.e($g->code).'</div>
                    </div>
                ';
            })
            ->editColumn('default_discount_percentage', fn (CustomerGroup $g) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'.number_format($g->default_discount_percentage, 1).'%</span>')
            ->editColumn('credit_limit', fn (CustomerGroup $g) => '$'.number_format($g->credit_limit, 2))
            ->editColumn('payment_terms_days', fn (CustomerGroup $g) => $g->payment_terms_days.' Days')
            ->editColumn('is_active', fn (CustomerGroup $g) => $g->is_active ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>' : '<span class="badge bg-secondary">Inactive</span>')
            ->addColumn('action', function (CustomerGroup $g) {
                $editUrl = route('admin.customer-groups.edit', $g->id);
                $deleteUrl = route('admin.customer-groups.destroy', $g->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Customer Group">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this customer group?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Group">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (CustomerGroup $g) => $g->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'default_discount_percentage', 'is_active', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<CustomerGroup>
     */
    public function query(CustomerGroup $model): QueryBuilder
    {
        return $model->newQuery()->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('customergroup-table')
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
            Column::make('name')->title('Group Name'),
            Column::make('code')->title('Code'),
            Column::make('default_discount_percentage')->title('Default Discount'),
            Column::make('payment_terms_days')->title('Payment Terms'),
            Column::make('credit_limit')->title('Default Credit Limit'),
            Column::make('users_count')->title('Assigned Accounts'),
            Column::make('tiers')->title('Active Tiers'),
            Column::make('is_active')->title('Status'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CustomerGroup_'.date('YmdHis');
    }
}
