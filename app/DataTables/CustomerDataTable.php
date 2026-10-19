<?php

namespace App\DataTables;

use App\Models\FrontendUser;
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
     * @param  QueryBuilder<FrontendUser>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (FrontendUser $c) {
                $status = $c->is_blocked
                    ? '<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 10px;">Blocked</span>'
                    : '<span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 10px;">Active</span>';
                $company = $c->company_name ? '<span class="text-muted small d-block">'.e($c->company_name).'</span>' : '';

                return '
                    <div>
                        <span class="fw-bold text-body-emphasis">'.e($c->name).'</span>
                        '.$status.'
                        '.$company.'
                        <div class="text-muted small">'.e($c->email).'</div>
                    </div>
                ';
            })
            ->editColumn('phone', fn (FrontendUser $c) => $c->phone ? '<span class="text-body font-monospace small">'.e($c->phone).'</span>' : '<span class="text-muted">-</span>')
            ->addColumn('location', fn (FrontendUser $c) => e($c->city ?? 'N/A'))
            ->editColumn('orders_count', fn (FrontendUser $c) => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">'.(int) $c->orders_count.' Orders</span>')
            ->addColumn('action', function (FrontendUser $c) {
                $editUrl = route('admin.customers.edit', $c->id);
                $deleteUrl = route('admin.customers.destroy', $c->id);
                $toggleBlockUrl = route('admin.customers.toggle-block', $c->id);
                $csrf = csrf_field();
                $blockMethod = method_field('PATCH');
                $deleteMethod = method_field('DELETE');
                $blockTitle = $c->is_blocked ? 'Unblock Customer' : 'Block Customer';
                $blockIcon = $c->is_blocked ? 'bi-unlock text-success' : 'bi-slash-circle text-warning';
                $blockConfirm = $c->is_blocked ? 'Unblock this customer account?' : 'Block this customer account? They will not be able to log in or place orders.';

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$editUrl.'" class="btn btn-outline-secondary" title="Edit Customer">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="'.$toggleBlockUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\''.$blockConfirm.'\');">
                            '.$csrf.'
                            '.$blockMethod.'
                            <button type="submit" class="btn btn-outline-secondary" title="'.$blockTitle.'">
                                <i class="bi '.$blockIcon.'"></i>
                            </button>
                        </form>
                        <form action="'.$deleteUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this customer?\');">
                            '.$csrf.'
                            '.$deleteMethod.'
                            <button type="submit" class="btn btn-outline-danger" title="Delete Customer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (FrontendUser $c) => $c->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['name', 'phone', 'orders_count', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<FrontendUser>
     */
    public function query(FrontendUser $model): QueryBuilder
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
            Column::make('city')->title('Location'),
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
