<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Order>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('order_number', function (Order $o) {
                return '<span class="fw-bold font-monospace text-primary">'.e($o->order_number).'</span>';
            })
            ->editColumn('customer_name', function (Order $o) {
                return '
                    <div>
                        <span class="fw-bold text-body-emphasis">'.e($o->customer_name).'</span>
                        <div class="text-muted small">'.e($o->customer_email).'</div>
                    </div>
                ';
            })
            ->editColumn('total_amount', fn (Order $o) => '<span class="fw-bold">$'.number_format($o->total_amount, 2).'</span>')
            ->editColumn('payment_status', function (Order $o) {
                $cls = match (strtolower($o->payment_status)) {
                    'paid' => 'bg-success-subtle text-success border border-success-subtle',
                    'pending' => 'bg-warning-subtle text-warning border border-warning-subtle',
                    'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default => 'bg-secondary',
                };

                return '<span class="badge '.$cls.'">'.e(strtoupper($o->payment_status)).'</span>';
            })
            ->editColumn('status', function (Order $o) {
                $cls = match (strtolower($o->status)) {
                    'completed', 'delivered' => 'bg-success text-white',
                    'processing', 'shipped' => 'bg-info text-dark',
                    'pending' => 'bg-warning text-dark',
                    'cancelled' => 'bg-danger text-white',
                    default => 'bg-secondary',
                };

                return '<span class="badge '.$cls.'">'.e(strtoupper($o->status)).'</span>';
            })
            ->editColumn('created_at', fn (Order $o) => $o->created_at ? $o->created_at->format('M d, Y H:i') : '-')
            ->editColumn('updated_at', fn (Order $o) => $o->updated_at ? $o->updated_at->format('M d, Y H:i') : '-')
            ->addColumn('action', function (Order $o) {
                $viewUrl = route('admin.orders.show', $o->id);

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$viewUrl.'" class="btn btn-outline-secondary" title="View Order Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['order_number', 'customer_name', 'total_amount', 'payment_status', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Order>
     */
    public function query(Order $model): QueryBuilder
    {
        return $model->newQuery()->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('order-table')
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
            Column::make('order_number')->title('Order Number'),
            Column::make('customer_name')->title('Customer Details'),
            Column::make('created_at')->title('Date')->attributes(['data-default-sort' => 'desc']),
            Column::make('total_amount')->title('Total'),
            Column::make('payment_status')->title('Payment'),
            Column::make('status')->title('Fulfillment'),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Order_'.date('YmdHis');
    }
}
