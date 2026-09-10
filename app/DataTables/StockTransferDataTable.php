<?php

namespace App\DataTables;

use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockTransferDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<StockTransfer>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('reference_number', function (StockTransfer $st) {
                return '<span class="font-monospace fw-bold text-primary">'.e($st->reference_number).'</span>';
            })
            ->addColumn('from_warehouse', fn (StockTransfer $st) => $st->fromWarehouse ? e($st->fromWarehouse->name) : '<span class="text-muted">-</span>')
            ->addColumn('to_warehouse', fn (StockTransfer $st) => $st->toWarehouse ? e($st->toWarehouse->name) : '<span class="text-muted">-</span>')
            ->editColumn('status', function (StockTransfer $st) {
                $cls = match (strtolower($st->status)) {
                    'completed', 'received' => 'bg-success-subtle text-success border border-success-subtle',
                    'pending', 'in_transit' => 'bg-warning-subtle text-warning border border-warning-subtle',
                    'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default => 'bg-secondary',
                };

                return '<span class="badge '.$cls.'">'.e(str_replace('_', ' ', strtoupper($st->status))).'</span>';
            })
            ->editColumn('transfer_date', fn (StockTransfer $st) => $st->transfer_date ? $st->transfer_date->format('M d, Y') : '-')
            ->addColumn('action', function (StockTransfer $st) {
                $viewUrl = route('admin.stock.transfers.show', $st->id);

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$viewUrl.'" class="btn btn-outline-secondary" title="View Transfer Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->editColumn('updated_at', fn (StockTransfer $t) => $t->updated_at?->format('Y-m-d H:i') ?? '-')
            ->rawColumns(['reference_number', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<StockTransfer>
     */
    public function query(StockTransfer $model): QueryBuilder
    {
        return $model->newQuery()->with(['fromWarehouse', 'toWarehouse', 'user'])->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stocktransfer-table')
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
            Column::make('reference_number')->title('Transfer #'),
            Column::make('from_warehouse')->title('From (Source)')->orderable(false),
            Column::make('to_warehouse')->title('To (Destination)')->orderable(false),
            Column::make('items_count')->title('Items & Quantity'),
            Column::make('status')->title('Status'),
            Column::make('transfer_date')->title('Date / Initiator')->attributes(['data-default-sort' => 'desc']),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'StockTransfer_'.date('YmdHis');
    }
}
