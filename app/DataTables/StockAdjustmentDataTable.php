<?php

namespace App\DataTables;

use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockAdjustmentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<StockAdjustment>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('reference_number', function (StockAdjustment $adj) {
                return '<span class="font-monospace fw-bold text-primary">'.e($adj->reference_number).'</span>';
            })
            ->addColumn('warehouse', fn (StockAdjustment $adj) => $adj->warehouse ? e($adj->warehouse->name) : '<span class="text-muted">-</span>')
            ->addColumn('user', fn (StockAdjustment $adj) => $adj->user ? e($adj->user->name) : '<span class="text-muted">-</span>')
            ->editColumn('type', function (StockAdjustment $adj) {
                $cls = match (strtolower($adj->type)) {
                    'addition', 'increase' => 'bg-success-subtle text-success border border-success-subtle',
                    'subtraction', 'decrease', 'scrap', 'damage' => 'bg-danger-subtle text-danger border border-danger-subtle',
                    default => 'bg-info-subtle text-info border border-info-subtle',
                };

                return '<span class="badge '.$cls.'">'.e(strtoupper($adj->type)).'</span>';
            })
            ->editColumn('reason', fn (StockAdjustment $adj) => e($adj->reason ?? '-'))
            ->editColumn('created_at', fn (StockAdjustment $adj) => $adj->created_at ? $adj->created_at->format('M d, Y H:i') : '-')
            ->editColumn('updated_at', fn (StockAdjustment $adj) => $adj->updated_at ? $adj->updated_at->format('M d, Y H:i') : '-')
            ->addColumn('action', function (StockAdjustment $adj) {
                $viewUrl = route('admin.stock.adjustments.show', $adj->id);

                return '
                    <div class="btn-group btn-group-sm">
                        <a href="'.$viewUrl.'" class="btn btn-outline-secondary" title="View Details">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['reference_number', 'type', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<StockAdjustment>
     */
    public function query(StockAdjustment $model): QueryBuilder
    {
        return $model->newQuery()->with(['warehouse', 'user'])->latest('updated_at');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stockadjustment-table')
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
            Column::make('reference_number')->title('Reference #'),
            Column::make('warehouse')->title('Warehouse')->orderable(false),
            Column::make('type')->title('Type'),
            Column::make('reason')->title('Reason')->orderable(false),
            Column::make('items_count')->title('Items Adjusted'),
            Column::make('created_at')->title('Author / Date')->attributes(['data-default-sort' => 'desc']),
            Column::computed('action')->title('Actions')->addClass('text-end no-sort'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'StockAdjustment_'.date('YmdHis');
    }
}
