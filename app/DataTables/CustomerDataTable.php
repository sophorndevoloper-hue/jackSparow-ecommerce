<?php

namespace App\DataTables;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CustomerDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Customer>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Customer $c) {
                $company = $c->company_name ? '<span class="text-muted small d-block">'.e($c->company_name).'</span>' : '';

                return '
                    <div>
                        <span class="fw-bold text-body-emphasis">'.e($c->name).'</span>
                        '.$company.'
                        <div class="text-muted small">'.e($c->email).'</div>
                    </div>
                ';
            })
            ->editColumn('phone', fn (Customer $c) => $c->phone ? '<span class="text-body font-monospace small">'.e($c->phone).'</span>' : '<span class="text-muted">-</span>')
            ->addColumn('location', fn (Customer $c) => e($c->city ?? 'N/A'))
            ->editColumn('customer_type', fn (Customer $c) => '<span class="badge bg-secondary-subtle text-body border">'.e(strtoupper($c->customer_type ?? 'INDIVIDUAL')).'</span>')
            ->editColumn('orders_count', fn (Customer $c) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'.(int) $c->orders_count.' Orders</span>')
            ->addColumn('action', function (Customer $c) {
                $editUrl = route('admin.customers.edit', $c->id);
                $deleteUrl = route('admin.customers.destroy', $c->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Customer">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this customer?\');">
                            '.$csrf.'
                            '.$method.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Customer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (Customer $c) => $c->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'phone', 'customer_type', 'orders_count', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Customer>
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('customer-table')
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
            Column::make('name')->title('Customer Profile'),
            Column::make('contact_info')->title('Contact Info')->orderable(false),
            Column::make('customer_type')->title('Customer Tier'),
            Column::make('orders_count')->title('Total Orders'),
            Column::make('total_spent')->title('Total Spent'),
            Column::make('created_at')->title('Registered')->attributes(['data-default-sort' => 'desc']),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Customer_'.date('YmdHis');
    }
}
